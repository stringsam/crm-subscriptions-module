<?php

namespace Crm\SubscriptionsModule\Repository;

use Crm\ApplicationModule\Repository;
use DateTime;
use Nette\Database\IRow;

class SubscriptionPaymentsRepository extends Repository
{
    protected $tableName = 'subscription_payments';

    public function add(IRow $subscription, IRow $payment)
    {
        return $this->insert([
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
            'created_at' => new DateTime(),
        ]);
    }
}
