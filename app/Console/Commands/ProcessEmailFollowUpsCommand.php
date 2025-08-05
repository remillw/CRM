<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEmailFollowUps;
use Illuminate\Console\Command;

class ProcessEmailFollowUpsCommand extends Command
{
    protected $signature = 'email:process-followups
                          {--dry-run : Afficher ce qui serait fait sans l\'exécuter}';

    protected $description = 'Traite les relances automatiques d\'emails selon les règles configurées';

    public function handle(): int
    {
        $this->info('🚀 Démarrage du traitement des relances automatiques...');

        if ($this->option('dry-run')) {
            $this->warn('⚠️  Mode DRY-RUN activé - Aucun email ne sera envoyé');
            // TODO: Implémenter le mode dry-run
            return self::SUCCESS;
        }

        try {
            // Dispatcher le job
            ProcessEmailFollowUps::dispatch();
            
            $this->info('✅ Job de traitement des relances lancé avec succès!');
            $this->info('📊 Consultez les logs pour voir les détails du traitement.');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du lancement du job: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}