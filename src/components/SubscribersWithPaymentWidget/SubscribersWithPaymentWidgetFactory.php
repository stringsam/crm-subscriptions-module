<?php

namespace Crm\SubscriptionsModule\Components;

use Crm\ApplicationModule\Widget\WidgetManager;
use Crm\SegmentModule\Repository\SegmentsValuesRepository;
use Kdyby\Translation\Translator;

class SubscribersWithPaymentWidgetFactory
{
    protected $widgetManager;
    protected $segmentsValuesRepository;
    protected $translator;
    protected $segmentCode;

    public function __construct(
        WidgetManager $widgetManager,
        SegmentsValuesRepository $segmentsValuesRepository,
        Translator $translator
    ) {
        $this->widgetManager = $widgetManager;
        $this->segmentsValuesRepository = $segmentsValuesRepository;
        $this->translator = $translator;
    }

    public function setSegment($code)
    {
        $this->segmentCode = $code;
        return $this;
    }

    public function create()
    {
        return (new SubscribersWithPaymentWidget(
            $this->widgetManager,
            $this->segmentsValuesRepository,
            $this->translator
        ))->setSegment($this->segmentCode);
    }
}
