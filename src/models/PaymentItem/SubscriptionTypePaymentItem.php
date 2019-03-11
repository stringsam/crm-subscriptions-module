<?php

namespace Crm\SubscriptionsModule\PaymentItem;

use Crm\PaymentsModule\PaymentItem\PaymentItemInterface;
use Nette\Database\Table\IRow;

class SubscriptionTypePaymentItem implements PaymentItemInterface
{
    const TYPE = 'subscription_type';

    private $subscriptionTypeItem;

    private $count;

    private $name = null;

    private $vat = null;

    private $price = null;

    public function __construct(
        IRow $subscriptionTypeItem,
        int $count = 1,
        $name = null,
        float $price = null,
        int $vat = null
    ) {
        $this->subscriptionTypeItem = $subscriptionTypeItem;
        $this->count = $count;
        $this->name = $name;
        $this->price = $price;
        $this->vat = $vat;
    }

    public static function fromSubscriptionType(IRow $subscriptionType)
    {
        $rows = [];
        foreach ($subscriptionType->related('subscription_type_items')->order('sorting') as $item) {
            $rows[] = new SubscriptionTypePaymentItem($item);
        }
        return $rows;
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function name(): string
    {
        if ($this->name) {
            return $this->name;
        }
        return $this->subscriptionTypeItem->name;
    }

    public function price(): float
    {
        if ($this->price !== null) {
            return $this->price;
        }
        return $this->subscriptionTypeItem->amount;
    }

    public function vat(): int
    {
        if ($this->vat !== null) {
            return $this->vat;
        }
        return $this->subscriptionTypeItem->vat;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function data(): array
    {
        return [
            'subscription_type_id' => $this->subscriptionTypeItem->subscription_type_id,
        ];
    }

    public function forceName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function forceVat(int $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

    public function forcePrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }
}
