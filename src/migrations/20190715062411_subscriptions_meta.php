<?php

use Phinx\Migration\AbstractMigration;

class SubscriptionsMeta extends AbstractMigration
{
    public function change()
    {
        $this->table('subscriptions_meta')
            ->addColumn('subscription_id', 'integer')
            ->addColumn('key', 'string')
            ->addColumn('value', 'string')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->addColumn('sorting', 'integer', ['default' => 100])
            ->addForeignKey('subscription_id', 'subscriptions')
            ->addIndex(['subscription_id', 'key'], ['unique' => true])
            ->create();
    }
}
