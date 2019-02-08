<?php


use Phinx\Migration\AbstractMigration;

class SubscriptionTriggers extends AbstractMigration
{
    public function up()
    {
        $q1 = <<<SQL

CREATE TRIGGER `subscriptions_count_up` AFTER INSERT ON `users` FOR EACH ROW UPDATE `subscriptions`
SET 
  `stats`.`value` = `stats`.`value` + 1 
WHERE
  `stats`.`key` = "subscriptions_count";
SQL;

        $q2 = <<<SQL
CREATE TRIGGER `subscriptions_count_down` AFTER DELETE ON `users` FOR EACH ROW UPDATE `subscriptions`
SET 
  `stats`.`value` = `stats`.`value` - 1 
WHERE
  `stats`.`key` = "subscriptions_count";
SQL;
        $this->execute($q1);
        $this->execute($q2);
    }

    public function down()
    {
        $this->execute('DROP TRIGGER IF EXISTS subscriptions_count_up');
        $this->execute('DROP TRIGGER IF EXISTS subscriptions_count_down');
    }
}
