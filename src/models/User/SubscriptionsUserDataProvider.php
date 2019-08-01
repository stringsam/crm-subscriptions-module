<?php

namespace Crm\SubscriptionsModule\User;

use Crm\ApplicationModule\User\UserDataProviderInterface;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Nette\Localization\ITranslator;
use Nette\Utils\DateTime;

class SubscriptionsUserDataProvider implements UserDataProviderInterface
{
    private $subscriptionsRepository;

    private $translator;

    public function __construct(SubscriptionsRepository $subscriptionsRepository, ITranslator $translator)
    {
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->translator = $translator;
    }

    public static function identifier(): string
    {
        return 'subscriptions';
    }

    public function data($userId)
    {
        $subscriptions = $this->subscriptionsRepository->userSubscriptions($userId)->where(['end_time > ?' => new DateTime()]);

        $result = [];
        foreach ($subscriptions as $subscription) {
            $types = [];
            $subscriptionTypes = $subscription->subscription_type->related('subscription_type_content_access')->order('content_access.sorting');
            foreach ($subscriptionTypes as $contentAccess) {
                $types[] = $contentAccess->content_access->name;
            }
            $result[] = [
                'start_time' => $subscription->start_time->getTimestamp(),
                'end_time' => $subscription->end_time->getTimestamp(),
                'code' => $subscription->subscription_type->code,
                'is_recurrent' => (bool) $subscription->is_recurrent,
                'types' => $types,
            ];
        }

        return $result;
    }

    public function download($userId)
    {
        $subscriptions = $this->subscriptionsRepository->userSubscriptions($userId);

        $result = [];
        foreach ($subscriptions as $subscription) {
            $result[] = [
                'start_time' => $subscription->start_time->format(\DateTime::RFC3339),
                'end_time' => $subscription->end_time->format(\DateTime::RFC3339),
                'subscription_type' => $subscription->subscription_type->user_label,
                'type' => $subscription->type
            ];
        }

        return $result;
    }

    public function downloadAttachments($userId)
    {
        return [];
    }

    public function protect($userId): array
    {
        return [];
    }

    public function delete($userId, $protectedData = [])
    {
        return false;
    }

    public function canBeDeleted($userId): array
    {
        $threeMonthsAgo = DateTime::from(strtotime('-3 months'));
        if ($this->subscriptionsRepository->hasSubscriptionEndAfter($userId, $threeMonthsAgo)) {
            return [false, $this->translator->translate('subscriptions.data_provider.delete.three_months_active')];
        }

        return [true, null];
    }
}
