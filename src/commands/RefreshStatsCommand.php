<?php

namespace Crm\SubscriptionsModule\Commands;

use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshStatsCommand extends Command
{
    private $subscriptionsRepository;

    public function __construct(SubscriptionsRepository $subscriptionsRepository)
    {
        parent::__construct();
        $this->subscriptionsRepository = $subscriptionsRepository;
    }

    protected function configure()
    {
        $this->setName('subscriptions:refresh_stats');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>***** REFRESHING SUBSCRIPTIONS STATS *****</info>');
        $output->writeln('');

        $this->subscriptionsRepository->totalCount(true, true);
        $this->subscriptionsRepository->currentSubscribersCount(true, true);

        $output->writeln('<info>Done</info>');
    }
}
