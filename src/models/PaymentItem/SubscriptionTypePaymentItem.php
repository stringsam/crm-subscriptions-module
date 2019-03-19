<?php

namespace Crm\SubscriptionsModule\PaymentItem;

use Crm\PaymentsModule\PaymentItem\PaymentItemInterface;
use Nette\Database\Table\IRow;

class SubscriptionTypePaymentItem implements PaymentItemInterface
{
    const TYPE = 'subscription_type';

    private $subscriptionTypeId;

    private $count;

    private $name;

    private $vat;

    private $price;

    public function __construct(
        int $subscriptionTypeId,
        string $name,
        float $price,
        int $vat,
        int $count = 1
    ) {
        $this->subscriptionTypeId = $subscriptionTypeId;
        $this->name = $name;
        $this->price = $price;
        $this->vat = $vat;
        $this->count = $count;
    }

    /**
     * @param IRow $subscriptionType
     * @param int $count
     * @return static[]
     */
    public static function fromSubscriptionType(IRow $subscriptionType, int $count = 1): array
    {
        $rows = [];
        foreach ($subscriptionType->related('subscription_type_items')->order('sorting') as $item) {
            $rows[] = static::fromSubscriptionTypeItem($item, $count);
        }
        return $rows;
    }

    /**
     * @param IRow $subscriptionTypeItem
     * @param int $count
     * @return static
     */
    public static function fromSubscriptionTypeItem(IRow $subscriptionTypeItem, int $count = 1)
    {
        return new SubscriptionTypePaymentItem(
            $subscriptionTypeItem->subscription_type_id,
            $subscriptionTypeItem->name,
            $subscriptionTypeItem->amount,
            $subscriptionTypeItem->vat,
            $count
        );
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function vat(): int
    {
        return $this->vat;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function data(): array
    {
        return [
            'subscription_type_id' => $this->subscriptionTypeId,
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
