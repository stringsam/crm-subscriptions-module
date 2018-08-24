<?php

namespace Crm\SubscriptionsModule\Repository;

use Crm\ApplicationModule\Repository;
use DateTime;
use Nette\Database\IRow;

class SubscriptionTypesUpgradesRepository extends Repository
{
    protected $tableName = 'subscription_types_upgrades';

    public function add(IRow $fromSubscriptionType, IRow $toSubscriptionType, $type = 'default')
    {
        return $this->insert([
            'from_subscription_type_id' => $fromSubscriptionType->id,
            'to_subscription_type_id' => $toSubscriptionType->id,
            'created_at' => new DateTime(),
            'type' => $type,
        ]);
    }

    public function remove(IRow $fromSubscriptionType, IRow $toSubscriptionType)
    {
        return $this->getTable()->where([
            'from_subscription_type_id' => $fromSubscriptionType->id,
            'to_subscription_type_id' => $toSubscriptionType->id,
        ])->delete();
    }

    public function exists(IRow $fromSubscriptionType, IRow $toSubscriptionType)
    {
        return $this->getTable()->where([
            'from_subscription_type_id' => $fromSubscriptionType->id,
            'to_subscription_type_id' => $toSubscriptionType->id,
        ])->count('*');
    }

    public function availableUpgrades(IRow $fromSubscriptionType, $service = null, $type = null)
    {
        $where = ['from_subscription_type_id' => $fromSubscriptionType->id];
        if (in_array($service, ['web', 'print', 'mobile', 'club', 'print_friday'])) {
            $where['subscription_type.' . $service] = true;
        }
        if ($type) {
            $where['subscription_types_upgrades.type'] = $type;
        }
        return $this->getTable()->where($where);
    }

    public function alreadyUpgraded(IRow $fromSubscriptionType, $type = null)
    {
        $where = [
            'to_subscription_type_id' => $fromSubscriptionType->id
        ];
        if ($type) {
            $where['subscription_types_upgrades.type'] = $type;
        }
        return $this->getTable()->where($where)->count('*') > 0;
    }
}
