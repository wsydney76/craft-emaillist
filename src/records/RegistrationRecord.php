<?php

namespace wsydney76\emaillist\records;

use Craft;
use craft\db\ActiveRecord;
use craft\helpers\StringHelper;
use function str_replace;
use function strtolower;

/**
 * @property string $email
 * @property string $verificationCode
 * @property string $site
 */
class RegistrationRecord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%emaillist_registrations}}';
    }

    public function rules()
    {
        return [
            [['email', 'list', 'site'], 'required'],
            ['email', 'filter', 'filter' =>  [$this, 'normalizeEmail']],
            ['email', 'email', 'message' => Craft::t('emaillist', 'This is not a valid email address!')],
            [['email','list','site'], 'unique', 'targetAttribute' => ['email', 'list','site']],
            ['list', 'string', 'max' => 25],
            ['verificationCode', 'string', 'max' => 100]
        ];
    }

    public function normalizeEmail($value) {
        return trim(strtolower($value));
    }

    public function beforeSave($insert): bool
    {
        if (!$this->verificationCode) {
            $securityService = Craft::$app->getSecurity();
            $unhashedCode = $securityService->generateRandomString(32);

            // Strip underscores so they don't get interpreted as italics markers in the Markdown parser
            $unhashedCode = str_replace('_', StringHelper::randomString(1), $unhashedCode);

            $hashedCode = $securityService->hashPassword($unhashedCode);
            $this->verificationCode = $hashedCode;
        }

        if (!$this->list) {
            $this->list = 'default';
        }

        return parent::beforeSave($insert);
    }

    public function attributeLabels()
    {
        return [
            'email' => Craft::t('emaillist', 'Email'),
            'list' => Craft::t('emaillist', 'List'),
            'site' => Craft::t('emaillist', 'Site'),
        ];
    }
}
