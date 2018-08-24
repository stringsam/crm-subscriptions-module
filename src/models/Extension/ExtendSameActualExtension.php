<?php

namespace Crm\SubscriptionsModule\Extension;

use Nette\Database\Context;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class ExtendSameActualExtension implements ExtensionInterface
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getStartTime(IRow $user, IRow $subscriptionType)
    {
        $actualSubscriptions = $this->database->getConnection()->query("SELECT end_time, subscription_type_id FROM subscriptions WHERE user_id=? AND start_time < ? AND end_time > ?", $user->id, new DateTime(), new DateTime())->fetchAll();
        foreach ($actualSubscriptions as $subscription) {
            if ($subscription->subscription_type_id == $subscriptionType->id) {
                return new Extension($subscription->end_time, true);
            }
        }
        return new Extension(new DateTime());
    }
}
