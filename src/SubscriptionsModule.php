<?php

namespace Crm\SubscriptionsModule;

use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoute;
use Crm\ApplicationModule\Access\AccessManager;
use Crm\ApplicationModule\Commands\CommandsContainerInterface;
use Crm\ApplicationModule\Criteria\CriteriaStorage;
use Crm\ApplicationModule\CrmModule;
use Crm\ApplicationModule\DataProvider\DataProviderManager;
use Crm\ApplicationModule\Menu\MenuContainerInterface;
use Crm\ApplicationModule\Menu\MenuItem;
use Crm\ApplicationModule\SeederManager;
use Crm\ApplicationModule\User\UserDataRegistrator;
use Crm\ApplicationModule\Widget\WidgetManagerInterface;
use Crm\SubscriptionsModule\Seeders\ContentAccessSeeder;
use Crm\SubscriptionsModule\Seeders\SubscriptionExtensionMethodsSeeder;
use Crm\SubscriptionsModule\Seeders\SubscriptionLengthMethodSeeder;
use Crm\SubscriptionsModule\Seeders\SubscriptionTypeNamesSeeder;
use League\Event\Emitter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class SubscriptionsModule extends CrmModule
{
    public function registerAdminMenuItems(MenuContainerInterface $menuContainer)
    {
        $mainMenu = new MenuItem(
            $this->translator->translate('subscriptions.menu.subscriptions'),
            '#subscriptions',
            'fa fa-shopping-cart',
            500
        );

        $menuItem1 = new MenuItem(
            $this->translator->translate('subscriptions.menu.subscription_types'),
            ':Subscriptions:SubscriptionTypesAdmin:default',
            'fa fa-magic',
            600
        );

        $menuItem2 = new MenuItem(
            $this->translator->translate('subscriptions.menu.subscriptions_generator'),
            ':Subscriptions:SubscriptionsGenerator:default',
            'fa fa-copy',
            700
        );

        $mainMenu->addChild($menuItem1);
        $mainMenu->addChild($menuItem2);

        $menuContainer->attachMenuItem($mainMenu);

        // dashboard menu item

        $menuItem = new MenuItem(
            $this->translator->translate('subscriptions.menu.stats'),
            ':Subscriptions:Dashboard:default',
            'fa fa-shopping-cart',
            400
        );
        $menuContainer->attachMenuItemToForeignModule('#dashboard', $mainMenu, $menuItem);

        $menuItem = new MenuItem(
            $this->translator->translate('subscriptions.menu.endings'),
            ':Subscriptions:Dashboard:endings',
            'fa fa-frown-o fa-fw',
            500
        );
        $menuContainer->attachMenuItemToForeignModule('#dashboard', $mainMenu, $menuItem);
    }

    public function registerFrontendMenuItems(MenuContainerInterface $menuContainer)
    {
        $menuItem = new MenuItem($this->translator->translate('subscriptions.menu.subscriptions'), ':Subscriptions:Subscriptions:my', '', 5);
        $menuContainer->attachMenuItem($menuItem);
    }

    public function registerWidgets(WidgetManagerInterface $widgetManager)
    {
        $widgetManager->registerWidget(
            'admin.user.detail.bottom',
            $this->getInstance(\Crm\SubscriptionsModule\Components\UserSubscriptionsListing::class),
            100
        );
        $widgetManager->registerWidgetFactory(
            'admin.payments.listing.action',
            $this->getInstance(\Crm\SubscriptionsModule\Components\SubscriptionButtonFactory::class),
            6000
        );
        $widgetManager->registerWidget(
            'admin.user.detail.box',
            $this->getInstance(\Crm\SubscriptionsModule\Components\ActualUserSubscriptions::class),
            300
        );
        $widgetManager->registerWidget(
            'dashboard.singlestat.totals',
            $this->getInstance(\Crm\SubscriptionsModule\Components\TotalSubscriptionsStatWidget::class),
            600
        );
        $widgetManager->registerWidget(
            'dashboard.singlestat.actuals.subscribers',
            $this->getInstance(\Crm\SubscriptionsModule\Components\ActualSubscribersStatWidget::class),
            700
        );
        $widgetManager->registerWidget(
            'dashboard.singlestat.actuals.system',
            $this->getInstance(\Crm\SubscriptionsModule\Components\ActualSubscriptionsStatWidget::class),
            500
        );
        $widgetManager->registerWidget(
            'dashboard.singlestat.today',
            $this->getInstance(\Crm\SubscriptionsModule\Components\TodaySubscriptionsStatWidget::class),
            500
        );
        $widgetManager->registerWidget(
            'dashboard.singlestat.month',
            $this->getInstance(\Crm\SubscriptionsModule\Components\MonthSubscriptionsStatWidget::class),
            600
        );
        $widgetManager->registerWidget(
            'dashboard.singlestat.mtd',
            $this->getInstance(\Crm\SubscriptionsModule\Components\MonthToDateSubscriptionsStatWidget::class),
            600
        );
        $widgetManager->registerWidget(
            'dashboard.bottom',
            $this->getInstance(\Crm\SubscriptionsModule\Components\EndingSubscriptionsWidget::class),
            100
        );
        $widgetManager->registerWidget(
            'subscriptions.endinglist',
            $this->getInstance(\Crm\SubscriptionsModule\Components\SubscriptionsEndingWithinPeriodWidget::class),
            500
        );
        $widgetManager->registerWidget(
            'subscriptions.endinglist',
            $this->getInstance(\Crm\SubscriptionsModule\Components\RenewedSubscriptionsEndingWithinPeriodWidget::class),
            600
        );
        $widgetManager->registerWidget(
            'admin.users.header',
            $this->getInstance(\Crm\SubscriptionsModule\Components\MonthSubscriptionsSmallBarGraphWidget::class),
            600
        );
        $widgetManager->registerWidget(
            'admin.user.list.emailcolumn',
            $this->getInstance(\Crm\SubscriptionsModule\Components\ActualSubscriptionLabel::class),
            600
        );
        $widgetManager->registerWidget(
            'admin.payments.top',
            $this->getInstance(\Crm\SubscriptionsModule\Components\PrintSubscribersWithoutPrintAddressWidget::class),
            2000
        );
    }

    public function registerEventHandlers(Emitter $emitter)
    {
        $emitter->addListener(
            \Crm\UsersModule\Events\NewAddressEvent::class,
            $this->getInstance(\Crm\SubscriptionsModule\Events\NewAddressHandler::class),
            600
        );
        $emitter->addListener(
            \Crm\SubscriptionsModule\Events\NewSubscriptionEvent::class,
            $this->getInstance(\Crm\ApplicationModule\Events\RefreshUserDataTokenHandler::class),
            600
        );
        $emitter->addListener(
            \Crm\SubscriptionsModule\Events\NewSubscriptionEvent::class,
            $this->getInstance(\Crm\SubscriptionsModule\Events\SubscriptionUpdatedHandler::class)
        );
        $emitter->addListener(
            \Crm\SubscriptionsModule\Events\SubscriptionUpdatedEvent::class,
            $this->getInstance(\Crm\SubscriptionsModule\Events\SubscriptionUpdatedHandler::class)
        );
    }

    public function registerCommands(CommandsContainerInterface $commandsContainer)
    {
        $commandsContainer->registerCommand($this->getInstance(\Crm\SubscriptionsModule\Commands\ChangeSubscriptionsStateCommand::class));
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(
            new ApiRoute(new ApiIdentifier('1', 'users', 'subscriptions'), 'Crm\SubscriptionsModule\Api\v1\UsersSubscriptionsHandler', 'Crm\UsersModule\Auth\UserTokenAuthorization')
        );

        $apiRoutersContainer->attachRouter(
            new ApiRoute(new ApiIdentifier('1', 'subscriptions', 'create'), 'Crm\SubscriptionsModule\Api\v1\CreateSubscriptionHandler', 'Crm\ApiModule\Authorization\BearerTokenAuthorization')
        );
    }

    public function registerUserData(UserDataRegistrator $dataRegistrator)
    {
        $dataRegistrator->addUserDataProvider($this->getInstance(\Crm\SubscriptionsModule\User\SubscriptionsUserDataProvider::class));
    }

    public function registerSegmentCriteria(CriteriaStorage $criteriaStorage)
    {
        $criteriaStorage->register('users', 'active_subscription', $this->getInstance(\Crm\SubscriptionsModule\Segment\ActiveSubscriptionCriteria::class));
    }

    public function registerRoutes(RouteList $router)
    {
        $router[] = new Route('subscriptions/[funnel/<funnel>]', 'Subscriptions:Subscriptions:new');
    }

    public function registerSeeders(SeederManager $seederManager)
    {
        $seederManager->addSeeder($this->getInstance(ContentAccessSeeder::class));
        $seederManager->addSeeder($this->getInstance(SubscriptionExtensionMethodsSeeder::class));
        $seederManager->addSeeder($this->getInstance(SubscriptionLengthMethodSeeder::class));
        $seederManager->addSeeder($this->getInstance(SubscriptionTypeNamesSeeder::class));
    }

    public function registerAccessProvider(AccessManager $accessManager)
    {
        $accessManager->addAccessProvider($this->getInstance(\Crm\SubscriptionsModule\Access\SubscriptionAccessProvider::class));
    }

    public function registerDataProviders(DataProviderManager $dataProviderManager)
    {
        $dataProviderManager->registerDataProvider(
            'users.dataprovider.filter_users_form',
            $this->getInstance(\Crm\SubscriptionsModule\DataProvider\FilterUsersFormDataProvider::class)
        );
        $dataProviderManager->registerDataProvider(
            'users.dataprovider.filter_users_selection',
            $this->getInstance(\Crm\SubscriptionsModule\DataProvider\FilterUsersSelectionDataProvider::class)
        );
        $dataProviderManager->registerDataProvider(
            'users.dataprovider.filter_user_actions_log_selection',
            $this->getInstance(\Crm\SubscriptionsModule\DataProvider\FilterUserActionLogsSelectionDataProvider::class)
        );
        $dataProviderManager->registerDataProvider(
            'users.dataprovider.filter_user_actions_log_form',
            $this->getInstance(\Crm\SubscriptionsModule\DataProvider\FilterUserActionLogsFormDataProvider::class)
        );
    }
}
