<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;
use Nette\Utils\DateTime;

/**
 * This widget checks if user has active running subscription
 * and renders simple label 'Actual' in user listing.
 *
 * @package Crm\SubscriptionsModule\Components
 */
class ActualSubscriptionLabel extends BaseWidget
{
    private $templateName = 'actual_subscription_label.latte';

    public function identifier()
    {
        return 'actualsubscriptionlabel';
    }

    public function render($user)
    {
        if (!isset($user->start_time) || !isset($user->end_time)) {
            return;
        }

        $this->template->actual = $user->start_time < new DateTime() && $user->end_time > new DateTime();
        $this->template->startTime = $user->start_time;
        $this->template->endTime = $user->end_time;
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
