<?php

use Phinx\Migration\AbstractMigration;

class AddSubscriptionTypeItems extends AbstractMigration
{
    public function up()
    {
        $this->table('subscription_type_items')
            ->addColumn('subscription_type_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('amount', 'decimal', ['null' => false, 'precision' => 10, 'scale' => 2])
            ->addColumn('vat', 'integer', ['null' => false])
            ->addColumn('sorting', 'integer', ['null' => false, 'default' => 100])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('subscription_type_id', 'subscription_types')
            ->addIndex(['subscription_type_id', 'sorting'])
            ->create();

        $subscriptionTypes = $this->query("SELECT * FROM subscription_types");
        foreach ($subscriptionTypes as $st) {
            $name = $st['user_label'] ? $st['user_label'] : $st['name'];
            $this->query("INSERT INTO subscription_type_items (subscription_type_id,name,amount,vat,sorting,created_at,updated_at) VALUES ({$st['id']}, '{$name}', {$st['price']}, 20, 100, NOW(), NOW())");
        }

        $this->table('subscription_types')
            ->addColumn('fixed_start', 'datetime', ['null' => true, 'after' => 'extending_length'])
            ->update();

    }

    public function down()
    {
        $this->table('subscription_type_items')
            ->drop();

        $this->table('subscription_types')
            ->removeColumn('fixed_start')
            ->update();
    }
}
