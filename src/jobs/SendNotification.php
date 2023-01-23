<?php

namespace wsydney76\emaillist\jobs;

use Craft;
use craft\queue\BaseJob;
use wsydney76\emaillist\Plugin;
use wsydney76\emaillist\records\RegistrationRecord;

/**
 * Send Notification queue job
 */
class SendNotification extends BaseJob
{
    public int $id;

    /**
     * @param \yii\queue\Queue|QueueInterface $queue The queue the job belongs to
     */
    function execute($queue): void
    {
        $record = RegistrationRecord::findOne($this->id);

        if (!$record)
            return;

        $service = Plugin::getInstance()->emaillist;

        $service->sendNotification($record);
    }

    /**
     * Returns a default description for [[getDescription()]].
     *
     * ::: tip
     * Run the description through [[\craft\i18n\Translation::prep()]] rather than [[\yii\BaseYii::t()|Craft::t()]]
     * so it can be lazy-translated for users’ preferred languages rather that the current app language.
     * :::
     *
     * @return string|null
     */
    protected function defaultDescription(): ?string
    {
        return 'Send Emaillist Notification';
    }
}
