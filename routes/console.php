<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programmer l'analyse automatique des requêtes SEO
Schedule::command('seo:analyze-due-queries')
    ->dailyAt('09:00')
    ->timezone('Europe/Paris')
    ->runInBackground()
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputOnFailure(config('mail.admin_email'));
