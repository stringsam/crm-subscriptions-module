<?php

namespace Crm\SubscriptionsModule\Events;

use Crm\InvoicesModule\InvoiceGenerator;
use Crm\PaymentsModule\Repository\PaymentsRepository;
use Crm\UsersModule\Events\PreNotificationEvent;
use League\Event\AbstractListener;
use League\Event\EventInterface;

class PreNotificationEventHandler extends AbstractListener
{
    private $invoiceGenerator;

    private $paymentsRepository;

    public function __construct(
        InvoiceGenerator $invoiceGenerator,
        PaymentsRepository $paymentsRepository
    ) {
        $this->invoiceGenerator = $invoiceGenerator;
        $this->paymentsRepository = $paymentsRepository;
    }

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
