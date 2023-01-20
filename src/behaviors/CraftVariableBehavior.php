<?php

namespace wsydney76\emaillist\behaviors;

use wsydney76\emaillist\records\EmaillistRecord;
use yii\base\Behavior;

class CraftVariableBehavior extends Behavior
{
    public function emaillist() {
        return EmaillistRecord::find();
    }
}