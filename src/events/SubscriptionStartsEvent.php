<?php

namespace Crm\SubscriptionsModule\Events;

use League\Event\AbstractEvent;
use Nette\Database\IRow;

class SubscriptionStartsEvent extends AbstractEvent
{
    /** @var IRow  */
    private $subscription;

    public function __construct(IRow $subscription)
    {
        $this->subscription = $subscription;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }
}
