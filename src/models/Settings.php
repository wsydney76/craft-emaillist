<?php

namespace wsydney76\emaillist\models;

use craft\base\Model;

class Settings extends Model
{
    public bool $sendNotification = true;
    public bool $useQueue = true;
    public array $lists = [];
}