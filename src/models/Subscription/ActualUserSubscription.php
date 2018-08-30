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
            return;
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
}
