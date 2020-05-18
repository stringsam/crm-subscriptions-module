<?php

use Phinx\Migration\AbstractMigration;

class FixingSubscriptionLengthType extends AbstractMigration
{
    public function change()
    {
        $this->execute("UPDATE subscriptions SET length = DATEDIFF(end_time, start_time) WHERE length IS NULL OR length = ''");
        $this->table('subscriptions')
            ->changeColumn('length', 'integer', ['null' => false])
            ->update();
    }
}
