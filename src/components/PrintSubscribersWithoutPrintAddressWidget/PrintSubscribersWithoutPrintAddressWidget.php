<?php

namespace Crm\SubscriptionsModule\Components;

use Nette\Database\Context;
use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\UsersModule\Repository\AddressesRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;

class PrintSubscribersWithoutPrintAddressWidget extends BaseWidget
{
    private $templateName = 'print_subscribers_without_print_address_widget.latte';

    /** @var WidgetManager */
    protected $widgetManager;

    /** @var Context */
    protected $database;

    /** @var AddressesRepository */
    protected $addressesRepository;

    public function __construct(
        WidgetManager $widgetManager,
        Context $database,
        AddressesRepository $addressesRepository
    ) {
        parent::__construct($widgetManager);
        $this->database = $database;
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

    public function render($id = '')
    {
        $listUsers = [];

        $usrs = $this->database->table('subscriptions')
            ->where('subscriptions.internal_status', SubscriptionsRepository::INTERNAL_STATUS_ACTIVE)
            ->where('subscription_type.print', 1)
            ->select('user.id, user.email')
            ->fetchAll();


        foreach ($usrs as $usr) {
            $address = $this->addressesRepository->address($usr, 'print');
            if (!$address) {
                $listUsers[] = $usr;
            }
        }

        $this->template->listUsers = $listUsers;

        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
