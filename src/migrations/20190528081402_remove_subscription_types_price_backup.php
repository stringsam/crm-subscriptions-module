<?php

use Phinx\Migration\AbstractMigration;

class RemoveSubscriptionTypesPriceBackup extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('subscription_types');
        if ($table->hasColumn('price_backup')) {
            $table->removeColumn('price_backup')
                ->update();
        }
    }

    public function down()
    {
        $this->output->writeln('Down migration is not available. `price_backup` was legacy backup column not used by CRM code.');
    }
}
