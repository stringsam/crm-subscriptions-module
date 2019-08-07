<?php

use Phinx\Migration\AbstractMigration;

class RemoveTypeOfSubscriptionTypes extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('subscription_types');
        if ($table->hasColumn('type')) {
            $table
                ->removeColumn('type')
                ->update();
        }
    }

    public function down()
    {
        $table = $this->table('subscription_types');
        if (!$table->hasColumn('type')) {
            $table
                ->addColumn('type', 'string', ['null' => false, 'default' => 'time_archive', 'after' => 'id'])
                ->update();
        }
    }
}
