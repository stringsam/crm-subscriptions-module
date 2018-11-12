<?php

namespace Crm\SubscriptionsModule\Components;

use Nette\Database\Context;
use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\UsersModule\Repository\UsersRepository;
use Crm\ApplicationModule\Widget\WidgetManager;

class PrintSubscribersWithoutPrintAddressWidget extends BaseWidget
{
    private $templateName = 'print_subscribers_without_print_address_widget.latte';

    /** @var WidgetManager */
    protected $widgetManager;

    /** @var UsersRepository */
    protected $usersRepository;

    public function __construct(
        WidgetManager $widgetManager,
        UsersRepository $usersRepository
    ) {
        parent::__construct($widgetManager);

        $this->usersRepository = $usersRepository;
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
            )->where('users.id NOT IN (SELECT user_id FROM addresses WHERE `type` = ?)', 'print')
            ->fetchAll();

        if (!empty($listUsers)) {
            $this->template->listUsers = $listUsers;

            $this->template->setFile(__DIR__ . '/' . $this->templateName);
            $this->template->render();
        }
    }
}
