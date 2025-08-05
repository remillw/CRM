<?php

namespace App\Jobs;

use App\Models\EmailSend;
use App\Models\EmailTemplate;
use App\Services\EmailTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailFollowUps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout

    public function __construct()
    {
        //
    }

    public function handle(EmailTrackingService $trackingService): void
    {
        Log::info('Démarrage du traitement des relances automatiques');

        $processedCount = 0;
        $followUpsSent = 0;

        // Récupérer tous les emails envoyés qui pourraient nécessiter une relance
        $emailSends = EmailSend::where('sent_at', '<=', now()->subDays(1))
            ->whereNull('follow_up_sent_at') // Pas encore de relance envoyée
            ->with(['contact.campaign'])
            ->get();

        foreach ($emailSends as $emailSend) {
            $processedCount++;
            
            try {
                $recommendations = $trackingService->generateFollowUpRecommendations($emailSend);
                
                foreach ($recommendations as $recommendation) {
                    if ($recommendation['delay_days'] == 0) { // Relance à envoyer maintenant
                        $this->sendFollowUp($emailSend, $recommendation, $trackingService);
                        $followUpsSent++;
                    }
                }
                
            } catch (\Exception $e) {
                Log::error("Erreur lors du traitement de la relance pour EmailSend {$emailSend->id}: " . $e->getMessage());
            }
        }

        Log::info("Traitement des relances terminé. {$processedCount} emails traités, {$followUpsSent} relances envoyées.");
    }

    private function sendFollowUp(EmailSend $originalEmail, array $recommendation, EmailTrackingService $trackingService): void
    {
        // Récupérer le template de relance
        $template = EmailTemplate::where('name', $recommendation['template'])->first();
        
        if (!$template) {
            Log::warning("Template '{$recommendation['template']}' non trouvé pour la relance");
            return;
        }

        // Préparer les variables pour le contact
        $variables = $trackingService->getContactVariables($originalEmail->contact);
        
        // Préparer l'email avec tracking
        $emailData = $trackingService->prepareEmailWithTracking($template, $originalEmail->contact, $variables);
        
        // Créer un nouvel EmailSend pour la relance
        $followUpEmail = $trackingService->createEmailSend(
            $originalEmail->contact,
            $originalEmail->campaign_id,
            $emailData['tracking_id'],
            $template->name
        );

        // Marquer l'email original comme ayant eu une relance
        $originalEmail->update([
            'follow_up_sent_at' => now(),
            'follow_up_type' => $recommendation['type']
        ]);

        // Ici vous pourriez intégrer votre système d'envoi d'email
        // Par exemple: Mail::to($originalEmail->contact->email)->send(new FollowUpMail($emailData));
        
        Log::info("Relance '{$recommendation['type']}' envoyée à {$originalEmail->contact->business_name} (Contact ID: {$originalEmail->contact->id})");
    }
}