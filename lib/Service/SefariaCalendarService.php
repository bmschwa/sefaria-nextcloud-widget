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

    private function extractCategoryLabel(array $calendarItem): string {
        $category = $calendarItem['category'] ?? '';

        if (is_array($category)) {
            $category = $category['en'] ?? $category['text'] ?? '';
        }

        if ($category === '') {
            $category = $calendarItem['title']['en'] ?? $calendarItem['displayValue']['en'] ?? 'SEFARIA';
        }

        return trim((string) $category);
    }

    private function buildCategoryIconSvg(array $calendarItem): string {
        $categoryLabel = $this->extractCategoryLabel($calendarItem);
        $safeText = preg_replace('/\s+/', ' ', $categoryLabel);
        $safeText = preg_replace('/[^\pL\pN\s\-]/u', '', $safeText ?? 'SEFARIA');
        $safeText = trim((string) $safeText);
        $safeText = strtoupper(substr($safeText, 0, 18));
        $safeText = $safeText === '' ? 'SEFARIA' : $safeText;

        $svg = sprintf(
            <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128" role="img" aria-label="%s">
              <defs>
                <path id="sefaria-curve" d="M 21 56 A 43 43 0 1 1 107 56" />
              </defs>
              <rect width="128" height="128" rx="18" fill="#18345d" />
              <circle cx="64" cy="64" r="41" fill="none" stroke="#ffffff" stroke-opacity="0.18" stroke-width="2" />
              <g transform="translate(0 3)">
                <path d="M40 39.5c-2.8 1.5-5.7 4.3-5.7 8.6 0 2.6 1.1 4.7 2.9 6.1 1.7 1.3 4.1 2.2 6.6 2.8 3.2 0.8 5.7 1.4 7.6 2.7 1.7 1.1 2.8 2.8 2.8 5.4 0 5.7-4.2 8.9-10.7 8.9-3.4 0-5.6-0.6-8.2-2.1-1.9-1.1-3.7-2.7-5.2-4.8l4.7-3.7c1.1 1.5 2.5 2.7 4.1 3.6 1.2 0.7 2.6 1 4.1 1 3.7 0 5.4-1.6 5.4-4.1 0-1.7-0.9-2.8-2.4-3.5-1.3-0.6-2.9-1-5.1-1.5-3.5-0.8-6.2-1.8-8.1-3.2-1.9-1.5-3.1-3.7-3.1-6.6 0-5.8 4.3-9.7 10.5-11.1l1.7-0.4 1.4 5.3-1.4 0.3c-3.5 0.7-5.5 2.1-5.5 4.8 0 2.7 2 4.1 5.3 4.7 1.5 0.3 2.7 0.7 3.6 1.2 1.4 0.8 2.3 1.9 2.3 3.5 0 1.9-1.7 3.2-4.6 3.2-1.9 0-3.6-0.6-5.3-1.9-0.8-0.6-1.5-1.4-2.2-2.2l-4.6 3.4c1.8 2.8 4.5 4.8 8.2 5.8 2.2 0.6 4.5 1 6.8 1 7.7 0 13.2-4.7 13.2-11.5 0-4.4-2.3-7.6-6.3-9.4-2.5-1.1-5.4-1.8-8.7-2.5-2.3-0.5-4.5-1.2-5.8-2.3-1.2-1.1-1.8-2.3-1.8-4 0-3 2.5-4.9 6.5-4.9 3.4 0 5.3 1.1 7.4 3.2l4.3-4.2c-2.6-3.3-6.4-5-11.8-5-7.5 0-12.9 4.2-12.9 10.5 0 3.5 1.8 6.2 5.5 8.1z" fill="#ffffff" fill-rule="evenodd"/>
              </g>
              <text fill="#dfe8ff" font-family="Arial, sans-serif" font-size="11" font-weight="700" letter-spacing="1.2">
                <textPath href="#sefaria-curve" startOffset="50%" text-anchor="middle">%s</textPath>
              </text>
            </svg>
            SVG,
            htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($safeText, ENT_QUOTES, 'UTF-8')
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
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

                $iconUrl = $this->buildCategoryIconSvg($calendarItem);
                $this->logger->debug('Sefaria widget icon points to category text: ' . $this->extractCategoryLabel($calendarItem));

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