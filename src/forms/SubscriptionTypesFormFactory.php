<?php

namespace Crm\SubscriptionsModule\Forms;

use Crm\IssuesModule\Repository\MagazinesRepository;
use Crm\SubscriptionsModule\Builder\SubscriptionTypeBuilder;
use Crm\SubscriptionsModule\Repository\ContentAccessRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionExtensionMethodsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionLengthMethodsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;
use Tomaj\Form\Renderer\BootstrapRenderer;

class SubscriptionTypesFormFactory
{
    private $subscriptionTypesRepository;

    private $subscriptionTypeBuilder;

    private $translator;

    private $magazinesRepository;

    private $subscriptionExtensionMethodsRepository;

    private $subscriptionLengthMethodsRepository;

    private $contentAccessRepository;

    public $onSave;

    public $onUpdate;

    public function __construct(
        SubscriptionTypesRepository $subscriptionTypesRepository,
        SubscriptionTypeBuilder $subscriptionTypeBuilder,
        SubscriptionExtensionMethodsRepository $subscriptionExtensionMethodsRepository,
        SubscriptionLengthMethodsRepository $subscriptionLengthMethodsRepository,
        MagazinesRepository $magazinesRepository,
        ContentAccessRepository $contentAccess,
        Translator $translator
    ) {
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
        $this->subscriptionTypeBuilder = $subscriptionTypeBuilder;
        $this->subscriptionExtensionMethodsRepository = $subscriptionExtensionMethodsRepository;
        $this->subscriptionLengthMethodsRepository = $subscriptionLengthMethodsRepository;
        $this->magazinesRepository = $magazinesRepository;
        $this->contentAccessRepository = $contentAccess;
        $this->translator = $translator;
    }

    /**
     * @return Form
     */
    public function create($subscriptionTypeId)
    {
        $defaults = [];
        if (isset($subscriptionTypeId)) {
            $subscriptionType = $this->subscriptionTypesRepository->find($subscriptionTypeId);
            $defaults = $subscriptionType->toArray();

            foreach ($subscriptionType->related('subscription_type_magazines') as $pair) {
                $defaults['magazine_' . $pair->magazine_id] = true;
            }
        }

        $form = new Form;

        $form->setRenderer(new BootstrapRenderer());
        $form->setTranslator($this->translator);
        $form->addProtection();

        $form->addGroup();

        $form->addText('name', 'subscriptions.data.subscription_types.fields.name')
            ->setRequired('subscriptions.data.subscription_types.required.name')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_types.placeholder.name');

        $form->addText('code', 'subscriptions.data.subscription_types.fields.code')
            ->setRequired()
            ->setAttribute('placeholder', 'subscriptions.data.subscription_types.placeholder.code');

        $form->addSelect('extension_method_id', 'subscriptions.data.subscription_types.fields.extension_method_id', $this->subscriptionExtensionMethodsRepository->all()->fetchPairs('method', 'title'))
            ->setRequired();

        $form->addSelect('length_method_id', 'subscriptions.data.subscription_types.fields.length_method_id', $this->subscriptionLengthMethodsRepository->all()->fetchPairs('method', 'title'))
            ->setRequired();

        $form->addText('user_label', 'subscriptions.data.subscription_types.fields.user_label')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_types.placeholder.user_label');

        $form->addText('length', 'subscriptions.data.subscription_types.fields.length')
            ->setRequired('subscriptions.data.subscription_types.required.length')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_types.placeholder.length');

        $form->addText('extending_length', 'subscriptions.data.subscription_types.fields.extending_length')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_types.placeholder.extending_length');

        $form->addText('fixed_end', 'subscriptions.data.subscription_types.fields.fixed_end')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_types.placeholder.fixed_end');

        $form->addSelect('type', 'subscriptions.data.subscription_types.fields.type', $this->subscriptionTypesRepository->availableTypes())
            ->setRequired('subscriptions.data.subscription_types.required.type');

        $form->addText('recurrent_charge_before', 'subscriptions.data.subscription_types.fields.recurrent_charge_before')
            ->setType('number');

        $form->addText('limit_per_user', 'subscriptions.data.subscription_types.fields.limit_per_user')
            ->addCondition(Form::FILLED)
            ->addRule(Form::INTEGER, 'subscriptions.data.subscription_types.validation.integer.limit_per_user')
            ->addRule(Form::MIN, 'subscriptions.data.subscription_types.validation.minimum.limit_per_user', 1);

        $contentAccesses = $this->contentAccessRepository->all();
        foreach ($contentAccesses as $contentAccess) {
            $form->addCheckbox($contentAccess->name, $contentAccess->name . ($contentAccess->description ? " ({$contentAccess->description})" : ''));
        }

        $form->addText('price', 'subscriptions.data.subscription_types.fields.price')
            ->setRequired('subscriptions.data.subscription_types.required.price')
            ->setAttribute('placeholder', 'subscriptions.data.subscription_types.placeholder.price');

        $form->addCheckbox('ask_address', 'subscriptions.data.subscription_types.fields.ask_address');

        $form->addSelect('next_subscription_type_id', 'subscriptions.data.subscription_types.fields.next_subscription_type_id', $this->subscriptionTypesRepository->all()->fetchPairs('id', 'name'))
            ->setPrompt('--');

        $form->addTextArea('description', 'subscriptions.data.subscription_types.fields.description');

        $form->addCheckbox('active', 'subscriptions.data.subscription_types.fields.active');
        $form->addCheckbox('visible', 'subscriptions.data.subscription_types.fields.visible');
        $form->addCheckbox('disable_notifications', 'subscriptions.data.subscription_types.fields.disable_notifications');

        $form->addtext('sorting', 'subscriptions.data.subscription_types.fields.sorting');

        $form->addGroup('subscriptions.data.subscription_types.fields.magazines');

        $magazines = $this->magazinesRepository->all();
        foreach ($magazines as $magazine) {
            $form->addCheckbox('magazine_'.$magazine->id, $magazine->name);
        }

        $form->addSubmit('send', 'system.save')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('system.save'));

        if ($subscriptionTypeId) {
            $form->addHidden('subscription_type_id', $subscriptionTypeId);
        }

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $magazines = [];
        foreach ($values as $key => $value) {
            if (substr($key, 0, 9) == 'magazine_') {
                if ($value) {
                    $id = intval(str_replace('magazine_', '', $key));
                    $magazines[] = $id;
                }
                unset($values[$key]);
            }
        }

        if ($values['limit_per_user'] == '') {
            $values['limit_per_user'] = null;
        }

        if ($values['fixed_end'] == '') {
            $values['fixed_end'] = null;
        } else {
            $values['fixed_end'] = DateTime::from(strtotime($values['fixed_end']));
        }

        if ($values['recurrent_charge_before'] == '') {
            $values['recurrent_charge_before'] = null;
        }

        if (isset($values['subscription_type_id'])) {
            $subscriptionTypeId = $values['subscription_type_id'];
            unset($values['subscription_type_id']);

            $subscriptionType = $this->subscriptionTypesRepository->find($subscriptionTypeId);
            $this->subscriptionTypesRepository->update($subscriptionType, $values);
            $this->subscriptionTypesRepository->setMagazines($subscriptionType, $magazines);
            $this->processContentTypes($subscriptionType, $values);
            $this->onUpdate->__invoke($subscriptionType);
        } else {
            $subscriptionType = $this->subscriptionTypeBuilder->createNew()
                ->setName($values['name'])
                ->setPrice($values['price'])
                ->setLength($values['length'])
                ->setExtensionMethod($values['extension_method_id'])
                ->setLengthMethod($values['length_method_id'])
                ->setExtendingLength($values['extending_length'])
                ->setFixedEnd($values['fixed_end'])
                ->setType($values['type'])
                ->setLimitPerUser($values['limit_per_user'])
                ->setUserLabel($values['user_label'])
                ->setSorting($values['sorting'])
                ->setActive($values['active'])
                ->setVisible($values['visible'])
                ->setDescription($values['description'])
                ->setCode($values['code'])
                ->setAskAddress($values['ask_address'])
                ->setDisabledNotifications($values['disable_notifications'])
                ->setRecurrentChargeBefore($values['recurrent_charge_before']);

            $contentAccesses = $this->contentAccessRepository->all();
            $contentAccessValues = [];
            foreach ($contentAccesses as $contentAccess) {
                $contentAccessValues[$contentAccess->name] = $values[$contentAccess->name];
            }
            $subscriptionType->setContentAccess($contentAccessValues);

            $subscriptionType = $subscriptionType->save();

            if (!$subscriptionType) {
                $form['name']->addError(implode("\n", $this->subscriptionTypeBuilder->getErrors()));
            } else {
                $this->subscriptionTypesRepository->setMagazines($subscriptionType, $magazines);
                $this->processContentTypes($subscriptionType, $values);
                $this->onSave->__invoke($subscriptionType);
            }
        }
    }

    private function processContentTypes(IRow $subscriptionType, $values)
    {
        $access = $this->contentAccessRepository->all()->fetchPairs('name', 'name');

        foreach ($access as $key) {
            if ($values->{$key}) {
                if (!$this->contentAccessRepository->hasAccess($subscriptionType, $key)) {
                    $this->contentAccessRepository->addAccess($subscriptionType, $key);
                }
            } else {
                $this->contentAccessRepository->removeAccess($subscriptionType, $key);
            }
        }
    }
}
