<?php

namespace Crm\SubscriptionsModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class SubscriptionMetaRepository extends Repository
{
    protected $tableName = 'subscriptions_meta';

    public function add(IRow $subscription, string $key, $value, int $sorting = 100)
    {
        return $this->getTable()->insert([
            'subscription_id' => $subscription->id,
            'key' => $key,
            'value' => (string) $value,
            'sorting' => $sorting,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
    }

    public function subscriptionMeta(IRow $subscription): array
    {
        return $this->getTable()->where([
            'subscription_id' => $subscription->id,
        ])->order('sorting ASC')->fetchPairs('key', 'value');
    }

    public function exists(IRow $subscription, string $key): bool
    {
        return $this->getTable()->where(['subscription_id' => $subscription->id, 'key' => $key])->count('*') > 0;
    }

    public function setMeta(IRow $subscription, string $key, $value): IRow
    {
        if ($this->exists($subscription, $key)) {
            $this->getTable()->where(['subscription_id' => $subscription->id, 'key' => $key])->update(['value' => $value]);
            return $this->getTable()->where(['subscription_id' => $subscription->id, 'key' => $key])->limit(1)->fetch();
        } else {
            return $this->add($subscription, $key, $value);
        }
    }

    public function getMeta(IRow $subscription, string $key): string
    {
        return $this->getTable()->where(['subscription_id' => $subscription->id, 'key' => $key])->fetchField('value');
    }

    public function findSubscriptionBy(string $key, string $value)
    {
        $meta = $this->getTable()->where(['key' => $key, 'value' => $value])->limit(1)->fetch();
        if ($meta) {
            return $meta->subscription;
        }
        return false;
    }
}
