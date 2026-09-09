<?php

declare(strict_types=1);

namespace OCA\SefariaDashboard\AppInfo;

use OCA\SefariaDashboard\Dashboard\SefariaWidget;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

if (class_exists('OCA\\SefariaDashboard\\AppInfo\\Application', false)) {
    return;
}

class Application extends App implements IBootstrap {
    public const APP_ID = 'sefaria_dashboard';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerDashboardWidget(SefariaWidget::class);
    }

    public function boot(IBootContext $context): void {
    }
}