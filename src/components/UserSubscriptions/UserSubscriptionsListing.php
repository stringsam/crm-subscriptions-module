<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\DataProvider\DataProviderManager;
use Crm\ApplicationModule\Widget\WidgetInterface;
use Crm\SubscriptionsModule\DataProvider\FilterGiftedSubscriptionsDataProviderInterface;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI;

class UserSubscriptionsListing extends UI\Control implements WidgetInterface
{
    private $templateName = 'user_subscriptions_listing.latte';

    /** @var DataProviderManager */
    private $dataProviderManager;

    /** @var SubscriptionsRepository */
    private $subscriptionsRepository;

    /** @var Translator  */
    private $translator;

    public function __construct(
        DataProviderManager $dataProviderManager,
        SubscriptionsRepository $subscriptionsRepository,
        Translator $translator
    ) {
        parent::__construct();
        $this->dataProviderManager = $dataProviderManager;
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->translator = $translator;
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

        // transforms Nette Selection to array & selects only IDs
        // iterator_to_array won't be needed when we start using proper objects for models
        $subscriptionIDs = array_column(iterator_to_array($subscriptions), 'id');

        /** @var FilterGiftedSubscriptionsDataProviderInterface[] $providers */
        $providers = $this->dataProviderManager->getProviders('subscriptions.dataprovider.filter_gifted_subscriptions', FilterGiftedSubscriptionsDataProviderInterface::class);
        foreach ($providers as $sorting => $provider) {
            // using += operator because array_merge would reindex array and remove keys (subscription IDs)
            $givenByEmail += $provider->provide($subscriptionIDs);
        }

        $this->template->totalSubscriptions = $this->totalCount($id);
        $this->template->subscriptions = $subscriptions;
        $this->template->givenByEmail = $givenByEmail;
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
