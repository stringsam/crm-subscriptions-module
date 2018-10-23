<?php

namespace Crm\SubscriptionsModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class SubscriptionTypesMetaRepository extends Repository
{
    protected $tableName = 'subscription_types_meta';

    public function add(IRow $subscriptionType, string $key, $value, int $sorting = 100)
    {
        return $this->getTable()->insert([
            'subscription_type_id' => $subscriptionType->id,
            'key' => $key,
            'value' => (string) $value,
            'sorting' => $sorting,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
    }

    public function subscriptionTypeMeta(IRow $subscriptionType): array
    {
        return $this->getTable()->where([
            'subscription_type_id' => $subscriptionType->id,
        ])->order('sorting ASC')->fetchPairs('key', 'value');
    }

    public function exists(IRow $subscriptionType, string $key): bool
    {
        return $this->getTable()->where(['subscription_type_id' => $subscriptionType->id, 'key' => $key])->count('*') > 0;
    }

    public function setMeta(IRow $subscriptionType, string $key, $value): IRow
    {
        if ($this->exists($subscriptionType, $key)) {
            $this->getTable()->where(['subscription_type_id' => $subscriptionType->id, 'key' => $key])->update(['value' => $value]);
            return $this->getTable()->where(['subscription_type_id' => $subscriptionType->id, 'key' => $key])->limit(1)->fetch();
        } else {
            return $this->add($subscriptionType, $key, $value);
        }
    }
}
