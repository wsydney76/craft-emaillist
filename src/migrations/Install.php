<?php

namespace wsydney76\emaillist\migrations;

use Craft;
use craft\db\Migration;
use wsydney76\emaillist\records\EmaillistRecord;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable(EmaillistRecord::tableName(), [
            'id' => $this->primaryKey(),
            'email' => $this->string(255)->notNull(),
            'verificationCode' => $this->string(255)->notNull(),
            'list' => $this->string(25)->defaultValue('default'),
            'site' => $this->string(255)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(EmaillistRecord::tableName());

        return true;
    }
}
