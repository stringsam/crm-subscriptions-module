<?php

namespace Crm\SubscriptionsModule\Scenarios;

use Crm\ApplicationModule\Criteria\Params\StringLabeledArrayParam;
use Crm\ApplicationModule\Criteria\ScenariosCriteriaInterface;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Nette\Database\Table\Selection;

class SubscriptionTypeCriteria implements ScenariosCriteriaInterface
{
    private $subscriptionTypesRepository;

    public function __construct(
        SubscriptionTypesRepository $subscriptionTypesRepository
    ) {
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
    }

    public function params(): array
    {
        $types = $this->subscriptionTypesRepository->all()->fetchPairs('code', 'name');

        return [
            new StringLabeledArrayParam('subscription_type', 'Subscription type', $types),
        ];
    }

    public function addCondition(Selection $selection): Selection
    {
        return $selection;
        // TODO
        //$where = [];
        //
        //$where[] = " payments.status IN ({$params->stringArray('code')->escapedString()}) ";
        //
        //return "SELECT DISTINCT(payments.id) AS id
        //  FROM payments
        //  WHERE " . implode(" AND ", $where);
    }

    public function label(): string
    {
        return 'Subscription type';
    }
}
