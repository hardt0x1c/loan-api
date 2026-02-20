<?php

use yii\db\Migration;

class m260220_000001_create_loan_requests_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%loan_requests}}', [
            'id' => $this->bigPrimaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount' => $this->integer()->notNull(),
            'term' => $this->integer()->notNull(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'processed_at' => $this->timestamp()->null(),
        ]);

        $this->createIndex('idx-loan_requests-status', '{{%loan_requests}}', 'status');
        $this->createIndex('idx-loan_requests-user_id', '{{%loan_requests}}', 'user_id');
        $this->createIndex('idx-loan_requests-user_id-status', '{{%loan_requests}}', ['user_id', 'status']);

        $this->execute(
            "CREATE UNIQUE INDEX idx-loan_requests-user_id-approved-unique "
            . "ON {{%loan_requests}} (user_id) WHERE status = 'approved'"
        );
    }

    public function safeDown()
    {
        $this->execute('DROP INDEX IF EXISTS idx-loan_requests-user_id-approved-unique');

        $this->dropIndex('idx-loan_requests-user_id-status', '{{%loan_requests}}');
        $this->dropIndex('idx-loan_requests-user_id', '{{%loan_requests}}');
        $this->dropIndex('idx-loan_requests-status', '{{%loan_requests}}');

        $this->dropTable('{{%loan_requests}}');
    }
}
