<?php

namespace Crm\SubscriptionsModule\Subscription;

use Crm\PaymentsModule\Repository\PaymentsRepository;
use Crm\PaymentsModule\Repository\RecurrentPaymentsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Crm\PaymentsModule\Upgrade\Expander;
use Nette\Security\User;

class ActualUserSubscription
{
    private $user;

    private $subscriptionsRepository;

    private $paymentsRepository;

    private $recurrentPaymentsRepository;

    private $expander;

    private $isLoaded = false;

    private $actualSubscription;

    private $payment = false;

    private $isRecurrent = false;

    private $recurrent = false;

    public function __construct(
        User $user,
        SubscriptionsRepository $subscriptionsRepository,
        PaymentsRepository $paymentsRepository,
        RecurrentPaymentsRepository $recurrentPaymentsRepository,
        Expander $expander
    ) {
        $this->user = $user;
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->recurrentPaymentsRepository = $recurrentPaymentsRepository;
        $this->expander = $expander;
    }

    private function init()
    {
        if ($this->isLoaded) {
            return;
        }

        $this->isLoaded = true;

        if (!$this->user->isLoggedIn()) {
            return;
        }

        $this->actualSubscription = $this->subscriptionsRepository->actualUserSubscription($this->user->getId());
        if (!$this->actualSubscription) {
            return;
        }

        $this->payment = $this->paymentsRepository->subscriptionPayment($this->actualSubscription);

        if ($this->payment) {
            $this->isRecurrent = $this->payment->payment_gateway->is_recurrent ? true : false;

            if ($this->isRecurrent) {
                $this->recurrent = $this->recurrentPaymentsRepository->recurrent($this->payment);
            }
        }
    }

    public function hasActual()
    {
        $this->init();
        return $this->actualSubscription ? true : false;
    }

    public function getSubscription()
    {
        $this->init();
        return $this->actualSubscription;
    }

    public function getPayment()
    {
        $this->init();
        return $this->payment;
    }

    public function isRecurrent()
    {
        return $this->isRecurrent;
    }

    public function isActiveRecurrent()
    {
        $this->init();
        if ($this->recurrent) {
            return $this->recurrent->state == RecurrentPaymentsRepository::STATE_ACTIVE;
        }
        return false;
    }

    public function getRecurrent()
    {
        $this->init();
        return $this->recurrent;
    }

    public function canUpgrade($service)
    {
        $this->init();
        return $this->expander->canUpgrade($service);
    }
}
