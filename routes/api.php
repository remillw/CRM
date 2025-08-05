<?php

use App\Http\Controllers\WebScrapingController;
use Illuminate\Support\Facades\Route;

Route::prefix('web-scraping')->group(function () {
    Route::post('scrape', [WebScrapingController::class, 'scrape']);
    Route::post('scrape-multiple', [WebScrapingController::class, 'scrapeMultiple']);
    Route::post('extract-emails', [WebScrapingController::class, 'extractEmails']);
    Route::post('extract-links', [WebScrapingController::class, 'extractLinks']);
    Route::post('extract-images', [WebScrapingController::class, 'extractImages']);
    Route::post('extract-table', [WebScrapingController::class, 'extractTable']);
});