<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\UsersModule\Repository\AddressesRepository;
use Crm\UsersModule\Repository\UsersRepository;
use Nette\Database\Context;

class PrintSubscribersWithoutPrintAddressWidget extends BaseWidget
{
    private $templateName = 'print_subscribers_without_print_address_widget.latte';

    /** @var WidgetManager */
    protected $widgetManager;

    protected $usersRepository;

    protected $addressesRepository;

    public function __construct(
        WidgetManager $widgetManager,
        UsersRepository $usersRepository,
        AddressesRepository $addressesRepository
    ) {
        parent::__construct($widgetManager);

        $this->usersRepository = $usersRepository;
        $this->addressesRepository = $addressesRepository;
    }

    public function header($id = '')
    {
        return 'Nevyplnena adresa';
    }

    public function identifier()
    {
        return 'printsubscriberswithoutprintaddresswidget';
    }

    public function render()
    {
        $listUsers = $this->usersRepository->getTable()
            ->where(':subscriptions.start_time < ?', Context::literal('NOW()'))
            ->where(':subscriptions.end_time > ?', Context::literal('NOW()'))
            ->where(
                ':subscriptions.subscription_type:subscription_type_content_access.content_access.name = ?',
                'print'
            )->where('users.id NOT IN ?', $this->addressesRepository->all()->where('type = ?', 'print')->select('user_id'))
            ->fetchAll();

        if (!empty($listUsers)) {
            $this->template->listUsers = $listUsers;

            $this->template->setFile(__DIR__ . '/' . $this->templateName);
            $this->template->render();
        }
    }
}
