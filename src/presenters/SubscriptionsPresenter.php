<?php

namespace Crm\SubscriptionsModule\Presenters;

use Crm\ApplicationModule\Presenters\FrontendPresenter;
use Crm\SalesFunnelModule\Repository\SalesFunnelsRepository;
use Nette\Application\BadRequestException;
use Nette\Http\Request;

class SubscriptionsPresenter extends FrontendPresenter
{
    /** @var  Request @inject */
    public $request;

    /** @var SalesFunnelsRepository @inject */
    public $salesFunnelsRepository;

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
        $salesFunnel = $this->salesFunnelsRepository->findByUrlKey($funnel);
        if (!$salesFunnel) {
            throw new BadRequestException('invalid sales funnel urlKey: ' . $funnel);
        }

        $refererUrl = $this->request->getReferer();
        $referer = '';
        if ($refererUrl) {
            $referer = $refererUrl->__toString();
        }
        $this->template->referer = $referer;
        $this->template->salesFunnel = $salesFunnel->url_key;
        $this->template->paymentGatewayId = null;
        $this->template->subscriptionTypeId = null;
        $this->template->utmSource = $this->getParameter('utm_source');
        $this->template->utmMedium = $this->getParameter('utm_medium');
        $this->template->utmCampaign = $this->getParameter('utm_campaign');
        $this->template->utmContent = $this->getParameter('utm_content');
        $this->template->bannerVariant = $this->getParameter('banner_variant');
        if (isset($this->params['payment_gateway_id'])) {
            $this->template->paymentGatewayId = intval($this->params['payment_gateway_id']);
        }
        if (isset($this->params['subscription_type_id'])) {
            $this->template->subscriptionTypeId = intval($this->params['subscription_type_id']);
        }

        $showHeader = true;
        $this->template->showHeader = $showHeader;
    }
}
