<?php

namespace wsydney76\emaillist\controllers;

use Craft;
use craft\web\Controller;
use craft\web\View;
use wsydney76\emaillist\events\EmaillistRegisterEvent;
use wsydney76\emaillist\Plugin;
use wsydney76\emaillist\records\EmaillistRecord;
use wsydney76\emaillist\services\EmaillistService;
use yii\web\Response;

/**
 * Register Email controller
 */
class EmaillistController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE;
    protected EmaillistService $service;

    public const EVENT_EMAILLIST_REGISTER = 'eventEmaillistRegister';

    public function beforeAction($action): bool
    {
        $this->service = Plugin::getInstance()->emaillist;

        return parent::beforeAction($action);
    }

    public function actionPing()
    {
        return 'Pong';
    }

    /**
     * emaillist/emaillist action
     */
    public function actionRegister(): Response
    {
        $this->requireAcceptsJson();

        $email = Craft::$app->request->getRequiredQueryParam('email');
        $list = Craft::$app->request->getQueryParam('list', 'default');


        if ($this->hasEventHandlers(self::EVENT_EMAILLIST_REGISTER)) {
            $event = new EmaillistRegisterEvent([
                'request' => $this->request,
                'email' => $email,
                'list' => $list
            ]);
            $this->trigger(self::EVENT_EMAILLIST_REGISTER, $event);
            if ($event->handled) {
                return $this->asJson([
                    'success' => true,
                    'message' => 'no',
                    'email' => $email
                ]);
            }
        }

        $record = $this->service->createEmaillistEntry($email, $list, Craft::$app->sites->currentSite->handle);

        if ($record->hasErrors()) {
            return $this->asJson([
                'success' => false,
                'message' => $record->getFirstError('email'),
                'email' => $record->email
            ]);
        }

        return $this->asJson([
            'success' => true,
            'message' => Craft::t(
                'emaillist',
                'Email {email} registered.',
                ['email' => $record->email]),
            'email' => $record->email
        ]);
    }

    public function actionUnregister()
    {
        $email = Craft::$app->request->getRequiredQueryParam('email');
        $verificationCode = Craft::$app->request->getRequiredQueryParam('verificationCode');
        $list = Craft::$app->request->getRequiredQueryParam('list');

        $this->service->unregister($email, $list, $verificationCode);

        return Craft::$app->view->renderPageTemplate('emaillist/_wrapper.twig', [
            'title' => Craft::t('emaillist', 'Unregister'),
            'text' => Craft::t('emaillist', 'Your email is removed from the list.'),
        ], View::TEMPLATE_MODE_SITE);
    }

    public function actionCpRegister()
    {
        $this->requirePermission('utility:emaillist-utility');

        $email = Craft::$app->request->getRequiredBodyParam('email');
        $list = Craft::$app->request->getRequiredBodyParam('list');
        $site = Craft::$app->request->getRequiredBodyParam('site');

        $record = new EmaillistRecord([
            'email' => $email,
            'list' => $list ?? 'default',
            'site' => $site
        ]);

        $record->save();

        if ($record->hasErrors()) {
            return $this->asModelFailure($record, 'Could not register email', 'emaillistRecord');
        }

        return  $this->asModelSuccess($record, 'Email registered.', 'emaillistRecord');

    }

    public function actionCpUnregister()
    {
        $this->requirePermission('utility:emaillist-utility');

        $ids = Craft::$app->request->getBodyParam('delete', []);

        if (!$ids) {
            return $this->asFailure('Nothing selected.');
        }

        $this->service->deleteByIds($ids);

        return $this->asSuccess('Selected emails deleted.');
    }

    public function actionCpExport()
    {
        $this->requirePermission('utility:emaillist-utility');

        $emails = EmaillistRecord::find()
            ->orderBy('email')
            ->collect();

        if (!$emails->count()) {
            return $this->asFailure('Nothing found.');
        }

        $csvOutput = $this->service->createCsvOutput($emails);

        return $this->response->sendContentAsFile($csvOutput, 'emails.csv', []);
    }

}
