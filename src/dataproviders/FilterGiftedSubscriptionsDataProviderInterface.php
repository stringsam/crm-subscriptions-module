<?php

namespace Crm\SubscriptionsModule\DataProvider;

use Crm\ApplicationModule\DataProvider\DataProviderInterface;

interface FilterGiftedSubscriptionsDataProviderInterface extends DataProviderInterface
{
    /**
     * Filter provided list of subscription IDs and return only gifted subscriptions.
     * Return array will be in format
     *
     *      [
     *          'subscription_id' => 'given_by_email',
     *          ...
     *      ]
     *
     * @param array $subscriptionIDs[int]
     *
     * @return array
     */
    public function provide(array $subscriptionIDs): array;
}
