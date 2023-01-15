<?php

namespace wsydney76\emaillist\services;

use wsydney76\emaillist\records\EmaillistRecord;
use yii\base\Component;

/**
 * Register Email Service service
 */
class EmaillistService extends Component
{
    public function getEmaillistEntry(string $email, string $verificationCode = ''): EmaillistRecord|null
    {

        $query = EmaillistRecord::find()->where(['email' => $email]);

        if ($verificationCode) {
            $query->andWhere(['verificationCode' => $verificationCode]);
        }

        $record = $query->one();
        if (!$record) {
            return null;
        }
        return $record;
    }

    public function createEmaillistEntry(string $email): EmaillistRecord
    {
        $record = $this->getEmaillistEntry($email);

        if ($record) {
            return $record;
        }

        $record = new EmaillistRecord([
            'email' => $email,
        ]);

        $record->save();

        return $record;
    }


    public function unregister(string $email, string $verificationCode)
    {;
        $record = EmaillistRecord::findOne(['email' => $email, 'verificationCode' => $verificationCode]);
        if ($record) {
            $record->delete();
        }
    }
}
