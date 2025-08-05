<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class WebScrapingService
{
    private Client $client;
    private array $defaultHeaders;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);

        $this->defaultHeaders = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'DNT' => '1',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
        ];
    }

    public function scrapeUrl(string $url, array $selectors = [], array $options = []): array
    {
        try {
            $headers = array_merge($this->defaultHeaders, $options['headers'] ?? []);
            
            $response = $this->client->request('GET', $url, [
                'headers' => $headers,
                'allow_redirects' => true,
            ]);

            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            $result = [
                'url' => $url,
                'status_code' => $response->getStatusCode(),
                'title' => $this->getTitle($crawler),
                'meta_description' => $this->getMetaDescription($crawler),
                'data' => [],
                'raw_html' => $options['include_html'] ?? false ? $html : null,
            ];

            if (!empty($selectors)) {
                $result['data'] = $this->extractData($crawler, $selectors);
            }

            return $result;

        } catch (RequestException $e) {
            Log::error('Scraping failed for URL: ' . $url, [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'url' => $url,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ];
        }
    }

    public function scrapeMultipleUrls(array $urls, array $selectors = [], array $options = []): array
    {
        $results = [];
        $delay = $options['delay'] ?? 1;

        foreach ($urls as $url) {
            $results[] = $this->scrapeUrl($url, $selectors, $options);
            
            if ($delay > 0 && count($urls) > 1) {
                sleep($delay);
            }
        }

        return $results;
    }

    public function extractEmails(string $url): array
    {
        $result = $this->scrapeUrl($url, [], ['include_html' => true]);
        
        if (isset($result['error'])) {
            return $result;
        }

        $html = $result['raw_html'];
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $html, $matches);
        
        return [
            'url' => $url,
            'emails' => array_unique($matches[0]),
            'count' => count(array_unique($matches[0]))
        ];
    }

    public function extractLinks(string $url, bool $external_only = false): array
    {
        $result = $this->scrapeUrl($url);
        
        if (isset($result['error'])) {
            return $result;
        }

        $crawler = new Crawler($result['raw_html'] ?? '');
        $links = [];
        $baseHost = parse_url($url, PHP_URL_HOST);

        $crawler->filter('a[href]')->each(function (Crawler $node) use (&$links, $url, $baseHost, $external_only) {
            $href = $node->attr('href');
            $text = trim($node->text());
            
            if (empty($href) || $href === '#') {
                return;
            }

            $absoluteUrl = $this->makeAbsoluteUrl($href, $url);
            $linkHost = parse_url($absoluteUrl, PHP_URL_HOST);
            
            if ($external_only && $linkHost === $baseHost) {
                return;
            }

            $links[] = [
                'url' => $absoluteUrl,
                'text' => $text,
                'is_external' => $linkHost !== $baseHost
            ];
        });

        return [
            'url' => $url,
            'links' => $links,
            'count' => count($links)
        ];
    }

    public function extractImages(string $url): array
    {
        $result = $this->scrapeUrl($url);
        
        if (isset($result['error'])) {
            return $result;
        }

        $crawler = new Crawler($result['raw_html'] ?? '');
        $images = [];

        $crawler->filter('img')->each(function (Crawler $node) use (&$images, $url) {
            $src = $node->attr('src');
            $alt = $node->attr('alt');
            
            if (!empty($src)) {
                $images[] = [
                    'src' => $this->makeAbsoluteUrl($src, $url),
                    'alt' => $alt,
                ];
            }
        });

        return [
            'url' => $url,
            'images' => $images,
            'count' => count($images)
        ];
    }

    public function extractTable(string $url, string $tableSelector = 'table'): array
    {
        $result = $this->scrapeUrl($url);
        
        if (isset($result['error'])) {
            return $result;
        }

        $crawler = new Crawler($result['raw_html'] ?? '');
        $tables = [];

        $crawler->filter($tableSelector)->each(function (Crawler $table, $index) use (&$tables) {
            $headers = [];
            $rows = [];

            $table->filter('thead tr th, thead tr td, tr:first-child th, tr:first-child td')->each(function (Crawler $header) use (&$headers) {
                $headers[] = trim($header->text());
            });

            $table->filter('tbody tr, tr')->each(function (Crawler $row, $rowIndex) use (&$rows, $headers) {
                if ($rowIndex === 0 && !empty($headers)) {
                    return;
                }

                $rowData = [];
                $row->filter('td, th')->each(function (Crawler $cell, $cellIndex) use (&$rowData, $headers) {
                    $key = $headers[$cellIndex] ?? "column_$cellIndex";
                    $rowData[$key] = trim($cell->text());
                });

                if (!empty($rowData)) {
                    $rows[] = $rowData;
                }
            });

            $tables[] = [
                'index' => $index,
                'headers' => $headers,
                'rows' => $rows,
                'count' => count($rows)
            ];
        });

        return [
            'url' => $url,
            'tables' => $tables,
            'count' => count($tables)
        ];
    }

    private function extractData(Crawler $crawler, array $selectors): array
    {
        $data = [];

        foreach ($selectors as $key => $selector) {
            if (is_array($selector)) {
                $elements = $crawler->filter($selector['selector']);
                $results = [];

                $elements->each(function (Crawler $node) use (&$results, $selector) {
                    if (isset($selector['attribute'])) {
                        $results[] = $node->attr($selector['attribute']);
                    } else {
                        $results[] = trim($node->text());
                    }
                });

                $data[$key] = $results;
            } else {
                try {
                    $element = $crawler->filter($selector);
                    $data[$key] = $element->count() > 0 ? trim($element->text()) : null;
                } catch (\Exception $e) {
                    $data[$key] = null;
                }
            }
        }

        return $data;
    }

    private function getTitle(Crawler $crawler): ?string
    {
        try {
            $title = $crawler->filter('title');
            return $title->count() > 0 ? trim($title->text()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getMetaDescription(Crawler $crawler): ?string
    {
        try {
            $meta = $crawler->filter('meta[name="description"]');
            return $meta->count() > 0 ? $meta->attr('content') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function makeAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';

        if (str_starts_with($url, '//')) {
            return $scheme . ':' . $url;
        }

        if (str_starts_with($url, '/')) {
            return $scheme . '://' . $host . $url;
        }

        $path = rtrim($parsed['path'] ?? '', '/');
        return $scheme . '://' . $host . $path . '/' . $url;
    }
}