<?php

namespace Crm\SubscriptionsModule\Repository;

use Crm\ApplicationModule\Repository;

class SubscriptionTypeNamesRepository extends Repository
{
    protected $tableName = 'subscription_type_names';

    public function add($type, $sorting)
    {
        return $this->getTable()->insert([
            'type' => $type,
            'sorting' => $sorting,
        ]);
    }

    public function exists($type)
    {
        return $this->getTable()->where(['type' => $type])->count('*');
    }
}
