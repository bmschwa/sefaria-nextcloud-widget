<?php

declare(strict_types=1);

namespace OCA\SefariaDashboard\Service;

use OCA\SefariaDashboard\AppInfo\Application;
use OCP\Dashboard\Model\WidgetItem;
use OCP\Dashboard\Model\WidgetItems;
use OCP\Http\Client\IClientService;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;

class SefariaCalendarService {
    private const CALENDAR_URL = 'https://www.sefaria.org/api/calendars';

    public function __construct(
        private IClientService $clientService,
        private IURLGenerator $urlGenerator,
        private LoggerInterface $logger,
    ) {
    }

    public function getItems(int $limit): WidgetItems {
        try {
            $response = $this->clientService->newClient()->get(self::CALENDAR_URL, [
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 5,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('Sefaria returned HTTP ' . $response->getStatusCode());
            }

            $payload = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $calendarItems = $payload['calendar_items'] ?? [];
            $items = [];

            foreach ($calendarItems as $calendarItem) {
                $displayValue = $calendarItem['displayValue']['en'] ?? '';
                $title = $calendarItem['title']['en'] ?? '';
                $url = $calendarItem['url'] ?? '';

                if ($title === '' || $displayValue === '' || $url === '') {
                    continue;
                }

                $iconUrl = $this->urlGenerator->getAbsoluteURL(
                    $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg')
                );
                //$this->logger->debug('Sefaria widget icon points to: ' . $iconUrl);

                $linkUrl = 'https://www.sefaria.org/' . ltrim($url, '/');

                $items[] = new WidgetItem(
                    $displayValue, // title
                    $title, // subtitle
                    $linkUrl, // link
                    $iconUrl, // iconURL
                    $payload['date'] ?? '', // sinceId
                    //overlayIconUrl
                );

                if (count($items) >= $limit) {
                    break;
                }
            }

            return new WidgetItems(
                $items,
                $items === [] ? 'Sefaria learning is unavailable right now.' : '',
            );
        } catch (\Throwable $exception) {
            $this->logger->warning('Unable to load the Sefaria calendar: ' . $exception->getMessage());

            return new WidgetItems([], 'Sefaria learning is unavailable right now.');
        }
    }
}