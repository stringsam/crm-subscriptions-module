<?php

namespace Crm\SubscriptionsModule\Events;

use Crm\ApplicationModule\User\UserData;
use Crm\UsersModule\User\IUserGetter;
use League\Event\AbstractListener;
use League\Event\EventInterface;

class SubscriptionUpdatedHandler extends AbstractListener
{
    private $userData;

    public function __construct(UserData $userData)
    {
        $this->userData = $userData;
    }

    public function handle(EventInterface $event)
    {
        if (!($event instanceof IUserGetter)) {
            throw new \Exception('cannot handle event, invalid instance received: ' . gettype($event));
        }

        $userId = $event->getUserId();
        $this->userData->refreshUserTokens($userId);
    }
}
