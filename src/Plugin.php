<?php

namespace wsydney76\emaillist;

use Craft;
use function array_merge;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Utilities;
use craft\web\UrlManager;
use craft\web\View;
use wsydney76\emaillist\models\Settings;
use wsydney76\emaillist\services\EmaillistService;
use wsydney76\emaillist\utilities\EmaillistUtility;
use wsydney76\emaillist\utilities\RegisterEmailUtility;
use yii\base\Event;

/**
 * Register Email plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author wsydney76 <wsydney@web.de>
 * @copyright wsydney76
 * @license MIT
 * @property-read EmaillistService $emaillist
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;

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

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)

        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['@emaillist'] = $this->getBasePath() . '/templates';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'emaillist/register' => 'emaillist/emaillist/register',
                    'emaillist/unregister' => 'emaillist/emaillist/unregister'
                ]);
            }
        );
        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = EmaillistUtility::class;
        });
    }
}
