<?php

namespace Crm\SubscriptionsModule\Forms;

use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesUpgradesRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;

class SubscriptionTypesUpgradesFormFactory
{
    /** @var SubscriptionTypesRepository  */
    private $subscriptionTypesRepository;

    /** @var SubscriptionTypesUpgradesRepository  */
    private $subscriptionTypesUpgradesRepository;

    /** @var Translator  */
    private $translator;

    public $onSave;

    public function __construct(
        SubscriptionTypesRepository $subscriptionTypesRepository,
        SubscriptionTypesUpgradesRepository $subscriptionTypesUpgradesRepository,
        Translator $translator
    ) {
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
        $this->subscriptionTypesUpgradesRepository = $subscriptionTypesUpgradesRepository;
        $this->translator = $translator;
    }

    /**
     * @return Form
     */
    public function create($id)
    {
        $defaults = [];

        $form = new Form;

        $form->setRenderer(new BootstrapRenderer());
        $form->setTranslator($this->translator);
        $form->addProtection();

        $form->addGroup();

        $fromSubscription = $this->subscriptionTypesRepository->find($id);
        $unavailableIds = array_map(function ($row) {
            return $row->to_subscription_type_id;
        }, $this->subscriptionTypesUpgradesRepository->availableUpgrades($fromSubscription)->fetchAll());
        $unavailableIds[] = $id;

        $form->addSelect('to_subscription_type_id', 'subscriptions.data.subscription_types.fields.name', $this->subscriptionTypesRepository->all()->where(['id NOT IN (?)' => array_values($unavailableIds)])->fetchPairs('id', 'name'))
            ->setRequired('subscriptions.data.subscription_types.required.name');

        $form->addSubmit('send', 'system.save')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('system.save'));

        if ($id) {
            $form->addHidden('from_subscription_type_id', $id);
        }

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $fromSubscription = $this->subscriptionTypesRepository->find($values->from_subscription_type_id);
        $toSubscription = $this->subscriptionTypesRepository->find($values->to_subscription_type_id);

        $this->subscriptionTypesUpgradesRepository->add($fromSubscription, $toSubscription);
        $this->onSave->__invoke($fromSubscription, $toSubscription);
    }
}
