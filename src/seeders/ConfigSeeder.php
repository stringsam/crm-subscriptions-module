<?php


namespace Crm\SubscriptionsModule\Seeders;

use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\Repository\ConfigCategoriesRepository;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Crm\ApplicationModule\Seeders\ISeeder;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigSeeder implements ISeeder
{
    private $configCategoriesRepository;

    private $configsRepository;

    private $configBuilder;

    public function __construct(
        ConfigCategoriesRepository $configCategoriesRepository,
        ConfigsRepository $configsRepository,
        ConfigBuilder $configBuilder
    ) {
        $this->configCategoriesRepository = $configCategoriesRepository;
        $this->configsRepository = $configsRepository;
        $this->configBuilder = $configBuilder;
    }

    public function seed(OutputInterface $output)
    {
        $category = $this->configCategoriesRepository->loadByName('subscriptions.config.category');
        if (!$category) {
            $category = $this->configCategoriesRepository->add('subscriptions.config.category', 'fa fa-tag', 300);
            $output->writeln('  <comment>* config category <info>Subscriptions</info> created</comment>');
        } else {
            $output->writeln('  * config category <info>Subscriptions</info> exists');
        }

        $name = 'vat_default';
        $value = '20';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('subscriptions.config.vat_default.name')
                ->setDescription('subscriptions.config.vat_default.description')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(false)
                ->setConfigCategory($category)
                ->setSorting(120)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }
    }
}
