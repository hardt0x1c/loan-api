<?php

use yii\db\Migration;

class m260220_000002_add_updated_at_to_loan_requests extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%loan_requests}}', 'updated_at', $this->timestamp()->notNull()->defaultExpression('NOW()'));
        
        $this->execute('
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
               NEW.updated_at = NOW();
               RETURN NEW;
            END;
            $$ language \'plpgsql\';
        ');
        $this->execute('
            CREATE TRIGGER update_loan_requests_updated_at
            BEFORE UPDATE ON {{%loan_requests}}
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
        ');
    }

    public function safeDown()
    {
        $this->execute('DROP TRIGGER IF EXISTS update_loan_requests_updated_at ON {{%loan_requests}}');
        $this->execute('DROP FUNCTION IF EXISTS update_updated_at_column()');
        $this->dropColumn('{{%loan_requests}}', 'updated_at');
    }
}
