<?php

namespace Crm\SubscriptionsModule\Api\v1;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApiModule\Api\IdempotentHandlerInterface;
use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Params\InputParam;
use Crm\ApiModule\Params\ParamsProcessor;
use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\SubscriptionsModule\Events\NewSubscriptionEvent;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Crm\UsersModule\Auth\UserManager;
use League\Event\Emitter;
use Nette\Database\Table\IRow;
use Nette\Http\Response;
use Nette\Utils\DateTime;

class CreateSubscriptionHandler extends ApiHandler implements IdempotentHandlerInterface
{
    private $subscriptionsRepository;

    private $subscriptionTypesRepository;

    private $userManager;

    private $emitter;

    private $hermesEmitter;

    public function __construct(
        SubscriptionTypesRepository $subscriptionTypesRepository,
        SubscriptionsRepository $subscriptionsRepository,
        UserManager $userManager,
        Emitter $emitter,
        \Tomaj\Hermes\Emitter $hermesEmitter
    ) {
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->userManager = $userManager;
        $this->emitter = $emitter;
        $this->hermesEmitter = $hermesEmitter;
    }

    public function params()
    {
        return [
            new InputParam(InputParam::TYPE_POST, 'email', InputParam::REQUIRED),
            new InputParam(InputParam::TYPE_POST, 'subscription_type_id', InputParam::REQUIRED),
            new InputParam(InputParam::TYPE_POST, 'start_time', InputParam::OPTIONAL),
            new InputParam(InputParam::TYPE_POST, 'type', InputParam::OPTIONAL),
        ];
    }

    public function handle(ApiAuthorizationInterface $authorization)
    {
        $paramsProcessor = new ParamsProcessor($this->params());
        $params = $paramsProcessor->getValues();

        $subscriptionType = $this->subscriptionTypesRepository->find($params['subscription_type_id']);
        if (!$subscriptionType) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Subscription type not found']);
            $response->setHttpCode(Response::S404_NOT_FOUND);
            return $response;
        }

        $user = $this->userManager->loadUserByEmail($params['email']);

        if (!empty($subscriptionType->limit_per_user) &&
            $this->subscriptionsRepository->getCount($subscriptionType->id, $user->id) >= $subscriptionType->limit_per_user) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'Limit per user reached']);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return $response;
        }

        $type = SubscriptionsRepository::TYPE_REGULAR;
        if (isset($params['type']) && in_array($params['type'], $this->subscriptionsRepository->availableTypes())) {
            $type = $params['type'];
        }

        $subscription = $this->subscriptionsRepository->add(
            $subscriptionType,
            false,
            $user,
            $type,
            DateTime::from(strtotime($params['start_time']))
        );
        $this->emitter->emit(new NewSubscriptionEvent($subscription));
        $this->hermesEmitter->emit(new HermesMessage('new-subscription', [
            'subscription_id' => $subscription->id,
        ]));

        return $this->createResponse($subscription);
    }

    public function idempotentHandle(ApiAuthorizationInterface $authorization)
    {
        $paramsProcessor = new ParamsProcessor($this->params());
        $params = $paramsProcessor->getValues();

        $user = $this->userManager->loadUserByEmail($params['email']);

        $where = [
            'user_id' => $user->id,
            'start_time' => DateTime::from(strtotime($params['start_time'])),
            'subscription_type_id' => $params['subscription_type_id'],
            'type' => isset($params['type']) && $params['type'] ? $params['type'] : SubscriptionsRepository::TYPE_REGULAR,
        ];
        $subscription = $this->subscriptionsRepository->getTable()->where($where)->limit(1)->fetch();

        return $this->createResponse($subscription);
    }

    private function createResponse(IRow $subscription)
    {
        $response = new JsonResponse([
            'status' => 'ok',
            'message' => 'Subscription created',
            'subscriptions' => [
                'id' => $subscription->id,
                'start_time' => $subscription->start_time->format('c'),
                'end_time' => $subscription->end_time->format('c'),
            ],
        ]);
        $response->setHttpCode(Response::S200_OK);
        return $response;
    }
}
