<?php

namespace wsydney76\emaillist\services;

use Craft;
use craft\helpers\App;
use craft\web\View;
use Illuminate\Support\Collection;
use wsydney76\emaillist\jobs\SendNotification;
use wsydney76\emaillist\Plugin;
use wsydney76\emaillist\records\EmaillistRecord;
use yii\base\Component;
use const PHP_EOL;

/**
 * Register Email Service service
 */
class EmaillistService extends Component
{
    public function getEmaillistEntry(string $email, string $list, string $verificationCode = ''): EmaillistRecord|null
    {

        $query = EmaillistRecord::find()->where(['email' => $email, 'list' => $list]);

        if ($verificationCode) {
            $query->andWhere(['verificationCode' => $verificationCode]);
        }

        $record = $query->one();
        if (!$record) {
            return null;
        }
        return $record;
    }

    public function createEmaillistEntry(string $email, string $list, string $site = ''): EmaillistRecord

    {

        /** @var EmaillistRecord $record */
        $record = EmaillistRecord::findWithTrashed()
            ->where(['email' => $email, 'list' => $list])
            ->one();

        if ($record) {
            if ($record->dateDeleted) {
                $record->restore();
                $this->notifyEmail($record);
            }
            return $record;
        }

        if (!$site) {
            $site = Craft::$app->sites->primarySite->handle;
        }

        $record = new EmaillistRecord([
            'email' => $email,
            'list' => $list,
            'site' => $site,
        ]);

        $record->save();

        $this->notifyEmail($record);

        return $record;
    }


    public function unregister(string $email ,string $list, string $verificationCode)
    {

        $record = EmaillistRecord::findOne([
            'email' => $email,
            'list' => $list,
            'verificationCode' => $verificationCode
        ]);
        if ($record) {
            $record->softDelete();
        }
    }

    public function deleteByIds(array $ids)
    {
        $records = EmaillistRecord::find()->where(['in', 'id', $ids])->all();
        foreach ($records as $record) {
            $record->softDelete();
        }
    }


    public function sendNotification(EmaillistRecord $record)
    {
        $site = Craft::$app->sites->getSiteByHandle($record->site);
        if (!$site) {
            $site = Craft::$app->sites->primarySite;
        }

        $lang = $site->language;

        Craft::$app->mailer->compose()
            ->setTo($record->email)
            ->setFrom(App::env('EMAIL_ADDRESS'))
            ->setSubject(Craft::t(('emaillist'), 'Your email is registered', [], $lang))
            ->setHtmlBody(Craft::$app->view->renderTemplate('emaillist/_confirm.twig', [
                'record' => $record,
                'site' => $site,
            ] ))
            ->send();
    }

    public function createCsvOutput(Collection $emails): string
    {
        $out = '"Email","List","Date registered"' . PHP_EOL;

        /** @var EmaillistRecord $email */
        foreach ($emails as $email) {
            $out .=
                '"' . $email->email . '",' .
                '"' . $email->list . '",' .
                '"' . $email->dateCreated . '"' .
                PHP_EOL;
        }

        return $out;
    }

    private function notifyEmail(EmaillistRecord $record)
    {
        $settings = Plugin::getInstance()->getSettings();
        if ($settings->sendNotification) {
            Craft::$app->queue->push(new SendNotification([
                'id' => $record->id
            ]));
        }
    }
}
