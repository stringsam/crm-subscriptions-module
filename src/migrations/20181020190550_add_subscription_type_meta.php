<?php

use Phinx\Migration\AbstractMigration;

class AddSubscriptionTypeMeta extends AbstractMigration
{
    public function change()
    {
        $this->table('subscription_types_meta')
            ->addColumn('subscription_type_id', 'integer', ['null' => false])
            ->addColumn('key', 'string', ['null' => false])
            ->addColumn('value', 'string', ['null' => true])
            ->addColumn('sorting', 'integer', ['null' => false, 'default' => 100])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('subscription_type_id', 'subscription_types')
            ->addIndex(['subscription_type_id', 'key'], ['unique' => true])
            ->create();
    }
}
