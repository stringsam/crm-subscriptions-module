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

        $event1Query = <<<SQL
SELECT COUNT(DISTINCT(`user`.`id`)) 
FROM `subscriptions` 
LEFT JOIN `users` `user` ON `subscriptions`.`user_id` = `user`.`id` 
WHERE (`user`.`active` = 1) AND (`start_time` < NOW()) AND (`end_time` > NOW())
SQL;

        $event1 = <<<SQL
        CREATE EVENT current_subscribers_count
ON SCHEDULE
  EVERY 3 MINUTE
DO
  INSERT INTO stats (`key`, `value`)
  VALUES ('current_subscribers_count', ($event1Query))
  ON DUPLICATE KEY UPDATE value=VALUES(value);
SQL;
        $this->execute($event1);
    }

    public function down()
    {
        $this->execute('DROP TRIGGER IF EXISTS subscriptions_count_up');
        $this->execute('DROP TRIGGER IF EXISTS subscriptions_count_down');
        $this->execute('DROP EVENT IF EXISTS current_subscribers_count');
    }
}
