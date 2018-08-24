<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\SubscriptionsModule\Subscription\ActualUserSubscription;

class ActualSubscriptionWidget extends BaseWidget
{
    private $templateName = 'actual_subscription_widget.latte';

    private $actualUserSubscription;

    public function __construct(
        WidgetManager $widgetManager,
        ActualUserSubscription $actualUserSubscription
    ) {
        parent::__construct($widgetManager);
        $this->actualUserSubscription = $actualUserSubscription;
    }

    public function header($id = '')
    {
        return 'Actual subscription message';
    }

    public function identifier()
    {
        return 'useractualsubscriptionsmessage';
    }

    public function render($id)
    {
        $this->template->actualUserSubscription = $this->actualUserSubscription;
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
