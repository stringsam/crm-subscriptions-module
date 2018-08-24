<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;

class ActualSubscriptionsStatWidget extends BaseWidget
{
    private $templateName = 'actual_subscriptions_stat_widget.latte';

    /** @var SubscriptionsRepository */
    private $subscriptionsRepository;

    public function __construct(
        WidgetManager $widgetManager,
        SubscriptionsRepository $subscriptionsRepository
    ) {
        parent::__construct($widgetManager);
        $this->subscriptionsRepository = $subscriptionsRepository;
    }

    public function identifier()
    {
        return 'actualsubscriptionsstatwidget';
    }

    public function render()
    {
        $this->template->actualSubscriptions = $this->subscriptionsRepository->actualSubscriptions()->count('*');
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
