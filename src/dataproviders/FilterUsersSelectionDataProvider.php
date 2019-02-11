<?php

namespace Crm\SubscriptionsModule\DataProvider;

use Crm\ApplicationModule\DataProvider\DataProviderException;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Crm\UsersModule\DataProvider\FilterUsersSelectionDataProviderInterface;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

class FilterUsersSelectionDataProvider implements FilterUsersSelectionDataProviderInterface
{
    private $subscriptionTypesRepository;

    public function __construct(SubscriptionTypesRepository $subscriptionTypesRepository)
    {
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
    }

    public function provide(array $params): Selection
    {
        if (!isset($params['selection'])) {
            throw new DataProviderException('selection param missing');
        }
        if (!isset($params['params'])) {
            throw new DataProviderException('params param missing');
        }

        $params['selection']
            ->select(':subscriptions.start_time, :subscriptions.end_time');

        if (!($params['only_count'] ?? false)) {
            $params['selection']->joinWhere(':subscriptions', 'end_time > NOW()');
        }

        if (isset($params['params']['actual_subscription']) && $params['params']['actual_subscription'] == '1') {
            $params['selection']
                ->where(':subscriptions.start_time < ?', new DateTime)
                ->where(':subscriptions.end_time > ?', new DateTime);
        }
        if (isset($params['params']['subscription_type'])) {
            $params['selection']
                ->where(':subscriptions.subscription_type_id = ?', intval($params['params']['subscription_type']));
        }

        return $params['selection'];
    }
}
