<?php

namespace Crm\SubscriptionsModule\Events;

use Crm\UsersModule\Events\PreNotificationEvent;
use League\Event\AbstractListener;
use League\Event\EventInterface;

class PreNotificationEventHandler extends AbstractListener
{
    public function handle(EventInterface $event)
    {
        if (!($event instanceof PreNotificationEvent)) {
            throw new \Exception('PreNotificationEvent object expected, instead ' . get_class($event) . ' received');
        }

        $notificationEvent = $event->getNotificationEvent();
        $params = $notificationEvent->getParams();

        // Add subscription context to subscription notification
        if (isset($params['subscription']) && empty($notificationEvent->getContext())) {
            $notificationEvent->setContext('subscription.' . $params['subscription']['id']);
        }
    }
}
