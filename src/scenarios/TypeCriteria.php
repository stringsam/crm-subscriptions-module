<?php

namespace Crm\SubscriptionsModule\Scenarios;

use Crm\ApplicationModule\Criteria\Params\StringLabeledArrayParam;
use Crm\ApplicationModule\Criteria\ScenariosCriteriaInterface;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Nette\Database\Table\Selection;

class TypeCriteria implements ScenariosCriteriaInterface
{
    private $subscriptionsRepository;

    public function __construct(
        SubscriptionsRepository $subscriptionsRepository
    ) {
        $this->subscriptionsRepository = $subscriptionsRepository;
    }

    public function params(): array
    {
        $types = $this->subscriptionsRepository->availableTypes();

        return [
            new StringLabeledArrayParam('type', 'Type', $types),
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
        return 'Type';
    }
}
