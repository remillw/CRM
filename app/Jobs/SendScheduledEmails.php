<?php

namespace App\Jobs;

use App\Models\EmailCampaignSchedule;
use App\Services\EmailTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignEmail;

class SendScheduledEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes timeout

    public function __construct(
        public EmailCampaignSchedule $schedule
    ) {}

    public function handle(EmailTrackingService $trackingService): void
    {
        Log::info("Démarrage de l'envoi de la campagne programmée: {$this->schedule->name}");

        // Vérifier que la campagne peut être envoyée
        if (!$this->schedule->canBeSent()) {
            Log::warning("La campagne {$this->schedule->id} ne peut pas être envoyée");
            return;
        }

        // Marquer comme en cours d'envoi
        $this->schedule->markAsStarted();

        $successCount = 0;
        $failureCount = 0;

        try {
            // Récupérer tous les contacts ciblés
            $contacts = $this->schedule->getTargetContacts()->get();

            Log::info("Envoi à {$contacts->count()} contacts pour la campagne {$this->schedule->name}");

            foreach ($contacts as $contact) {
                try {
                    // Préparer les variables pour ce contact
                    $variables = $trackingService->getContactVariables($contact);
                    
                    // Préparer l'email avec tracking
                    $emailData = $trackingService->prepareEmailWithTracking(
                        $this->schedule->template, 
                        $contact, 
                        $variables
                    );
                    
                    // Créer l'EmailSend record
                    // Pour les tests, utiliser null au lieu du campaign_id
                    $campaignId = $this->schedule->is_test ? null : $contact->campaign_id;
                    $emailSend = $trackingService->createEmailSend(
                        $contact,
                        $campaignId,
                        $emailData['tracking_id'],
                        $this->schedule->template->name
                    );
                    
                    // Associer à la campagne programmée
                    $emailSend->update(['campaign_schedule_id' => $this->schedule->id]);

                    // Simuler l'envoi (vous pouvez intégrer votre système d'envoi ici)
                    $this->sendEmail($contact, $emailData);
                    
                    $successCount++;
                    $this->schedule->incrementSentCount();
                    
                    Log::info("Email envoyé avec succès à {$contact->email} ({$contact->business_name})");
                    
                    // Délai entre les envois pour éviter d'être marqué comme spam
                    $delay = $this->schedule->send_options['delay_seconds'] ?? 1;
                    if ($delay > 0) {
                        sleep($delay);
                    }
                    
                } catch (\Exception $e) {
                    $failureCount++;
                    $this->schedule->incrementFailedCount();
                    
                    Log::error("Erreur lors de l'envoi à {$contact->email}: " . $e->getMessage());
                }
            }

            // Marquer comme terminé
            $this->schedule->markAsCompleted();
            
            Log::info("Campagne {$this->schedule->name} terminée: {$successCount} succès, {$failureCount} échecs");
            
        } catch (\Exception $e) {
            // Marquer comme échoué
            $this->schedule->markAsFailed();
            
            Log::error("Erreur critique lors de l'envoi de la campagne {$this->schedule->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function sendEmail($contact, $emailData): void
    {
        try {
            // Pour les tests, envoyer vraiment l'email
            if ($this->schedule->is_test) {
                Mail::to($contact->email)->send(
                    new CampaignEmail(
                        $emailData['subject'],
                        $emailData['content'],
                        $emailData['tracking_id']
                    )
                );
                
                Log::info("📧 EMAIL TEST ENVOYÉ", [
                    'to' => $contact->email,
                    'subject' => $emailData['subject'],
                    'tracking_id' => $emailData['tracking_id'],
                    'business_name' => $contact->business_name,
                ]);
            } else {
                // Pour les envois normaux, simuler pour l'instant
                Log::info("📧 EMAIL SIMULÉ (production)", [
                    'to' => $contact->email,
                    'subject' => $emailData['subject'],
                    'tracking_id' => $emailData['tracking_id'],
                    'business_name' => $contact->business_name,
                ]);
                
                // Simuler une réussite (vous pouvez retirer ceci quand vous intégrez un vrai système d'envoi)
                if (rand(1, 100) <= 5) { // 5% de chance d'échec simulé
                    throw new \Exception('Simulation d\'échec d\'envoi');
                }
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi réel de l'email: " . $e->getMessage());
            throw $e;
        }
    }
}