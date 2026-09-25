<?php

declare(strict_types=1);

namespace OCA\SefariaDashboard\Service;

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

        $fontSize = 24;
        $labelLength = mb_strlen($safeText, 'UTF-8');

        if ($labelLength > 10) {
            $fontSize = max(12, 24 - (($labelLength - 10) * 1.2));
        }

        $baseSvgPath = dirname(__DIR__, 2) . '/img/app.svg';
        $baseSvg = @file_get_contents($baseSvgPath);
        $baseShape = '';

        if (is_string($baseSvg) && $baseSvg !== '') {
            preg_match_all('/<path[^>]*d="([^"]+)"[^>]*fill="([^"]+)"[^>]*\/?>/i', $baseSvg, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (isset($match[1], $match[2])) {
                    $baseShape .= '<path d="' . htmlspecialchars($match[1], ENT_QUOTES, 'UTF-8') . '" fill="' . htmlspecialchars($match[2], ENT_QUOTES, 'UTF-8') . '"/>';
                }
            }
        }

        if ($baseShape === '') {
            $baseShape = '<path d="M48 54C52 50 57 48 63 48C70 48 75 51 75 57C75 63 71 66 63 67L56 68C51 69 48 71 48 75C48 80 52 84 60 84C68 84 73 81 77 77" fill="#ffffff" fill-rule="evenodd"/>';
        }

        $svg = sprintf(
            <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128" role="img" aria-label="%s">
              <defs>
                <path id="sefaria-curve" d="M 12 78 C 30 60, 52 42, 76 30 C 96 20, 112 15, 118 24" />
              </defs>
              <rect width="128" height="128" rx="18" fill="#18345d" />
              <g transform="translate(82 74) scale(0.9)">
                %s
              </g>
              <text fill="#dfe8ff" font-family="Arial, sans-serif" font-size="%s" font-weight="700" letter-spacing="1.9">
                <textPath href="#sefaria-curve" startOffset="50%%" text-anchor="middle">%s</textPath>
              </text>
            </svg>
            SVG,
            htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'),
            $baseShape,
            $fontSize,
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