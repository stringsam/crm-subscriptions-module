<?php

namespace Crm\SubscriptionsModule\Commands;

use Crm\SubscriptionsModule\Events\SubscriptionEndsEvent;
use Crm\SubscriptionsModule\Events\SubscriptionStartsEvent;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use League\Event\Emitter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeSubscriptionsStateCommand extends Command
{
    /** @var SubscriptionsRepository  */
    private $subscriptionsRepository;

    /** @var Emitter */
    private $emitter;

    public function __construct(SubscriptionsRepository $subscriptionsRepository, Emitter $emitter)
    {
        parent::__construct();
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->emitter = $emitter;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('subscriptions:change_status')
            ->setDescription('Change subscriptions status')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>***** CHANGE SUBSCRIPTIONS STATUS *****</info>');
        $output->writeln('');

        $expiredSubscriptions = $this->subscriptionsRepository->getExpiredSubscriptions();
        foreach ($expiredSubscriptions as $subscription) {
            $output->writeln('Expired subscription #' . $subscription->id . ' ' . json_encode($subscription->toArray()));
            $this->subscriptionsRepository->update($subscription, ['internal_status' => SubscriptionsRepository::INTERNAL_STATUS_AFTER_END]);
            $this->emitter->emit(new SubscriptionEndsEvent($subscription));
        }

        $output->writeln('');

        $startedSubscriptions = $this->subscriptionsRepository->getStartedSubscriptions();
        foreach ($startedSubscriptions as $subscription) {
            $output->writeln('Started subscription #' . $subscription->id . ' ' . json_encode($subscription->toArray()));
            $this->subscriptionsRepository->update($subscription, ['internal_status' => SubscriptionsRepository::INTERNAL_STATUS_ACTIVE]);
            $this->emitter->emit(new SubscriptionStartsEvent($subscription));
        }
    }
}
