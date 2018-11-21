<?php

namespace Crm\SubscriptionsModule\Subscription;

use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Nette\Security\User;

class ActualUserSubscription
{
    private $user;

    private $subscriptionsRepository;

    private $isLoaded = false;

    private $actualSubscription;

    private $nextSubscription;

    public function __construct(
        User $user,
        SubscriptionsRepository $subscriptionsRepository
    ) {
        $this->user = $user;
        $this->subscriptionsRepository = $subscriptionsRepository;
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
            $this->nextSubscription = false;
            return;
        }

        $this->nextSubscription = $this->subscriptionsRepository->find($this->actualSubscription->next_subscription_id);
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

    public function hasNextSubscription()
    {
        $this->init();
        return $this->nextSubscription ? true : false;
    }

    public function getNextSubscription()
    {
        $this->init();
        return $this->nextSubscription;
    }
}
