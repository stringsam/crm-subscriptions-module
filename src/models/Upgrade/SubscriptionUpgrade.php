<?php

namespace Crm\SubscriptionsModule\Upgrade;

use Nette\Database\Table\ActiveRow;

class SubscriptionUpgrade
{
    private $message;

    private $canUpgrade;

    private $payment;

    private $subscription;

    private $availableUpgraders;

    private $upgradePrice;

    public function __construct(
        $message,
        $canUpgrade = false,
        ActiveRow $payment = null,
        ActiveRow $subscription = null,
        array $availableUpgraders = null,
        $upgradePrice = null
    ) {
        $this->message = $message;
        $this->canUpgrade = $canUpgrade;
        $this->payment = $payment;
        $this->subscription = $subscription;
        $this->availableUpgraders = $availableUpgraders;
        $this->upgradePrice = $upgradePrice;

        // precalculate charge prices based on actual data so they can be displayed in view before actual upgrade

        if (!empty($availableUpgraders)) {
            /** @var Upgrader $upgrader */
            foreach ($availableUpgraders as $upgrader) {
                $upgrader->calculateChargePrice($payment, $subscription);
            }
        }
    }

    public function canUpgrade()
    {
        return $this->canUpgrade;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getPayment()
    {
        return $this->payment;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }

    public function getAvailableUpgraders()
    {
        return $this->availableUpgraders;
    }

    public function getUpgradePrice()
    {
        return $this->upgradePrice;
    }
}
