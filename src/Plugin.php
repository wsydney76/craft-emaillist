<?php

namespace wsydney76\emaillist;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Dashboard;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use wsydney76\emaillist\behaviors\CraftVariableBehavior;
use wsydney76\emaillist\models\Settings;
use wsydney76\emaillist\services\EmaillistService;
use wsydney76\emaillist\widgets\EmaillistWidget;
use yii\base\Event;
use function array_merge;

/**
 * Register Email plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author wsydney76 <wsydney@web.de>
 * @copyright wsydney76
 * @license MIT
 * @property-read EmaillistService $emaillist
 * @property-read BaseMigrationService $baseMigrationService
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public static function config(): array
    {
        return [
            'components' => ['emaillist' => EmaillistService::class],
        ];
    }

    public function init()
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    protected function createSettingsModel(): ?Settings
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('emaillist/_settings.twig', [
            'settings' => $this->getSettings()
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)

        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['emaillist'] = $this->getBasePath() . '/templates';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'emaillist/register' => 'emaillist/emaillist/register',
                    'emaillist/unregister' => 'emaillist/emaillist/unregister',
                ]);
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'emaillist' => ['template' => 'emaillist/_index']
                ]);
            }
        );

        // Register CP Urls
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event): void {
            $event->rules['emaillist/<id:[\d]+>'] = 'emaillist/emaillist/cp-edit';
            $event->rules['emaillist/new'] = 'emaillist/emaillist/cp-edit';
        });

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->behaviors[] = CraftVariableBehavior::class;
            }
        );
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = EmaillistWidget::class;
            });
    }
}
