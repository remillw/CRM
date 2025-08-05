<?php

namespace App\Http\Controllers;

use App\Services\WebScrapingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebScrapingController extends Controller
{
    private WebScrapingService $scrapingService;

    public function __construct(WebScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    public function index()
    {
        return Inertia::render('WebScraping/Index');
    }

    public function scrape(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'selectors' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        $url = $request->input('url');
        $selectors = $request->input('selectors', []);
        $options = $request->input('options', []);

        $result = $this->scrapingService->scrapeUrl($url, $selectors, $options);

        return response()->json($result);
    }

    public function scrapeMultiple(Request $request)
    {
        $request->validate([
            'urls' => 'required|array',
            'urls.*' => 'required|url',
            'selectors' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        $urls = $request->input('urls');
        $selectors = $request->input('selectors', []);
        $options = $request->input('options', []);

        $results = $this->scrapingService->scrapeMultipleUrls($urls, $selectors, $options);

        return response()->json($results);
    }

    public function extractEmails(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $result = $this->scrapingService->extractEmails($request->input('url'));

        return response()->json($result);
    }

    public function extractLinks(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'external_only' => 'nullable|boolean',
        ]);

        $result = $this->scrapingService->extractLinks(
            $request->input('url'),
            $request->boolean('external_only', false)
        );

        return response()->json($result);
    }

    public function extractImages(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $result = $this->scrapingService->extractImages($request->input('url'));

        return response()->json($result);
    }

    public function extractTable(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'table_selector' => 'nullable|string',
        ]);

        $result = $this->scrapingService->extractTable(
            $request->input('url'),
            $request->input('table_selector', 'table')
        );

        return response()->json($result);
    }
}