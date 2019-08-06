<?php

use Phinx\Migration\AbstractMigration;

class MigrateSubscriptionTypeProductToNoSubscription extends AbstractMigration
{
    public function up()
    {
        $sql = <<<SQL
UPDATE `subscription_types`
SET `no_subscription` = 1
WHERE `type` = "product";
SQL;
        $this->execute($sql);
    }

    public function down()
    {
        $this->output->writeln(' -- <error>Down migration is not available.</error>');
        $this->output->writeln(' -- We are unable to determine if all `no_subscription` items are `product` type.');
    }
}
