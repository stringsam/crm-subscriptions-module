<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Components\Graphs\GoogleBarGraphControlFactoryInterface;
use Crm\ApplicationModule\Graphs\GraphData;
use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Nette\Database\Context;
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

    private $database;

    public function __construct(
        WidgetManager $widgetManager,
        GoogleBarGraphControlFactoryInterface $factory,
        GraphData $graphData,
        ITranslator $translator,
        Context $database
    ) {
        parent::__construct($widgetManager);

        $this->factory = $factory;
        $this->graphData = $graphData;
        $this->translator = $translator;
        $this->database = $database;
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
        $control = $this->factory->create();

        $results = $this->database->table('subscriptions')
            ->where('subscriptions.internal_status', SubscriptionsRepository::INTERNAL_STATUS_ACTIVE)
            ->where('subscriptions.created_at > ?', DateTime::from($this->dateFrom))
            ->where('subscriptions.created_at < ?', DateTime::from($this->dateTo))
            ->group('user.source')
            ->select('user.source, count(*) AS count')
            ->fetchAll();

        $data = [];

        foreach ($results as $row) {
            $data[$row['source']] = $row['count'];
        }

        $control->addSerie($this->translator->translate('dashboard.users.active_sub_registrations.title'), $data);

        return $control;
    }
}

