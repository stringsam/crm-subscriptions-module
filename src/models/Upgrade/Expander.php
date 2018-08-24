<?php

namespace Crm\SubscriptionsModule\Upgrade;

use Crm\PaymentsModule\GatewayFactory;
use Crm\PaymentsModule\Repository\PaymentGatewaysRepository;
use Crm\PaymentsModule\Repository\PaymentLogsRepository;
use Crm\PaymentsModule\Repository\PaymentsRepository;
use Crm\PaymentsModule\Repository\RecurrentPaymentsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesUpgradesRepository;
use Crm\UsersModule\Repository\UserActionsLogRepository;
use League\Event\Emitter;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tomaj\Hermes\Dispatcher;

class Expander
{
    const UPGRADE_RECURRENT_FREE      = 'recurrent_free';
    const UPGRADE_RECURRENT           = 'recurrent';
    const UPGRADE_SHORT               = 'short';
    const UPGRADE_PAID_EXTEND         = 'paid_extend';
    const UPGRADE_SPECIAL             = 'special';
    const UPGRADE_SPECIAL_RECURRENT   = 'special_recurrent';

    /** @var User  */
    private $user;

    private $subscriptionsRepository;

    private $paymentsRepository;

    private $paymentLogsRepository;

    private $recurrentPaymentsRepository;

    private $subscriptionTypesRepository;

    private $subscriptionTypesUpgradesRepository;

    private $emitter;

    private $dispatcher;

    private $gatewayFactory;

    private $paymentGatewaysRepository;

    private $userActionsLogRepository;

    private $action = false;

    private $factory;

    public function __construct(
        User $user,
        SubscriptionsRepository $subscriptionsRepository,
        PaymentsRepository $paymentsRepository,
        PaymentLogsRepository $paymentLogsRepository,
        RecurrentPaymentsRepository $recurrentPaymentsRepository,
        SubscriptionTypesRepository $subscriptionTypesRepository,
        SubscriptionTypesUpgradesRepository $subscriptionTypesUpgradesRepository,
        PaymentGatewaysRepository $paymentGatewaysRepository,
        Emitter $emitter,
        Dispatcher $dispatcher,
        GatewayFactory $gatewayFactory,
        UserActionsLogRepository $userActionsLogRepository,
        UpgradeFactory $factory
    ) {
        $this->user = $user;
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->paymentLogsRepository = $paymentLogsRepository;
        $this->recurrentPaymentsRepository = $recurrentPaymentsRepository;
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
        $this->subscriptionTypesUpgradesRepository = $subscriptionTypesUpgradesRepository;
        $this->paymentGatewaysRepository = $paymentGatewaysRepository;
        $this->emitter = $emitter;
        $this->dispatcher = $dispatcher;
        $this->gatewayFactory = $gatewayFactory;
        $this->userActionsLogRepository = $userActionsLogRepository;
        $this->factory = $factory;
    }

    public function setFix($fix)
    {
        $this->factory->setDailyFix($fix / 31); // internally we want to store daily price for fix
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param $service
     *
     * @return SubscriptionUpgrade
     * @throws \Exception
     */
    public function canUpgrade($service)
    {
        if (!$this->user->isLoggedIn()) {
            return new SubscriptionUpgrade('Aktuálne nemáte žiadne predplatné alebo nie ste prihlásený. Prosím, <a target="_top" href="/sign/in">prihláste sa</a> alebo si vyberte z našej <a href="/" target="_top">ponuky predplatných</a>.');
        }

        $actualUserSubscription = $this->subscriptionsRepository->actualUserSubscription($this->user->id);
        if (!$actualUserSubscription) {
            return new SubscriptionUpgrade('Aktuálne nemáte žiadne predplatné alebo nie ste prihlásený. Prosím, <a target="_top" href="/sign/in">prihláste sa</a> alebo si vyberte z našej <a href="/" target="_top">ponuky predplatných</a>.');
        }

        $basePayment = $this->paymentsRepository->subscriptionPayment($actualUserSubscription);
        if (!$basePayment) {
            $this->userActionsLogRepository->add($this->user->getId(), 'upgrade.cannot_upgrade', ['subscription_id' => $actualUserSubscription->id, 'subscription_type_id' => $actualUserSubscription->subscription_type_id]);
            return new SubscriptionUpgrade('Prechod na vyšší balíček predplatného z vášho konta nie je možné vykonať automaticky, prosím, napíšte nám na info@dennikn.sk a radi vám pomôžeme.');
        }

        // najdeme do akeho predplatneho je mozne spravit upgrade
        $type = 'default';
        if ($this->action) {
            $type = 'action';
        }
        $availableSubscriptionTypes = $this->subscriptionTypesUpgradesRepository->availableUpgrades($actualUserSubscription->subscription_type, $service, $type);
        $availableUpgrades = [];
        if ($availableSubscriptionTypes->count('*') == 0) {
            if ($this->subscriptionTypesUpgradesRepository->alreadyUpgraded($actualUserSubscription->subscription_type, $type)) {
                $this->userActionsLogRepository->add($this->user->getId(), 'upgrade.already_upgraded', ['subscription_id' => $actualUserSubscription->id, 'subscription_type_id' => $actualUserSubscription->subscription_type_id]);
                return new SubscriptionUpgrade('Tento vyšší balíček predplatného už teraz máte aktívny, upgrade cez túto stránku teda nie je potrebný.', false, $basePayment, $actualUserSubscription);
            }

            $this->userActionsLogRepository->add($this->user->getId(), 'upgrade.unsupported_subscription_type', ['subscription_id' => $actualUserSubscription->id, 'subscription_type_id' => $actualUserSubscription->subscription_type_id]);
            return new SubscriptionUpgrade('Váš aktuálny typ predplatného nie je možné zmeniť na vyšší typ.', false, $basePayment, $actualUserSubscription);
        }

        foreach ($availableSubscriptionTypes as $availableSubscriptionType) {
            if ($basePayment->payment_gateway->is_recurrent) {
                $recurrent = $this->recurrentPaymentsRepository->recurrent($basePayment);
                if (!$recurrent) {
                    continue;
                }
                if ($recurrent->subscription_type_id != $actualUserSubscription->subscription_type_id) {
                    continue;
                }
                // skontroluj ci mu nekonci karta skor ako o mesiac
                if ($recurrent->expires_at < DateTime::from('+1 month')) {
                    return new SubscriptionUpgrade('Váša karta ma blízku dobu expirácie. Nie je možné zmeniť na vyšší typ.', false, $basePayment, $actualUserSubscription);
                }

                $remainingDiff = (new DateTime())->diff($actualUserSubscription->end_time);

                if ($remainingDiff->days >= 5) {
                    $availableUpgrades[] = $this->factory->build(
                        self::UPGRADE_RECURRENT,
                        $availableSubscriptionType
                    );
                } else {
                    $availableUpgrades[] = $this->factory->build(
                        self::UPGRADE_RECURRENT_FREE,
                        $availableSubscriptionType
                    );
                }
            } else {
                /** @var ShortUpgrade $shortUpgrade */
                $shortUpgrade = $this->factory->build(
                    self::UPGRADE_SHORT,
                    $availableSubscriptionType
                );
                $shortenedEndTime = $shortUpgrade->getNewEndTime($basePayment, $actualUserSubscription, $availableSubscriptionType->to_subscription_type);
                $diff = (new DateTime())->diff($shortenedEndTime);

                if ($diff->days >= 14) {
                    $availableUpgrades[] = $shortUpgrade;
                } else {
                    $availableUpgrades[] = $this->factory->build(
                        self::UPGRADE_PAID_EXTEND,
                        $availableSubscriptionType
                    );
                }
            }
        }

        $message = null;

        return new SubscriptionUpgrade($message, true, $basePayment, $actualUserSubscription, $availableUpgrades);
    }

    public function upgrade($service, $subscriptionTypeId, $gatewayId = null)
    {
        $subscriptionUpgrade = $this->canUpgrade($service);
        if (!$subscriptionUpgrade->canUpgrade()) {
            return false;
        }

        $upgrader = null;
        /** @var Upgrader $availableUpgrader */
        foreach ($subscriptionUpgrade->getAvailableUpgraders() as $availableUpgrader) {
            if ($availableUpgrader->canUpgradeSubscriptionType($subscriptionTypeId)) {
                if (!$this->subscriptionTypesRepository->find($subscriptionTypeId)) {
                    continue;
                }
                $upgrader = $availableUpgrader;
                break;
            }
        }
        if (!$upgrader) {
            return false;
        }

        return $upgrader->upgrade($subscriptionUpgrade, $gatewayId);
    }

    public function setTrackingParams($utmParams)
    {
        $this->factory->setTrackingParams($utmParams);
    }

    public function setSalesFunnelId($salesFunnelId)
    {
        $this->factory->setSalesFunnelId($salesFunnelId);
    }
}
