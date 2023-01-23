<?php

namespace wsydney76\emaillist\behaviors;

use wsydney76\emaillist\records\RegistrationRecord;
use yii\base\Behavior;

class CraftVariableBehavior extends Behavior
{
    public function registrations() {
        return RegistrationRecord::find();
    }
}