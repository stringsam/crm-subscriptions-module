<?php

namespace Crm\SubscriptionsModule\Presenters;

use Crm\ApplicationModule\Presenters\FrontendPresenter;

class SubscriptionsPresenter extends FrontendPresenter
{
    public function renderMy()
    {
        $this->onlyLoggedIn();

        $this->template->showTrackingCode = false;
        $session = $this->getSession('success_login');
        if (isset($session->success) && $session->success == 'success') {
            unset($session->success);
            $this->template->showTrackingCode = true;
        }

        $this->template->subscriptions = $this->subscriptionsRepository->userSubscriptions($this->getUser()->getId());
    }

    public function renderNew($funnel = null)
    {
        $homepage = $this->applicationConfig->get('homepage_url');
        if ($homepage) {
            $this->redirectUrl($homepage);
        }

        if ($funnel === null) {
            $funnel = $this->applicationConfig->get('default_sales_funnel_url_key');
        }
        $this->template->funnel = $funnel;

        $showHeader = true;
        $this->template->showHeader = $showHeader;
    }
}
