<?php

namespace wsydney76\emaillist\utilities;

use Craft;
use craft\base\Utility;
use wsydney76\emaillist\Plugin;
use wsydney76\emaillist\records\EmaillistRecord;

/**
 * Emaillist Utility utility
 */
class EmaillistUtility extends Utility
{
    public static function displayName(): string
    {
        return Craft::t('emaillist', 'Email List');
    }

    static function id(): string
    {
        return 'emaillist-utility';
    }

    public static function iconPath(): ?string
    {
        return Plugin::getInstance()->getBasePath() . '/icon.svg';
    }

    static function contentHtml(): string
    {
        return Craft::$app->view->renderTemplate('emaillist/_utility.twig' ,[
            'emaillistQuery' => EmaillistRecord::find(),
        ]);
    }
}
