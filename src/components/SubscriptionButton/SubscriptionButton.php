<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;

class SubscriptionButton extends BaseWidget
{
    private $templateName = 'subscription_button.latte';

    public function header()
    {
        return 'Subscription';
    }

    public function identifier()
    {
        return 'usersubscription';
    }

    public function render($payment)
    {
        if (!$payment->subscription_id) {
            return;
        }

        $this->template->payment = $payment;
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
