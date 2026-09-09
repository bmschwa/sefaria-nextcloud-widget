<?php

declare(strict_types=1);

namespace OCA\SefariaDashboard\Dashboard;

use OCA\SefariaDashboard\AppInfo\Application;
use OCA\SefariaDashboard\Service\SefariaCalendarService;
use OCP\Dashboard\IAPIWidgetV2;
use OCP\Dashboard\IIconWidget;
use OCP\Dashboard\IReloadableWidget;
use OCP\Dashboard\Model\WidgetItems;
use OCP\IL10N;
use OCP\IURLGenerator;

class SefariaWidget implements IAPIWidgetV2, IIconWidget, IReloadableWidget {
    public function __construct(
        private IL10N $l10n,
        private IURLGenerator $urlGenerator,
        private SefariaCalendarService $calendarService,
    ) {
    }

    public function getId(): string {
        return 'sefaria_dashboard';
    }

    public function getTitle(): string {
        return $this->l10n->t("Today's learning");
    }

    public function getOrder(): int {
        return 50;
    }

    public function getIconClass(): string {
        return 'icon-sefaria-dashboard';
    }

    public function getIconUrl(): string {
        return $this->urlGenerator->getAbsoluteURL(
            $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg')
        );
    }

    public function getUrl(): ?string {
        return 'https://www.sefaria.org/calendars';
    }

    public function load(): void {
    }

    public function getItemsV2(string $userId, ?string $since = null, int $limit = 7): WidgetItems {
        return $this->calendarService->getItems(max(1, min($limit, 20)));
    }

    public function getReloadInterval(): int {
        return 3600;
    }
}