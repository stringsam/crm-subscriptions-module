<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\WidgetFactoryInterface;
use Crm\ApplicationModule\Widget\WidgetManager;

class SubscriptionButtonFactory implements WidgetFactoryInterface
{
    /** @var WidgetManager */
    protected $widgetManager;

    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    public function create()
    {
        $invoiceButton = new SubscriptionButton($this->widgetManager);
        return $invoiceButton;
    }
}
