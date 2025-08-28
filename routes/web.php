<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactListController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\SeoQueryController;
use App\Http\Controllers\WebScrapingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $stats = [
            'total_campaigns' => \App\Models\Campaign::count(),
            'total_contacts' => \App\Models\Contact::count(),
            'total_lists' => \App\Models\ContactList::count(),
            'total_email_campaigns' => \App\Models\EmailCampaign::count(),
            'active_campaigns' => \App\Models\Campaign::where('status', 'running')->count(),
            'recent_campaigns' => \App\Models\Campaign::latest()->take(5)->get(),
        ];
        
        return Inertia::render('Dashboard', compact('stats'));
    })->name('dashboard');

    // Campaigns routes - Order matters for routing!
    Route::get('campaigns/create-choice', [CampaignController::class, 'createChoice'])->name('campaigns.create-choice');
    Route::get('campaigns/create-empty', [CampaignController::class, 'createEmpty'])->name('campaigns.create-empty');
    Route::post('campaigns/create-empty', [CampaignController::class, 'storeEmpty'])->name('campaigns.store-empty');
    Route::get('campaigns/create-scraping', [CampaignController::class, 'createScraping'])->name('campaigns.create-scraping');
    Route::post('campaigns/scraping', [CampaignController::class, 'storeScraping'])->name('campaigns.store-scraping');
    Route::get('campaigns/create-with-import', [CampaignController::class, 'createWithImport'])->name('campaigns.create-with-import');
    Route::post('campaigns/import', [CampaignController::class, 'import'])->name('campaigns.import');
    Route::get('campaigns/download-template', [CampaignController::class, 'downloadTemplate'])->name('campaigns.download-template');
    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/restart', [CampaignController::class, 'restart'])->name('campaigns.restart');

    // Contacts routes
    Route::resource('contacts', ContactController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::post('contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
    Route::get('contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::post('contacts/{contact}/toggle-site-good', [ContactController::class, 'toggleSiteGood'])->name('contacts.toggle-site-good');
    Route::post('contacts/{contact}/toggle-can-command', [ContactController::class, 'toggleCanCommand'])->name('contacts.toggle-can-command');
    Route::post('contacts/{contact}/analyze-seo', [ContactController::class, 'analyzeSeo'])->name('contacts.analyze-seo');
    Route::post('contacts/bulk-analyze-seo', [ContactController::class, 'bulkAnalyzeSeo'])->name('contacts.bulk-analyze-seo');
    Route::post('contacts/analyze-campaign-seo', [ContactController::class, 'analyzeCampaignSeo'])->name('contacts.analyze-campaign-seo');

    // Contact Lists routes
    Route::resource('contact-lists', ContactListController::class);
    Route::post('contact-lists/{contactList}/segments', [ContactListController::class, 'createSegment'])->name('contact-lists.segments.store');
    Route::post('contact-lists/{contactList}/contacts/add', [ContactListController::class, 'addContacts'])->name('contact-lists.contacts.add');
    Route::post('contact-lists/{contactList}/contacts/remove', [ContactListController::class, 'removeContacts'])->name('contact-lists.contacts.remove');
    Route::post('contact-lists/{contactList}/campaign/{campaign}', [ContactListController::class, 'addFromCampaign'])->name('contact-lists.add-from-campaign');
    Route::get('contact-lists/{contactList}/export', [ContactListController::class, 'export'])->name('contact-lists.export');
    Route::post('contact-lists/{contactList}/sync', [ContactListController::class, 'sync'])->name('contact-lists.sync');

    // Email Templates routes
    Route::resource('email-templates', EmailTemplateController::class);
    Route::post('email-templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
    Route::post('email-templates/preview-draft', [EmailTemplateController::class, 'previewDraft'])->name('email-templates.preview-draft');
    Route::post('email-templates/send-test', [EmailTemplateController::class, 'sendTestEmail'])->name('email-templates.send-test');
    Route::post('email-templates/{emailTemplate}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('email-templates.duplicate');
    Route::post('email-templates/{emailTemplate}/toggle', [EmailTemplateController::class, 'toggle'])->name('email-templates.toggle');

    // SMS Templates routes
    Route::resource('sms-templates', App\Http\Controllers\SmsTemplateController::class);
    Route::post('sms-templates/send-test', [App\Http\Controllers\SmsTemplateController::class, 'sendTestSms'])->name('sms-templates.send-test');
    Route::post('sms-templates/{smsTemplate}/duplicate', [App\Http\Controllers\SmsTemplateController::class, 'duplicate'])->name('sms-templates.duplicate');
    Route::post('sms-templates/{smsTemplate}/toggle', [App\Http\Controllers\SmsTemplateController::class, 'toggle'])->name('sms-templates.toggle');

    // SMS Campaign Schedules routes
    Route::resource('sms-campaign-schedules', App\Http\Controllers\SmsCampaignScheduleController::class);

    // Email Campaigns routes
    Route::resource('email-campaigns', EmailCampaignController::class);
    Route::post('email-campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send'])->name('email-campaigns.send');

    // Analytics routes
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('analytics/campaigns', [AnalyticsController::class, 'campaigns'])->name('analytics.campaigns');
    Route::get('analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

    // SEO Queries routes
    Route::resource('seo-queries', SeoQueryController::class);
    Route::post('seo-queries/{seoQuery}/analyze', [SeoQueryController::class, 'analyze'])->name('seo-queries.analyze');
    Route::post('seo-queries/{seoQuery}/relaunch', [SeoQueryController::class, 'relaunch'])->name('seo-queries.relaunch');
    Route::post('seo-queries/{seoQuery}/toggle', [SeoQueryController::class, 'toggle'])->name('seo-queries.toggle');
    Route::get('seo-queries/{seoQuery}/results', [SeoQueryController::class, 'results'])->name('seo-queries.results');
    Route::get('seo-queries/compare', [SeoQueryController::class, 'compare'])->name('seo-queries.compare');
    Route::post('seo-queries/analyze-all', [SeoQueryController::class, 'analyzeAll'])->name('seo-queries.analyze-all');
    Route::post('seo-queries/analyze-multiple', [SeoQueryController::class, 'analyzeMultiple'])->name('seo-queries.analyze-multiple');
    Route::post('seo-queries/{seoQuery}/analyze-contact/{contact}', [SeoQueryController::class, 'analyzeContact'])->name('seo-queries.analyze-contact');
    Route::get('seo-queries/{seoQuery}/export', [SeoQueryController::class, 'export'])->name('seo-queries.export');
    Route::get('seo-queries/{seoQuery}/export-results', [SeoQueryController::class, 'exportResults'])->name('seo-queries.export-results');
    Route::get('seo-queries/export-comparison', [SeoQueryController::class, 'exportComparison'])->name('seo-queries.export-comparison');

    // Email Reports routes
    Route::get('email-reports', [App\Http\Controllers\EmailReportController::class, 'index'])->name('email-reports.index');
    Route::get('email-reports/campaign/{campaign}', [App\Http\Controllers\EmailReportController::class, 'campaign'])->name('email-reports.campaign');
    Route::get('email-reports/campaign-schedule/{emailCampaignSchedule}', [App\Http\Controllers\EmailReportController::class, 'campaignSchedule'])->name('email-reports.campaign-schedule');
    Route::post('email-reports/campaign/{campaign}/follow-up', [App\Http\Controllers\EmailReportController::class, 'sendFollowUp'])->name('email-reports.send-follow-up');
    Route::post('email-reports/schedule-follow-up', [App\Http\Controllers\EmailReportController::class, 'sendScheduleFollowUp'])->name('email-reports.send-schedule-follow-up');

    // Email Campaign Schedules routes
    Route::resource('email-campaign-schedules', App\Http\Controllers\EmailCampaignScheduleController::class);
    Route::post('email-campaign-schedules/{emailCampaignSchedule}/send-now', [App\Http\Controllers\EmailCampaignScheduleController::class, 'sendNow'])->name('email-campaign-schedules.send-now');
    Route::post('email-campaign-schedules/{emailCampaignSchedule}/duplicate', [App\Http\Controllers\EmailCampaignScheduleController::class, 'duplicate'])->name('email-campaign-schedules.duplicate');
    Route::get('email-campaign-schedules/{emailCampaignSchedule}/preview', [App\Http\Controllers\EmailCampaignScheduleController::class, 'preview'])->name('email-campaign-schedules.preview');
    Route::post('email-campaign-schedules/send-all-due', [App\Http\Controllers\EmailCampaignScheduleController::class, 'sendAllDue'])->name('email-campaign-schedules.send-all-due');

    // Web Scraping routes
    Route::get('web-scraping', [WebScrapingController::class, 'index'])->name('web-scraping');
});

// Email tracking routes (public)
Route::get('email/track/{trackingId}', [EmailTrackingController::class, 'trackOpen'])->name('email.track-open');
Route::get('email/click/{trackingId}', [EmailTrackingController::class, 'trackClick'])->name('email.track-click');
Route::get('email/unsubscribe/{trackingId}', [EmailTrackingController::class, 'unsubscribe'])->name('email.unsubscribe');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
