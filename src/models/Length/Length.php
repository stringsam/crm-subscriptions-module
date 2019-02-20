<?php

namespace Crm\SubscriptionsModule\Length;

use DateTime;

class Length
{
    private $date;

    private $length;

    public function __construct(DateTime $date, int $length)
    {
        $this->date = $date;
        $this->length = $length;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
