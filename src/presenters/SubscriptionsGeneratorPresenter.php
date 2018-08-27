<?php

namespace Crm\SubscriptionsModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\SubscriptionsModule\Forms\SubscriptionsGeneratorFormFactory;

class SubscriptionsGeneratorPresenter extends AdminPresenter
{
    /** @var SubscriptionsGeneratorFormFactory @inject */
    public $subscriptionsGeneratorFormFactory;

    public function renderDefault()
    {
    }

    public function createComponentSubscriptionsGeneratorForm()
    {
        $this->subscriptionsGeneratorFormFactory->onSubmit = function ($message) {
            $this->flashMessage($message, 'message');
        };
        $this->subscriptionsGeneratorFormFactory->onCreate = function ($message) {
            $this->flashMessage($message);
        };

        $form = $this->subscriptionsGeneratorFormFactory->create();

        return $form;
    }
}
