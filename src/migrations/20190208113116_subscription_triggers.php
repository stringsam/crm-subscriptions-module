<?php

use Crm\ApplicationModule\Helpers;
use Crm\ApplicationModule\Stats\StatsRepository;
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

        $currentSubscribersCountQuery = <<<SQL
SELECT COUNT(DISTINCT(`user`.`id`)) 
FROM `subscriptions` 
LEFT JOIN `users` `user` ON `subscriptions`.`user_id` = `user`.`id` 
WHERE `user`.`active` = 1 AND `start_time` < NOW() AND `end_time` > NOW()
SQL;
        $eventSql = Helpers::lockableScheduledEvent(
            'current_subscribers_count_event',
            3,
            StatsRepository::insertOrUpdateQuery('current_subscribers_count', $currentSubscribersCountQuery));
        $this->execute($eventSql);
    }

    public function down()
    {
        $this->execute('DROP TRIGGER IF EXISTS subscriptions_count_up');
        $this->execute('DROP TRIGGER IF EXISTS subscriptions_count_down');
        $this->execute('DROP EVENT IF EXISTS current_subscribers_count_event');
    }
}
