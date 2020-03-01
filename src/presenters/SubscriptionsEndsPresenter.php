<?php

namespace Crm\SubscriptionsModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\SubscriptionsModule\Components\SubscriptionEndsStatsFactoryInterface;
use Crm\SubscriptionsModule\Repository\ContentAccessRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Tomaj\Form\Renderer\BootstrapRenderer;

class SubscriptionsEndsPresenter extends AdminPresenter
{
    /** @var SubscriptionsRepository @inject */
    public $subscriptionsRepository;

    /** @var SubscriptionTypesRepository @inject */
    public $subscriptionTypesRepository;

    /** @var ContentAccessRepository @inject */
    public $contentAccessRepository;

    /** @persisted */
    public $startTime;

    /** @persisted */
    public $endTime;

    /** @persisted */
    public $withoutNext;

    /** @persisted */
    public $withoutRecurrent;

    /** @persisted */
    public $freeSubscriptions;

    /** @persisted */
    public $contentAccessTypes;

    public function startup()
    {
        parent::startup();

        $this->startTime = DateTime::from(strtotime('-1 week'));
        $this->endTime = new DateTime();
        $this->freeSubscriptions = false;
        if (isset($this->params['startTime'])) {
            $this->startTime = DateTime::from(strtotime($this->params['startTime']));
        }
        if (isset($this->params['endTime'])) {
            $this->endTime = DateTime::from(strtotime($this->params['endTime']));
        }
        $this->withoutNext = false;
        if (isset($this->params['withoutNext']) && $this->params['withoutNext']) {
            $this->withoutNext = true;
        }
        $this->withoutRecurrent = false;
        if (isset($this->params['withoutRecurrent']) && $this->params['withoutRecurrent']) {
            $this->withoutRecurrent = true;
        }
        if (isset($this->params['freeSubscriptions']) && $this->params['freeSubscriptions']) {
            $this->freeSubscriptions = true;
        }
        if (isset($this->params['contentAccessTypes'])) {
            $this->contentAccessTypes = $this->params['contentAccessTypes'];
        }
    }

    public function renderDefault()
    {
        $subscriptions = $this->subscriptionsRepository->subscriptionsEndBetween($this->startTime, $this->endTime, $this->withoutNext ? false : null);
        $subscriptions1 = $this->subscriptionsRepository->subscriptionsEndBetween($this->startTime, $this->endTime, false);

        if (!$this->freeSubscriptions) {
            $subscriptions
                ->where('subscription_type.price > ?', 0)
                ->where('subscriptions.type NOT IN (?)', ['free']);
        }
        if ($this->withoutRecurrent) {
            $subscriptions->where('subscriptions.id NOT', $subscriptions1->where([
                ':payments:recurrent_payments.status' => null,
                ':payments:recurrent_payments.retries > ?' => 0,
                ':payments:recurrent_payments.state = ?' => 'active'
            ])->fetchPairs(null, 'id'));
        }

        if ($this->contentAccessTypes) {
            $subscriptions->where('subscription_type:subscription_type_content_access.content_access.id IN (?)', $this->contentAccessTypes);
        }

        $data = $subscriptions->fetchAll();
        $this->template->subscriptions = $data;
    }

    protected function createComponentSubscriptionEndsStats(SubscriptionEndsStatsFactoryInterface $factory)
    {
        $control = $factory->create();
        $control->setStartTime($this->startTime);
        $control->setEndTime($this->endTime);
        $control->setWithoutNext($this->withoutNext);
        $control->setWithoutRecurrent($this->withoutRecurrent);
        $control->setFreeSubscriptions($this->freeSubscriptions);
        return $control;
    }

    public function createComponentAdminFilterForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->setRenderer(new BootstrapRenderer());
        $form->addText('start_time', 'subscriptions.data.subscriptions.fields.start_time')
            ->setAttribute('autofocus')
            ->setAttribute('class', 'flatpickr');
        $form->addText('end_time', 'subscriptions.data.subscriptions.fields.end_time')
            ->setAttribute('class', 'flatpickr');
        $form->addCheckbox('without_next', 'subscriptions.admin.subscriptions_ends.default.without_next');
        $form->addCheckbox('without_recurrent', 'subscriptions.admin.subscriptions_ends.default.without_recurrent');
        $form->addCheckbox('free_subscriptions', 'subscriptions.admin.subscriptions_ends.default.free_subscriptions');

        $form->addMultiSelect('content_access_types','subscriptions.admin.subscription_end_stats.content_access_types', $this->contentAccessRepository->all()->fetchPairs('id', 'name'))
            ->getControlPrototype()->addAttributes(['class' => 'select2']);

        $form->addSubmit('send', 'system.filter')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-filter"></i> ' . $this->translator->translate('system.filter'));

        $presenter = $this;
        $form->addSubmit('cancel', 'system.cancel_filter')->onClick[] = function () use ($presenter) {
            $presenter->redirect('default', ['text' => '']);
        };
        $form->onSuccess[] = [$this, 'adminFilterSubmited'];
        $form->setDefaults([
            'start_time' => $_GET['startTime'] ?? date('d.m.Y', strtotime('-1 week')),
            'end_time' => $_GET['endTime'] ?? date('d.m.Y'),
            'without_next' => $_GET['withoutNext'] ?? '',
            'without_recurrent' => $_GET['withoutRecurrent'] ?? '',
            'free_subscriptions' => $_GET['freeSubscriptions'] ?? '',
            'content_access_types' => $_GET['contentAccessTypes'] ?? [],
        ]);
        return $form;
    }

    public function adminFilterSubmited($form, $values)
    {
        $this->redirect('default', [
            'startTime' => $values['start_time'],
            'endTime' => $values['end_time'],
            'withoutNext' => $values['without_next'],
            'withoutRecurrent' => $values['without_recurrent'],
            'freeSubscriptions' => $values['free_subscriptions'],
            'contentAccessTypes' => $values['content_access_types'],
        ]);
    }
}
