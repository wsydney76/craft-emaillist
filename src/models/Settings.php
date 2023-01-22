<?php

namespace wsydney76\emaillist\models;

use craft\base\Model;
use function collect;

class Settings extends Model
{
    public bool $sendNotification = true;
    public bool $useQueue = true;
    public array $lists = [];

    public function getListLabels(): array
    {
        return collect($this->lists)
            ->mapWithKeys(fn($list) => [
                $list['value'] => $list['label'],
            ])
        ->toArray();
    }
}