<?php

namespace wsydney76\emaillist\services;

use Craft;
use craft\helpers\App;
use craft\web\View;
use Illuminate\Support\Collection;
use wsydney76\emaillist\jobs\SendNotification;
use wsydney76\emaillist\Plugin;
use wsydney76\emaillist\records\RegistrationRecord;
use yii\base\Component;
use const PHP_EOL;

/**
 * Register Email Service service
 */
class EmaillistService extends Component
{
    public function getEmaillistEntry(string $email, string $list, string $verificationCode = ''): RegistrationRecord|null
    {

        $query = RegistrationRecord::find()->where(['email' => $email, 'list' => $list]);

        if ($verificationCode) {
            $query->andWhere(['verificationCode' => $verificationCode]);
        }

        $record = $query->one();
        if (!$record) {
            return null;
        }
        return $record;
    }

    public function createEmaillistEntry(string $email, string $list, string $site = ''): RegistrationRecord
    {

        /** @var RegistrationRecord $record */
        $record = RegistrationRecord::find()
            ->where(['email' => $email, 'list' => $list, 'site' => $site])
            ->one();

        if ($record) {
            return $record;
        }

        if (!$site) {
            $site = Craft::$app->sites->primarySite->handle;
        }

        $record = new RegistrationRecord([
            'email' => $email,
            'list' => $list,
            'site' => $site,
        ]);

        $record->save();

        if (!$record->hasErrors()) {
            $this->notifyEmail($record);
        }

        return $record;
    }


    public function unregister(string $email, string $list, string $verificationCode)
    {

        $record = RegistrationRecord::findOne([
            'email' => $email,
            'list' => $list,
            'verificationCode' => $verificationCode
        ]);
        if ($record) {
            $record->delete();
        }
    }

    public function deleteByIds(array $ids)
    {
        $records = RegistrationRecord::find()->where(['in', 'id', $ids])->all();
        foreach ($records as $record) {
            $record->delete();
        }
    }


    public function sendNotification(RegistrationRecord $registration)
    {
        $site = Craft::$app->sites->getSiteByHandle($registration->site);
        if (!$site) {
            $site = Craft::$app->sites->primarySite;
        }

        $lang = $site->language;

        Craft::$app->mailer->compose()
            ->setTo($registration->email)
            ->setFrom(App::env('EMAIL_ADDRESS'))
            ->setSubject(Craft::t(('emaillist'), 'Your email is registered', [], $lang))
            ->setHtmlBody(Craft::$app->view->renderTemplate('emaillist/_confirm.twig', [
                'record' => $registration,
                'site' => $site,
            ]))
            ->send();
    }

    public function createCsvOutput(Collection $registrations): string
    {
        $out = '"Email","List","Site","Date registered"' . PHP_EOL;

        /** @var RegistrationRecord $email */
        foreach ($registrations as $registration) {
            $out .=
                '"' . $registration->email . '",' .
                '"' . $registration->list . '",' .
                '"' . $registration->site . '",' .
                '"' . $registration->dateCreated . '"' .
                PHP_EOL;
        }

        return $out;
    }

    private function notifyEmail(RegistrationRecord $registration)
    {
        $settings = Plugin::getInstance()->getSettings();
        if ($settings->sendNotification) {
            if ($settings->useQueue) {
                Craft::$app->queue->push(new SendNotification([
                    'id' => $registration->id
                ]));
            } else {
                $this->sendNotification($registration);
            }
        }
    }
}
