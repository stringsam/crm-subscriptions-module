<?php

namespace Crm\SubscriptionsModule\Extension;

use Nette\Database\Context;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class ExtendActualExtension implements ExtensionInterface
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getStartTime(IRow $user, IRow $subscriptionType)
    {
        $actualSubscription = $this->database->getConnection()->query("SELECT end_time, subscription_type_id FROM subscriptions WHERE user_id=? AND start_time < ? AND end_time > ? LIMIT 1", $user->id, new DateTime(), new DateTime())->fetch();
        if ($actualSubscription) {
            return new Extension($actualSubscription->end_time, $subscriptionType->id == $actualSubscription->subscription_type_id);
        }
        return new Extension(new DateTime());
    }
}
