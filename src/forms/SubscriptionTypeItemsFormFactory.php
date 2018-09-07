<?php

namespace Crm\SubscriptionsModule\Forms;

use Crm\SubscriptionsModule\Repository\SubscriptionTypeItemsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;

class SubscriptionTypeItemsFormFactory
{
    private $subscriptionTypesRepository;

    private $subscriptionTypeItemsRepository;

    /** @var Translator  */
    private $translator;

    public $onSave;

    public function __construct(
        SubscriptionTypesRepository $subscriptionTypesRepository,
        SubscriptionTypeItemsRepository $subscriptionTypeItemsRepository,
        Translator $translator
    ) {
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
        $this->subscriptionTypeItemsRepository = $subscriptionTypeItemsRepository;
        $this->translator = $translator;
    }

    public function create($subscriptionTypeId): Form
    {
        $defaults = [];

        $form = new Form;

        $form->setRenderer(new BootstrapRenderer());
        $form->setTranslator($this->translator);
        $form->addProtection();

        $form->addGroup();

        $form->addHidden('subscription_type_id', $subscriptionTypeId);

        $form->addText('name', 'subscriptions.data.subscription_type_items.fields.name')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_type_items.placeholder.name')
            ->setRequired('subscriptions.data.subscription_type_items.required.name');

        $form->addText('amount', 'subscriptions.data.subscription_type_items.fields.amount')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_type_items.placeholder.amount')
            ->setRequired('subscriptions.data.subscription_type_items.required.amount')
            ->addRule(Form::FLOAT);

        $form->addInteger('vat', 'subscriptions.data.subscription_type_items.fields.vat')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_type_items.placeholder.vat')
            ->setRequired('subscriptions.data.subscription_type_items.required.vat');

        $form->addSubmit('send', 'system.save')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('system.save'));

//        if ($id) {
//            $form->addHidden('from_subscription_type_id', $id);
//        }

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $subscriptionType = $this->subscriptionTypesRepository->find($values['subscription_type_id']);
        $item = $this->subscriptionTypeItemsRepository->add($subscriptionType, $values['name'], $values['amount'], $values['vat']);
        $this->onSave->__invoke($item);
    }
}
