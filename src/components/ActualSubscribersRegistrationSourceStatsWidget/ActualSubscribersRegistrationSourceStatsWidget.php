<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Components\Graphs\GoogleBarGraphGroupControlFactoryInterface;
use Crm\ApplicationModule\Graphs\Criteria;
use Crm\ApplicationModule\Graphs\GraphData;
use Crm\ApplicationModule\Graphs\GraphDataItem;
use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Nette\Localization\ITranslator;
use Nette\Utils\DateTime;

class ActualSubscribersRegistrationSourceStatsWidget extends BaseWidget
{
    private $templateName = 'actual_subscribers_registration_source_stats_widget.latte';

    private $factory;

    private $graphData;

    private $translator;

    private $dateFrom;

    private $dateTo;

    public function __construct(
        WidgetManager $widgetManager,
        GoogleBarGraphGroupControlFactoryInterface $factory,
        GraphData $graphData,
        ITranslator $translator
    ) {
        parent::__construct($widgetManager);
        $this->factory = $factory;
        $this->graphData = $graphData;
        $this->translator = $translator;
    }

    public function render($params)
    {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . $this->templateName);
        $this->dateFrom = $params['dateFrom'];
        $this->dateTo = $params['dateTo'];
        $this->template->render();
    }

    public function createComponentGoogleUserSubscribersRegistrationSourceStatsGraph()
    {
        $graphDataItem = new GraphDataItem();
        $graphDataItem->setCriteria(

            (new Criteria())->setTableName('subscriptions')
                ->setJoin('JOIN users ON subscriptions.user_id = users.id')
                ->setWhere("AND subscriptions.internal_status = '" . SubscriptionsRepository::INTERNAL_STATUS_ACTIVE . "'")
                ->setTimeField('created_at')
                ->setGroupBy('users.source')
                ->setSeries('users.source')
                ->setValueField('count(*)')
                ->setStart(DateTime::from($this->dateFrom))
                ->setEnd(DateTime::from($this->dateTo))


//            (new Criteria())->setTableName('subscriptions')
//                ->setJoin('JOIN users ON subscriptions.user_id = users.id')
//                ->setWhere("AND subscriptions.internal_status = '" . SubscriptionsRepository::INTERNAL_STATUS_ACTIVE . "'")
//                ->setTimeField('created_at')
//                ->setGroupBy('users.source')
//                ->setSeries('users.source')
//                ->setValueField('count(*)')
//                ->setStart(DateTime::from($this->dateFrom))
//                ->setEnd(DateTime::from($this->dateTo))
//
//            (new Criteria())->setTableName('users')
//                ->setJoin('JOIN subscriptions ON subscriptions.user_id = users.id')
//                ->setWhere("AND subscriptions.internal_status = '" . SubscriptionsRepository::INTERNAL_STATUS_ACTIVE . "'")
//                ->setTimeField('created_at')
//                ->setGroupBy('users.source')
//                ->setSeries('users.source')
//                ->setValueField('count(*)')
//                ->setStart(DateTime::from($this->dateFrom))
//                ->setEnd(DateTime::from($this->dateTo))
        );

        $control = $this->factory->create();
        $control->setGraphTitle($this->translator->translate('dashboard.users.active_sub_registrations.title'))
            ->setGraphHelp($this->translator->translate('dashboard.users.active_sub_registrations.tooltip'))
            ->addGraphDataItem($graphDataItem);

        return $control;
    }
}

