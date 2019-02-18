<?php

namespace Crm\SubscriptionsModule\DataProvider;

use Crm\ApplicationModule\DataProvider\DataProviderException;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Crm\SubscriptionsModule\Subscription\SubscriptionType;
use Crm\UsersModule\DataProvider\FilterUsersFormDataProviderInterface;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

class FilterUsersFormDataProvider implements FilterUsersFormDataProviderInterface
{
    private $subscriptionTypesRepository;

    private $translator;

    public function __construct(SubscriptionTypesRepository $subscriptionTypesRepository, ITranslator $translator)
    {
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
        $this->translator = $translator;
    }

    public function provide(array $params): Form
    {
        if (!isset($params['form'])) {
            throw new DataProviderException('form param missing');
        }

        $subscriptionTypePairs = SubscriptionType::getPairs($this->subscriptionTypesRepository->all());
        $subscriptionType = $params['form']->addSelect('subscription_type', '', $subscriptionTypePairs)
            ->setPrompt($this->translator->translate('subscriptions.admin.filter_users.subscription_type'))->setAttribute('style', 'max-width:500px');
        $subscriptionType->getControlPrototype()->addAttributes(['class' => 'select2']);

        $params['form']->addCheckbox('actual_subscription', $this->translator->translate('subscriptions.admin.filter_users.actual_subscription'));
        return $params['form'];
    }
}
