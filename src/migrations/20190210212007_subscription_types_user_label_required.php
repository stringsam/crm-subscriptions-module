<?php

use Phinx\Migration\AbstractMigration;

class SubscriptionTypesUserLabelRequired extends AbstractMigration
{
    public function up()
    {
        // fill missing user labels
        $this->query("UPDATE `subscription_types` SET `user_label` = `name` WHERE `user_label` = '' OR `user_label` IS NULL;");

        $this->table('subscription_types')
            ->changeColumn('user_label', 'string', ['null' => false])
            ->update();
    }

    public function down()
    {
        $this->table('subscription_types')
            ->changeColumn('user_label', 'string', ['null' => true])
            ->update();
    }
}
