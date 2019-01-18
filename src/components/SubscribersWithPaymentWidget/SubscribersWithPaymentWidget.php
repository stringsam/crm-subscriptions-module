<?php

namespace Crm\SubscriptionsModule\Components;

use DateTime;
use Crm\SegmentModule\SegmentFactory;
use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\SegmentModule\Repository\SegmentsRepository;
use Crm\SegmentModule\Repository\SegmentsValuesRepository;
use Kdyby\Translation\Translator;

class SubscribersWithPaymentWidget extends BaseWidget
{
    private $templateName = 'subscribers_with_payment_widget.latte';

    /**
     * @var SegmentsRepository
     */
    private $segmentsRepository;

    /**
     * @var SegmentFactory
     */
    private $segmentFactory;

    /**
     * @var SegmentsValuesRepository
     */
    private $segmentsValuesRepository;

    /**
     * @var string
     */
    private $segmentCode;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $identifier;


    public function __construct(
        WidgetManager $widgetManager,
        SegmentsValuesRepository $segmentsValuesRepository,
        Translator $translator
    ) {
        parent::__construct($widgetManager);

        $this->translator = $translator;
        $this->segmentsValuesRepository = $segmentsValuesRepository;
        $this->identifier = 'subscriberswithpaymentwidget' . uniqid();
    }

    public function header()
    {
        return 'Subscription';
    }

    public function identifier()
    {
        return $this->identifier;
    }

    public function setSegment($code)
    {
        $this->segmentCode = $code;
        return $this;
    }

    public function setDateModifier($str)
    {
        $this->date = (new DateTime)->modify($str);
        return $this;
    }

    public function render()
    {
        $date = new DateTime;
        $now = $this->segmentsValuesRepository->segment($this->segmentCode)
            ->order('date DESC')
            ->limit(1)
            ->select('*')
            ->fetch();

        $then = $this->segmentsValuesRepository->segment($this->segmentCode)
            ->where('date <= ?', $this->date)
            ->order('date DESC')
            ->limit(1)
            ->select('*')
            ->fetch();

        $title = $this->translator->translate('subscriptions.admin.subscribers_with_payment_widget.last_day');
        $days = $date->diff($this->date)->d;
        if ($days > 1) {
            $title = $this->translator->translate(
                'subscriptions.admin.subscribers_with_payment_widget.last_days',
                null,
                ['count' => $days]
            );
        }

        $this->template->date = $this->date;
        $this->template->now = $now ? $now->value : 0;
        $this->template->then = $then ? $then->value: 0;
        $this->template->title = $title;

        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
