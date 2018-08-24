<?php

namespace Crm\SubscriptionsModule\Length;

use DateTime;
use Nette\Database\Table\IRow;

class CalendarDaysLengthMethod implements LengthMethodInterface
{
    public function getEndTime(DateTime $startTime, IRow $user, IRow $subscriptionType, bool $isExtending): Length
    {
        $length = cal_days_in_month(CAL_GREGORIAN, $startTime->format('m'), $startTime->format('Y'));
        $interval = new \DateInterval("P{$length}D");
        $end = (clone $startTime)->add($interval);
        return new Length($end, $length);
    }
}
