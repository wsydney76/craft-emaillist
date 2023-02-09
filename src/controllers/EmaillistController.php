<?php

namespace wsydney76\emaillist\controllers;

use Craft;
use craft\helpers\AdminTable;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use craft\web\View;
use wsydney76\emaillist\events\EmaillistRegisterEvent;
use wsydney76\emaillist\Plugin;
use wsydney76\emaillist\records\RegistrationRecord;
use wsydney76\emaillist\services\EmaillistService;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use function extract;
use function str_replace;
use function ucfirst;
use const EXTR_OVERWRITE;

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

        $registration = $this->service->createEmaillistEntry($email, $list, Craft::$app->sites->currentSite->handle);

        if ($registration->hasErrors()) {
            return $this->asJson([
                'success' => false,
                'message' => $registration->getFirstError('email'),
                'email' => $registration->email
            ]);
        }

        return $this->asJson([
            'success' => true,
            'message' => Craft::t(
                'emaillist',
                'Email {email} registered.',
                ['email' => $registration->email]),
            'email' => $registration->email
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


    public function actionCpEdit($id = null, ?RegistrationRecord $registration = null)
    {
        $this->requirePermission('accessplugin-emaillist');

        if (!$registration) {
            if ($id) {
                $registration = RegistrationRecord::findOne($id);
                if (!$registration) {
                    throw new NotFoundHttpException();
                }
            } else {
                $registration =  new RegistrationRecord();
            }
        }

        $buttons = [
            [
                'label' => Craft::t('app', 'Save and continue editing'),
                'redirect' => 'registration/{id}',
                'shortcut' => true,
                'retainScroll' => true,
            ],
            [
                'label' => Craft::t('emaillist', 'Save and register another email'),
                'redirect' => 'registration/new',
                'shortcut' => true,
                'shift' => true,
            ]
        ];

        if (!$registration->isNewRecord) {
            $buttons[] = [
                'label' => Craft::t('emaillist', 'Delete email registration'),
                'redirect' => 'emaillist',
                'destructive' => true,
                'action' => 'emaillist/emaillist/cp-unregister',
                'confirm' => Craft::t('emaillist', 'Are you sure you want to delete this email?'),
            ];
        }

        $title = $registration->isNewRecord ?
            Craft::t('emaillist', 'Create new Email Registration') :
            Craft::t('emaillist', 'Edit Email Registration');

        return $this->asCpScreen()
            ->title($title)
            ->addCrumb(
                Craft::t('emaillist', Craft::t('emaillist', 'Email Lists')),
                'emaillist')
            ->action('emaillist/emaillist/cp-register')
            ->redirectUrl('emaillist')
            ->saveShortcutRedirectUrl('emaillist/{id}')
            ->altActions($buttons)
            ->contentTemplate('emaillist/_emaillist-edit.twig', [
                'settings' => Plugin::getInstance()->getSettings(),
                'registration' => $registration
            ])
            ->sidebarTemplate('emaillist/_emaillist-sidebar.twig', [
                'registration' => $registration
            ]);
    }

    public function actionCpRegister()
    {
        $this->requirePermission('accessplugin-emaillist');

        $params = Craft::$app->request->getRequiredBodyParam('registration');

        extract($params, EXTR_OVERWRITE);

        $registration = $id ? RegistrationRecord::findOne($id) : new RegistrationRecord();

        if (!$registration) {
            throw new NotFoundHttpException();
        }

        $registration->setAttributes([
            'email' => $email,
            'list' => $list,
            'site' => $site,
        ]);

        $registration->active = $active ? 1 : 0;

        $registration->save();

        if ($registration->hasErrors()) {
            return $this->asModelFailure(
                $registration,
                Craft::t('emaillist', 'Could not save email registration.'),
                'registration');
        }

        return $this->asModelSuccess(
            $registration,
            Craft::t('emaillist', 'Email registration saved.'),
            'registration');
    }

    public function actionCpUnregister()
    {
        $this->requirePermission('accessplugin-emaillist');

        $ids = Craft::$app->request->getBodyParam('ids', []);

        if ($ids) {
            $this->service->deleteByIds($ids);
            return $this->asSuccess('Selected emails deleted.');
        }

        $id = Craft::$app->request->getRequiredBodyParam('id');

        $registration = RegistrationRecord::findOne($id);
        if ($registration) {
            $registration->delete();
        }

        return $this->asSuccess('Selected emails deleted.');
    }

    public function actionCpExport()
    {
        $this->requirePermission('accessplugin-emaillist');

        $registrations = RegistrationRecord::find()
            ->orderBy('email')
            ->collect();

        if (!$registrations->count()) {
            return $this->asFailure('Nothing found.');
        }

        $csvOutput = $this->service->createCsvOutput($registrations);

        return $this->response->sendContentAsFile($csvOutput, 'emails.csv', []);
    }

    public function actionTableData()
    {
        $settings = Plugin::getInstance()->settings;
        $this->requireAcceptsJson();
        $this->requirePermission('accessplugin-emaillist');

        $request = Craft::$app->request;
        $formatter = Craft::$app->formatter;

        $page = $request->getParam('page') ?: 1;
        $limit = $request->getParam('per_page') ?: 12;
        $orderBy = $request->getParam('sort') ?: 'dateCreated desc';
        $orderBy = str_replace('|', ' ', $orderBy);

        $query = RegistrationRecord::find();

        foreach (['list', 'email'] as $param) {
            $value = $request->getParam($param);
            if ($value) {
                $query->andWhere([$param => $value]);
            }
        }
        $search = $request->getParam('search');
        if ($search) {
            $query->andWhere(['like', 'email', $search]);
        }

        $count = $query->count();
        $pagination = AdminTable::paginationLinks($page, $count, $limit);

        $offset = ($page - 1) * $limit;
        $registrations = $query->orderBy($orderBy)->offset($offset)->limit($limit)->collect();

        $lists = collect($settings->lists);

        $data = $registrations->map(fn($email) => [
            'id' => $email->id,
            'title' => $email->email,
            'url' => UrlHelper::cpUrl("registration/{$email->id}"),
            'status' => $email->active,
            'list' => $lists->firstWhere('value', $email->list)['label'] ?? ucfirst($email->list),
            'site' => Craft::$app->sites->getSiteByHandle($email->site)->name,
            'date' => $formatter->asRelativeTime($email->dateCreated),
        ]);

        return $this->asJson(['pagination' => $pagination, 'data' => $data]);
    }

}
