<?php

namespace Crm\SubscriptionsModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class SubscriptionTypeItemsRepository extends Repository
{
    protected $tableName = 'subscription_type_items';

    final public function add(IRow $subscriptionType, string $name, float $amount, int $vat, int $sorting = null)
    {
        return $this->getTable()->insert([
            'subscription_type_id' => $subscriptionType->id,
            'name' => $name,
            'amount' => $amount,
            'vat' => $vat,
            'sorting' => $sorting ? $sorting : $this->getNextSorting($subscriptionType),
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
    }

    final public function exists(IRow $subscriptionType, string $name)
    {
        return $this->getTable()->where(['subscription_type_id' => $subscriptionType->id, 'name' => $name])->count('*');
    }

    final public function subscriptionTypeItems(IRow $subscriptionType)
    {
        return $this->getTable()->where(['subscription_type_id' => $subscriptionType->id])->order('sorting ASC');
    }

    private function getNextSorting(IRow $subscriptionType)
    {
        $item = $this->getTable()->where(['subscription_type_id' => $subscriptionType->id])->order('sorting DESC')->limit(1)->fetch();
        if (!$item) {
            return 100;
        }
        return $item->sorting + 100;
    }
}
