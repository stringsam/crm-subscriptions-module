<?php

namespace Crm\SubscriptionsModule\Length;

use DateTime;
use Nette\Database\Table\IRow;

class FixDaysLengthMethod implements LengthMethodInterface
{
    public function getEndTime(DateTime $startTime, IRow $user, IRow $subscriptionType, bool $isExtending = false): Length
    {
        $length = $subscriptionType->length;
        if ($isExtending && $subscriptionType->extending_length) {
            $length = $subscriptionType->extending_length;
        }
        $interval = new \DateInterval("P{$length}D");
        $end = (clone $startTime)->add($interval);

        if ($subscriptionType->fixed_end) {
            $end = $subscriptionType->fixed_end;
        }

        return new Length($end, $length);
    }

    public function getStartTime(DateTime $endTime, IRow $subscriptionType, bool $isExtending = false): Length
    {
        $length = $subscriptionType->length;
        if ($isExtending && $subscriptionType->extending_length) {
            $length = $subscriptionType->extending_length;
        }
        $interval = (new \DateInterval("P{$length}D"));
        $start = (clone $endTime)->sub($interval);

        if ($subscriptionType->fixed_start) {
            $start = $subscriptionType->fixed_start;
        }

        return new Length($start, $length);
    }
}
