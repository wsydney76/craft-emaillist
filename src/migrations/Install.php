<?php

namespace wsydney76\emaillist\migrations;

use Craft;
use craft\db\Migration;
use wsydney76\emaillist\records\RegistrationRecord;

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
        $this->createTable(RegistrationRecord::tableName(), [
            'id' => $this->primaryKey(),
            'email' => $this->string(255)->notNull(),
            'verificationCode' => $this->string(255)->notNull(),
            'list' => $this->string(25)->defaultValue('default'),
            'site' => $this->string(255)->notNull(),
            'active' => $this->boolean()->defaultValue(true),
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
        $this->dropTableIfExists(RegistrationRecord::tableName());

        return true;
    }
}
