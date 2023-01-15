<?php

namespace wsydney76\emaillist\controllers;

use Craft;
use craft\web\Controller;
use craft\web\View;
use wsydney76\emaillist\Plugin;
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

        $model = $this->service->createEmaillistEntry($email);

        if ($model->hasErrors()) {
            return $this->asJson([
                'success' => false,
                'message' => $model->getFirstError('email'),
                'email' => $model->email
            ]);
        }

        return $this->asJson([
            'success' => true,
            'message' => Craft::t('emaillist', 'Email registered.'),
            'email' => $model->email
        ]);
    }

    public function actionUnregister()
    {
        $email = Craft::$app->request->getRequiredQueryParam('email');
        $verificationCode = Craft::$app->request->getRequiredQueryParam('verificationCode');

        $this->service->unregister($email, $verificationCode);

        return Craft::$app->view->renderPageTemplate('@emaillist/wrapper.twig', [
            'title' => 'Cancel',
            'text' => Craft::t('emaillist', 'Your email is removed from the list.'),
        ], View::TEMPLATE_MODE_SITE);
    }

}
