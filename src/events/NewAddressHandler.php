<?php

namespace Crm\SubscriptionsModule\Events;

use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use League\Event\AbstractListener;
use League\Event\EventInterface;
use Nette\Utils\DateTime;

class NewAddressHandler extends AbstractListener
{
    private $subscriptionsRepository;

    public function __construct(SubscriptionsRepository $subscriptionsRepository)
    {
        $this->subscriptionsRepository = $subscriptionsRepository;
    }

    public function handle(EventInterface $event)
    {
        $address = $event->getAddress();

        if ($address->type != 'print') {
            return;
        }

        $subscription = $this->subscriptionsRepository->actualUserSubscription($address->user_id);
        if ($subscription && ($subscription->subscription_type->print || $subscription->subscription_type->print_friday) && !$subscription->address_id) {
            $this->subscriptionsRepository->update($subscription, [
                'address_id' => $address->id,
                'modified_at' => new DateTime(),
            ]);
        }
    }
}
