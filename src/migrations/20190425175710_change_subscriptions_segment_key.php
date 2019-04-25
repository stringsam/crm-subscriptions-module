<?php

use Phinx\Migration\AbstractMigration;

class ChangeSubscriptionsSegmentKey extends AbstractMigration
{
    public function up()
    {
        $this->query('UPDATE segments SET criteria=REPLACE(criteria, \'"key":"active_subscription"\', \'"key":"users_active_subscription"\')');
        $this->query('UPDATE segments SET criteria=REPLACE(criteria, \'"key":"inactive_subscription"\', \'"key":"users_inactive_subscription"\')');
    }

    public function down()
    {
        $this->query('UPDATE segments SET criteria=REPLACE(criteria, \'"key":"users_active_subscription"\', \'"key":"active_subscription"\')');
        $this->query('UPDATE segments SET criteria=REPLACE(criteria, \'"key":"users_inactive_subscription"\', \'"key":"inactive_subscription"\')');
    }
}
