<?php

namespace Crm\SubscriptionsModule\Components;

use Nette\Database\Context;
use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\UsersModule\Repository\AddressesRepository;

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

    public function render()
    {
        $listUsers = $this->database->table('subscriptions')
            ->where('subscriptions.start_time < ?', $this->database::literal('NOW()'))
            ->where('subscriptions.end_time > ?', $this->database::literal('NOW()'))
            ->where('subscription_type.print', 1)
            ->where('user.id NOT IN (SELECT user_id FROM addresses WHERE `type` = ?)', 'print')
            ->select('user.id, user.email')
            ->fetchAll();

        if (!empty($listUsers)) {
            $this->template->listUsers = $listUsers;

            $this->template->setFile(__DIR__ . '/' . $this->templateName);
            $this->template->render();
        }
    }
}
