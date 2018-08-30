<?php

namespace Crm\SubscriptionsModule\Presenters;

use Crm\ApplicationModule\Components\Graphs\GoogleLineGraphGroupControlFactoryInterface;
use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\GraphDataItem;
use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\SubscriptionsModule\Forms\SubscriptionTypesFormFactory;
use Crm\SubscriptionsModule\Forms\SubscriptionTypesUpgradesFormFactory;
use Crm\SubscriptionsModule\Report\NoRecurrentChargeReport;
use Crm\SubscriptionsModule\Report\PaidNextSubscriptionReport;
use Crm\SubscriptionsModule\Report\RecurrentWithoutProfileReport;
use Crm\SubscriptionsModule\Report\ReportGroup;
use Crm\SubscriptionsModule\Report\ReportTable;
use Crm\SubscriptionsModule\Report\StoppedOnFirstSubscriptionReport;
use Crm\SubscriptionsModule\Report\TotalRecurrentSubscriptionsReport;
use Crm\SubscriptionsModule\Report\TotalSubscriptionsReport;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesUpgradesRepository;

class SubscriptionTypesAdminPresenter extends AdminPresenter
{
    /** @var SubscriptionTypesRepository @inject */
    public $subscriptionTypesRepository;

    /** @var SubscriptionTypesUpgradesRepository @inject */
    public $subscriptionTypesUpgradesRepository;

    /** @var SubscriptionTypesFormFactory @inject */
    public $subscriptionTypeFactory;

    /** @var SubscriptionTypesUpgradesFormFactory @inject */
    public $subscriptionTypesUpgradesFormFactory;

    public function renderDefault()
    {
        $subscriptionTypes = $this->filteredSubscriptionTypes();
        $activeSubscriptionTypes = [];
        $inactiveSubscriptionTypes = [];
        foreach ($subscriptionTypes as $subscriptionType) {
            if ($subscriptionType->visible) {
                $activeSubscriptionTypes[] = $subscriptionType;
            } else {
                $inactiveSubscriptionTypes[] = $subscriptionType;
            }
        }

        $this->template->activeSubscriptionTypes = $activeSubscriptionTypes;
        $this->template->inactiveSubscriptionTypes = $inactiveSubscriptionTypes;

        $this->template->totalSubscriptionTypes = $this->subscriptionTypesRepository->totalCount();
    }

    private function filteredSubscriptionTypes()
    {
        return $this->subscriptionTypesRepository->all($this->text)->order('sorting ASC');
    }

    public function renderShow($id)
    {
        $subscriptionType = $this->subscriptionTypesRepository->find($id);
        if (!$subscriptionType) {
            $this->flashMessage($this->translator->translate('subscriptions.admin.subscription_types.messages.subscription_type_not_found'));
            $this->redirect('default');
        }
        $this->template->type = $subscriptionType;
        $this->template->availableUpgrades = $this->subscriptionTypesUpgradesRepository->availableUpgrades($subscriptionType);

        $reportTable = new ReportTable(
            ['subscription_type_id' => $id],
            $this->context->getByType('Nette\Database\Context'),
            new ReportGroup('users.source')
        );
        $reportTable
            ->addReport(new TotalSubscriptionsReport(''))
            ->addReport(new TotalRecurrentSubscriptionsReport(''))
            ->addReport(new NoRecurrentChargeReport(''), ['Crm\SubscriptionsModule\Report\TotalRecurrentSubscriptionsReport'])
            ->addReport(new StoppedOnFirstSubscriptionReport(''), ['Crm\SubscriptionsModule\Report\TotalRecurrentSubscriptionsReport'])
            ->addReport(new PaidNextSubscriptionReport('', 1), ['Crm\SubscriptionsModule\Report\TotalRecurrentSubscriptionsReport'])
            ->addReport(new PaidNextSubscriptionReport('', 2))
            ->addReport(new PaidNextSubscriptionReport('', 3))
            ->addReport(new RecurrentWithoutProfileReport(''), ['Crm\SubscriptionsModule\Report\TotalRecurrentSubscriptionsReport'])
        ;
        $this->template->reportTables = [
            'Zdroj pouzivatelov' => $reportTable->getData(),
        ];
    }

    protected function createComponentSubscriptionTypesUpgradesForm()
    {
        $id = null;
        if (isset($this->params['id'])) {
            $id = $this->params['id'];
        }

        $form = $this->subscriptionTypesUpgradesFormFactory->create($id);
        $this->subscriptionTypesUpgradesFormFactory->onSave = function ($subscriptionType) {
            $this->flashMessage($this->translator->translate('subscriptions.admin.subscription_types.messages.subscription_types_upgrade_created'));
            $this->redirect('SubscriptionTypesAdmin:Show', $subscriptionType->id);
        };
        return $form;
    }

    public function handleRemoveUpgrade($fromSubscriptionTypeId, $toSubscriptionTypeId)
    {
        $fromSubscriptionType = $this->subscriptionTypesRepository->find($fromSubscriptionTypeId);
        $toSubscriptionType = $this->subscriptionTypesRepository->find($toSubscriptionTypeId);
        $this->subscriptionTypesUpgradesRepository->remove($fromSubscriptionType, $toSubscriptionType);
        $this->flashMessage($this->translator->translate('subscriptions.admin.subscription_types.messages.subscription_types_upgrade_deleted'));
        $this->redirect('show', $fromSubscriptionTypeId);
    }

    public function renderEdit($id)
    {
        $subscriptionType = $this->subscriptionTypesRepository->find($id);
        if (!$subscriptionType) {
            $this->flashMessage($this->translator->translate('subscriptions.admin.subscription_types.messages.subscription_type_not_found'));
            $this->redirect('default');
        }
        $this->template->type = $subscriptionType;
    }

    protected function createComponentSubscriptionTypeForm()
    {
        $id = null;
        if (isset($this->params['id'])) {
            $id = $this->params['id'];
        }

        $form = $this->subscriptionTypeFactory->create($id);

        $this->subscriptionTypeFactory->onSave = function ($subscriptionType) {
            $this->flashMessage($this->translator->translate('subscriptions.admin.subscription_types.messages.subscription_type_created'));
            $this->redirect('SubscriptionTypesAdmin:Show', $subscriptionType->id);
        };
        $this->subscriptionTypeFactory->onUpdate = function ($subscriptionType) {
            $this->flashMessage($this->translator->translate('subscriptions.admin.subscription_types.messages.subscription_type_updated'));
            $this->redirect('SubscriptionTypesAdmin:Show', $subscriptionType->id);
        };
        return $form;
    }

    protected function createComponentSubscriptionsGraph(GoogleLineGraphGroupControlFactoryInterface $factory)
    {
        $graphDataItem1 = new GraphDataItem();
        $graphDataItem1->setCriteria((new Criteria())
            ->setTableName('subscriptions')
            ->setTimeField('created_at')
            ->setWhere('AND subscription_type_id=' . intval($this->params['id']))
            ->setValueField('COUNT(*)')
            ->setStart('-1 month'))
            ->setName('Created subscriptions');

        $control = $factory->create()
            ->setGraphTitle('New subscriptions')
            ->setGraphHelp('New subscriptions created in time')
            ->addGraphDataItem($graphDataItem1);

        return $control;
    }

    public function renderExport()
    {
        $this->getHttpResponse()->addHeader('Content-Type', 'application/csv');
        $this->getHttpResponse()->addHeader('Content-Disposition', 'attachment; filename=export.csv');

        $this->template->types = $this->subscriptionTypesRepository->all();
    }
}
