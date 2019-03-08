<?php

namespace Crm\SubscriptionsModule\PaymentItem;

use Crm\PaymentsModule\PaymentItem\PaymentItemInterface;
use Nette\Database\Table\IRow;

class SubscriptionTypePaymentItem implements PaymentItemInterface
{
    const TYPE = 'subscription_type';

    private $subscriptionType;

    private $count;

    public function __construct(IRow $subscriptionType, int $count = 1)
    {
        $this->subscriptionType = $subscriptionType;
        $this->count = $count;
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function name(): string
    {
        return $this->subscriptionType->user_label ?? $this->subscriptionType->name;
    }

    public function price(): float
    {
        return $this->subscriptionType->price;
    }

    public function vat(): int
    {
        return 20; // TODO
    }

    public function count(): int
    {
        return $this->count;
    }

    public function data(): array
    {
        return [
            'subscription_type_id' => $this->subscriptionType->id,
        ];
    }
}