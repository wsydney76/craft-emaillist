<?php

namespace wsydney76\emaillist\widgets;

use Craft;
use craft\base\Widget;
use craft\helpers\App;
use wsydney76\emaillist\Plugin;
use wsydney76\emaillist\records\EmaillistRecord;

/**
 * Emaillist Widget widget type
 */
class EmaillistWidget extends Widget
{
    public static function displayName(): string
    {
        return Craft::t('emaillist', 'Email List');
    }

    public static function isSelectable(): bool
    {
        return true;
    }

    public static function icon(): ?string
    {
        return Plugin::getInstance()->getBasePath() . "/icon.svg";
    }

    public function getBodyHtml(): ?string
    {
        $counts = EmaillistRecord::find()
            ->groupBy('list')
            ->orderBy('list')
            ->where(['active' => 1])
            ->select(['count(*) as count', 'list'])
            ->createCommand()
            ->queryAll()
        ;


        return Craft::$app->view->renderTemplate('emaillist/_widget.twig', [
            'labels' => Plugin::getInstance()->getSettings()->getListLabels(),
            'counts' => $counts
        ]);
    }
}
