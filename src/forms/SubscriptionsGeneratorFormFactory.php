<?php

namespace Crm\SubscriptionsModule\Forms;

use Crm\ApplicationModule\DataProvider\DataProviderManager;
use Crm\PaymentsModule\Repository\PaymentsRepository;
use Crm\SubscriptionsModule\DataProvider\PaymentFromVariableSymbolDataProviderInterface;
use Crm\SubscriptionsModule\Generator\SubscriptionsGenerator;
use Crm\SubscriptionsModule\Generator\SubscriptionsParams;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Crm\UsersModule\Auth\UserManager;
use Crm\UsersModule\Repository\UsersRepository;
use DateInterval;
use Kdyby\Translation\Translator;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Tomaj\Form\Renderer\BootstrapRenderer;

class SubscriptionsGeneratorFormFactory
{
    /** @var DataProviderManager */
    public $dataProviderManager;

    /** @var UserManager  */
    private $userManager;

    /** @var PaymentsRepository  */
    private $paymentsRepository;

    /** @var UsersRepository  */
    private $usersRepository;

    /** @var SubscriptionTypesRepository  */
    private $subscriptionTypesRepository;

    /** @var LinkGenerator  */
    private $linkGenerator;

    /** @var Translator  */
    private $translator;

    public $onSubmit;

    public $onCreate;

    public function __construct(
        DataProviderManager $dataProviderManager,
        UserManager $userManager,
        PaymentsRepository $paymentsRepository,
        UsersRepository $usersRepository,
        SubscriptionsGenerator $subscriptionsGenerator,
        SubscriptionTypesRepository $subscriptionTypesRepository,
        LinkGenerator $linkGenerator,
        Translator $translator
    ) {
        $this->dataProviderManager = $dataProviderManager;
        $this->userManager = $userManager;
        $this->paymentsRepository = $paymentsRepository;
        $this->usersRepository = $usersRepository;
        $this->subscriptionsGenerator = $subscriptionsGenerator;
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
        $this->linkGenerator = $linkGenerator;
        $this->translator = $translator;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $defaults = [
            'type' => SubscriptionsRepository::TYPE_GIFT,
        ];

        $form = new Form;

        $form->setRenderer(new BootstrapRenderer());
        $form->setTranslator($this->translator);
        $form->addProtection();

        $form->addGroup('subscriptions.menu.subscriptions');

        $form->addSelect('subscription_type', 'subscriptions.menu.subscription_types', $this->subscriptionTypesRepository->all()->fetchPairs('id', 'name'));

        $form->addText('subscriptions_count', 'subscriptions.admin.subscription_generator.form.subscriptions_count')
            ->setType('number')
            ->setRequired('subscriptions.admin.subscription_generator.required.subscriptions_count')
            ->setAttribute('placeholder', 'subscriptions.admin.subscription_generator.placeholder.subscriptions_count')
            ->addRule(Form::INTEGER, 'subscriptions.admin.subscription_generator.errors.number');

        $form->addText('start_time', 'subscriptions.data.subscriptions.fields.start_time')
            ->setAttribute('placeholder', 'subscriptions.data.subscriptions.placeholder.start_time')
            ->setOption('description', 'subscriptions.admin.subscription_generator.description.start_time');

        $form->addText('end_time', 'subscriptions.data.subscriptions.fields.end_time')
            ->setAttribute('placeholder', 'subscriptions.data.subscriptions.placeholder.end_time')
            ->setOption('description', 'subscriptions.admin.subscription_generator.description.end_time');

        $form->addSelect('type', 'subscriptions.data.subscriptions.fields.type', [
            SubscriptionsRepository::TYPE_REGULAR  => SubscriptionsRepository::TYPE_REGULAR,
            SubscriptionsRepository::TYPE_FREE     => SubscriptionsRepository::TYPE_FREE,
            SubscriptionsRepository::TYPE_DONATION => SubscriptionsRepository::TYPE_DONATION,
            SubscriptionsRepository::TYPE_GIFT     => SubscriptionsRepository::TYPE_GIFT,
            SubscriptionsRepository::TYPE_SPECIAL  => SubscriptionsRepository::TYPE_SPECIAL,
        ])->setOption('description', 'subscriptions.admin.subscription_generator.description.type');

        $form->addGroup('Platba');

        $form->addText('variable_symbol', 'Variabilný symbol')
            ->setAttribute('placeholder', 'napríklad 1351234215');

        $form->addGroup('Informácie o používateľovi');

        $form->addText('user_email', 'Email')
            ->setRequired('Email používateľa musí byť vyplnený')
            ->setType('email')
            ->setAttribute('placeholder', 'napríklad jozko@pucik.sk')
            ->setOption('description', 'Ak zadaný email neexistuje tak bude konto vytvorené');

        $form->addText('first_name', 'Krstné meno:')
            ->setAttribute('placeholder', 'krstné meno');
        $form->addText('last_name', 'Priezvisko:')
            ->setAttribute('placeholder', 'priezvisko');
        $form->addText('institution_name', 'Názov inštitúcie:')
            ->setAttribute('placeholder', 'napríklad Knižnica Martina Bella');
        $form->addText('phone_number', 'Telefón')
            ->setAttribute('placeholder', 'napríklad 0915 123 456');
        $form->addText('print_first_name', 'Meno (pre doručenie):')
            ->setAttribute('placeholder', 'krstné meno pre doručenie printu (ak je rôzne)')
            ->setOption('description', 'Vyplniť iba ak je meno pre doručenie iné ako pôvodne');
        $form->addText('print_last_name', 'Priezvisko (pre doručenie):')
            ->setAttribute('placeholder', 'priezvisko pre doručenie printu (ak je rôzne)')
            ->setOption('description', 'Vyplniť iba ak je priezvisko pre doručenie iné ako pôvodne');
        $form->addText('address', 'Ulica:')
            ->setAttribute('placeholder', 'napište ulicu');
        $form->addText('zip', 'PSC:')
            ->setAttribute('placeholder', 'napríklad 08102');
        $form->addText('city', 'Mesto:')
            ->setAttribute('placeholder', 'napríklad Trnava');

        $form->addCheckbox('generate', 'subscriptions.admin.subscription_generator.form.generate')
            ->setOption('description', 'subscriptions.admin.subscription_generator.description.generate');

        $form->addSubmit('submit', 'subscriptions.admin.subscription_generator.form.send')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('subscriptions.admin.subscription_generator.form.send'));

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $subscriptionType = $this->subscriptionTypesRepository->find($values['subscription_type']);
        $startTime = DateTime::from(strtotime($values['start_time']));
        $endTime = DateTime::from(strtotime($values['end_time']));
        if (!$values['end_time']) {
            $endTime = clone $startTime;
            $endTime->add(new DateInterval("P{$subscriptionType->length}D"));
        }

        $user = $this->userManager->loadUserByEmail($values['user_email']);

        $payment = null;
        if ($values['variable_symbol']) {
            /** @var PaymentFromVariableSymbolDataProviderInterface[] $providers */
            $providers = $this->dataProviderManager->getProviders('subscriptions.dataprovider.payment_from_variable_symbol', PaymentFromVariableSymbolDataProviderInterface::class);
            foreach ($providers as $sorting => $provider) {
                $payment = $provider->provide(['variableSymbol' => $values['variable_symbol']]);
                if ($payment) {
                    break;
                }
            }

            if (is_null($payment)) {
                $form['variable_symbol']->addError($this->translator->translate('subscriptions.admin.subscription_generator.errors.unknown_variable_symbol', ['variable_symbol' => $values['variable_symbol']]));
                return;
            }
        }

        $count = intval($values['subscriptions_count']);

        if ($values['generate'] == 1) {
            if (!$user) {
                $user = $this->userManager->addNewUser($values['user_email'], false, 'subscriptiongenerator');
                if (!$user) {
                    $form['user_email']->addError('Cannot process email');
                    return;
                }
                $data = [
                    'first_name' => $values['first_name'],
                    'last_name' => $values['last_name'],
                    'phone_number' => $values['phone_number'],
                    'print_first_name' => $values['print_first_name'],
                    'print_last_name' => $values['print_last_name'],
                    'address' => $values['address'],
                    'zip' => $values['zip'],
                    'city' => $values['city'],
                ];
                if ($values['institution_name']) {
                    $data['institution_name'] = $values['institution_name'];
                    $data['is_institution'] = true;
                }
                $this->usersRepository->update($user, $data);
            }

            $this->subscriptionsGenerator->generate(new SubscriptionsParams($subscriptionType, $user, $values['type'], $startTime, $endTime, $payment), $count);

            $message = $this->translator->translate('subscriptions.admin.subscription_generator.messages.created', ['subscriptions_count' => $count, 'link' => $this->linkGenerator->link('Users:UsersAdmin:show', ['id' => $user->id]), 'email' => $user->email]);
            $this->onCreate->__invoke($message);
        } else {
            $message = $this->translator->translate('subscriptions.admin.subscription_generator.messages.creating', ['count' => $count, 'start_time' => $startTime, 'end_time' => $endTime]);
            if ($payment) {
                $message .= $this->translator->translate('subscriptions.admin.subscription_generator.messages.payment', ['email' => $payment->user->email, 'amount' => $payment->amount]);
            } else {
                $message .= $this->translator->translate('subscriptions.admin.subscription_generator.messages.no_payment');
            }

            if ($user) {
                $message .= $this->translator->translate('subscriptions.admin.subscription_generator.messages.user', ['email' => $user->email, 'id' => $user->id]);
            } else {
                $message .= $this->translator->translate('subscriptions.admin.subscription_generator.messages.new_user', ['email' => $values['user_email']]);
            }

            $this->onSubmit->__invoke($message);
        }
    }
}
