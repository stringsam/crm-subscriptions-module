<?php

namespace Crm\SubscriptionsModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\SubscriptionsModule\Forms\SubscriptionTypeItemsFormFactory;
use Crm\SubscriptionsModule\Repository\SubscriptionTypeItemsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;

class SubscriptionTypeItemsAdminPresenter extends AdminPresenter
{
    private $subscriptionTypeItemsFormFactory;

    private $subscriptionTypesRepository;
    /**
     * @var SubscriptionTypeItemsRepository
     */
    private $subscriptionTypeItemsRepository;

    public function __construct(
        SubscriptionTypeItemsFormFactory $subscriptionTypeItemsFormFactory,
        SubscriptionTypeItemsRepository $subscriptionTypeItemsRepository,
        SubscriptionTypesRepository $subscriptionTypesRepository
    ) {
        parent::__construct();

        $this->subscriptionTypeItemsFormFactory = $subscriptionTypeItemsFormFactory;
        $this->subscriptionTypeItemsRepository = $subscriptionTypeItemsRepository;
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
    }

    public function renderNew($subscriptionTypeId)
    {
        $subscriptionType = $this->subscriptionTypesRepository->find($subscriptionTypeId);

        $this->template->type = $subscriptionType;
        $this->template->subscriptionTypeId = $subscriptionTypeId;
    }

    public function renderEdit($subscriptionTypeItemId)
    {
        $item = $this->subscriptionTypeItemsRepository->find($subscriptionTypeItemId);

        $subscriptionType = $this->subscriptionTypesRepository->find($item->subscription_type_id);

        $this->template->type = $subscriptionType;
        $this->template->item = $item;
        $this->template->subscriptionTypeItemId = $subscriptionTypeItemId;
    }


    protected function createComponentSubscriptionTypeItemsForm()
    {
        $form = $this->subscriptionTypeItemsFormFactory->create($this->params['id']);
        $this->subscriptionTypeItemsFormFactory->onSave = function ($subscriptionTypeItem) {
            $this->flashMessage($this->translator->translate('subscriptions.admin.subscription_types.messages.subscription_type_item_created'));
            $this->redirect('SubscriptionTypesAdmin:Show', $subscriptionTypeItem->subscription_type_id);
        };
        return $form;
    }
}
