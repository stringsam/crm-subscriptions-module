<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\WidgetInterface;
use Crm\PaymentsModule\Repository\PaymentGiftCouponsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI;

class UserSubscriptionsListing extends UI\Control implements WidgetInterface
{
    private $templateName = 'user_subscriptions_listing.latte';

    /** @var SubscriptionsRepository */
    private $subscriptionsRepository;

    /** @var Translator  */
    private $translator;

    /** @var PaymentGiftCouponsRepository */
    private $paymentGiftCouponsRepository;

    public function __construct(
        SubscriptionsRepository $subscriptionsRepository,
        PaymentGiftCouponsRepository $paymentGiftCouponsRepository,
        Translator $translator
    ) {
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->translator = $translator;
        $this->paymentGiftCouponsRepository = $paymentGiftCouponsRepository;
    }

    public function header($id = '')
    {
        $header = $this->translator->translate('subscriptions.admin.user_subscriptions.header');
        if ($id) {
            $header .= ' <small>(' . $this->totalCount($id) . ')</small>';
        }
        return $header;
    }

    public function identifier()
    {
        return 'usersubscriptions';
    }

    public function render($id)
    {
        $subscriptions = $this->subscriptionsRepository->userSubscriptions($id);
        $givenByEmail = [];

        foreach ($subscriptions as $subscription) {
            if ($subscription->type === SubscriptionsRepository::TYPE_GIFT) {
                $giftCoupon = $this->paymentGiftCouponsRepository->findBy('subscription_id', $subscription->id);
                if ($giftCoupon) {
                    $givenByEmail[$subscription->id] = $giftCoupon->payment->user->email;
                }
            }
        }
        $this->template->totalSubscriptions = $this->totalCount($id);
        $this->template->subscriptions = $subscriptions;
        $this->template->givenByEmail = $givenByEmail;
        $this->template->donatedSubscriptions = $this->subscriptionsRepository->userDonatedPayments($id);
        $this->template->id = $id;
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }

    private $totalCount = null;

    private function totalCount($id)
    {
        if ($this->totalCount == null) {
            $this->totalCount = $this->subscriptionsRepository->userSubscriptions($id)->count('*');
        }
        return $this->totalCount;
    }
}
