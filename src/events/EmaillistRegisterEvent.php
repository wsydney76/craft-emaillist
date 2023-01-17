<?php

namespace wsydney76\emaillist\events;

use craft\web\Request;
use yii\base\Event;

class EmaillistRegisterEvent extends Event
{
    public Request $request;
    public string $email;
    public string $list;
}