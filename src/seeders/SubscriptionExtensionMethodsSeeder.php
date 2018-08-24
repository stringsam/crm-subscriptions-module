<?php

namespace Crm\SubscriptionsModule\Seeders;

use Crm\ApplicationModule\Seeders\ISeeder;
use Crm\SubscriptionsModule\Repository\SubscriptionExtensionMethodsRepository;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionExtensionMethodsSeeder implements ISeeder
{
    private $subscriptionExtensionMethodsRepository;

    public function __construct(SubscriptionExtensionMethodsRepository $subscriptionExtensionMethodsRepository)
    {
        $this->subscriptionExtensionMethodsRepository = $subscriptionExtensionMethodsRepository;
    }

    public function seed(OutputInterface $output)
    {
        $method = 'extend_actual';
        if (!$this->subscriptionExtensionMethodsRepository->exists($method)) {
            $this->subscriptionExtensionMethodsRepository->add(
                $method,
                'Extend actual',
                'Put new subscription after actual subscription or starts now',
                100
            );
            $output->writeln("  <comment>* subscription extension method <info>{$method}</info> created</comment>");
        } else {
            $output->writeln("  * subscription extension method <info>{$method}</info> exists");
        }

        $method = 'start_now';
        if (!$this->subscriptionExtensionMethodsRepository->exists($method)) {
            $this->subscriptionExtensionMethodsRepository->add(
                $method,
                'Start now',
                'Begins immediately regardless actual subscription',
                200
            );
            $output->writeln("  <comment>* subscription extension method <info>{$method}</info> created</comment>");
        } else {
            $output->writeln("  * subscription extension method <info>{$method}</info> exists");
        }
    }
}
