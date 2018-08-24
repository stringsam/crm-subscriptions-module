<?php

namespace Crm\SubscriptionsModule\Extension;

use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class StartNowExtension implements ExtensionInterface
{
    public function getStartTime(IRow $user, IRow $subscriptionType)
    {
        return new Extension(new DateTime());
    }
}
