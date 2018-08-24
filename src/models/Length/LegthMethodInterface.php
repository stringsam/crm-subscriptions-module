<?php

namespace Crm\SubscriptionsModule\Length;

use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

interface LengthMethodInterface
{
    public function getEndTime(DateTime $startTime, IRow $user, IRow $subscriptionType, bool $isExtending): Length;
}
