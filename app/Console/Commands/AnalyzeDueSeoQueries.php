<?php

namespace App\Console\Commands;

use App\Models\SeoQuery;
use App\Jobs\AnalyzeCustomSeoQuery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AnalyzeDueSeoQueries extends Command
{
    protected $signature = 'seo:analyze-due-queries';
    protected $description = 'Analyser toutes les requêtes SEO qui sont dues pour une analyse';

    public function handle(): int
    {
        $this->info('🔍 Recherche des requêtes SEO dues pour analyse...');
        
        $dueQueries = SeoQuery::dueForAnalysis()->get();
        
        if ($dueQueries->isEmpty()) {
            $this->info('✅ Aucune requête SEO n\'est due pour analyse.');
            return Command::SUCCESS;
        }
        
        $this->info("📋 {$dueQueries->count()} requête(s) SEO trouvée(s) pour analyse :");
        
        $totalJobs = 0;
        
        foreach ($dueQueries as $query) {
            $this->line("   • {$query->name} ({$query->query})");
            
            // Récupérer les contacts ciblés
            $targetContacts = $query->getTargetContacts()->get();
            
            if ($targetContacts->isEmpty()) {
                $this->warn("     ⚠️  Aucun contact avec site web trouvé pour cette requête");
                continue;
            }
            
            // Programmer les jobs d'analyse
            foreach ($targetContacts as $contact) {
                AnalyzeCustomSeoQuery::dispatch($query, $contact);
                $totalJobs++;
            }
            
            // Mettre à jour les timestamps
            $query->update(['last_analyzed_at' => now()]);
            $query->scheduleNextAnalysis();
            
            $this->line("     ✅ {$targetContacts->count()} analyse(s) programmée(s)");
            
            // Éviter le rate limiting
            if ($targetContacts->count() > 10) {
                $this->line("     ⏳ Pause de 5 secondes pour éviter le rate limiting...");
                sleep(5);
            }
        }
        
        Log::info("Commande SEO : {$dueQueries->count()} requêtes analysées, {$totalJobs} jobs programmés");
        
        $this->info("🎉 Analyse terminée ! {$totalJobs} job(s) d'analyse SEO programmé(s) en arrière-plan.");
        
        return Command::SUCCESS;
    }
}