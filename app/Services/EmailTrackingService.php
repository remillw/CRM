<?php

namespace App\Services;

use App\Models\EmailSend;
use App\Models\EmailTemplate;
use App\Models\Contact;
use Illuminate\Support\Str;

class EmailTrackingService
{
    public function prepareEmailWithTracking(EmailTemplate $template, Contact $contact, array $variables = []): array
    {
        // Créer un tracking ID unique
        $trackingId = Str::uuid();
        
        // Préparer le pixel de tracking
        $trackingPixel = $this->generateTrackingPixel($trackingId);
        
        // Préparer le lien de désinscription
        $unsubscribeLink = route('email.unsubscribe', ['trackingId' => $trackingId]);
        
        // Ajouter les variables de tracking
        $variables = array_merge($variables, [
            'tracking_pixel' => $trackingPixel,
            'unsubscribe_link' => $unsubscribeLink,
        ]);
        
        // Rendre le contenu avec les variables
        $subject = $template->renderSubject($variables);
        $content = $template->renderContent($variables);
        
        return [
            'tracking_id' => $trackingId,
            'subject' => $subject,
            'content' => $content,
            'variables' => $variables,
        ];
    }
    
    public function createEmailSend(Contact $contact, $campaignId, string $trackingId, string $templateName): EmailSend
    {
        return EmailSend::create([
            'contact_id' => $contact->id,
            'campaign_id' => $campaignId,
            'tracking_id' => $trackingId,
            'status' => 'sent',
            'sent_at' => now(),
            'template_name' => $templateName,
        ]);
    }
    
    public function getContactVariables(Contact $contact): array
    {
        return [
            'business_name' => $contact->business_name,
            'owner_name' => $contact->owner_name ?: $contact->business_name,
            'activity_type' => $contact->campaign->activity_type ?? 'entreprise',
            'city' => $contact->campaign->city ?? 'votre ville',
            'website' => $contact->website,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ];
    }
    
    public function generateFollowUpRecommendations(EmailSend $emailSend): array
    {
        $recommendations = [];
        
        $daysSinceSent = now()->diffInDays($emailSend->sent_at);
        
        // Première relance pour emails non ouverts après 3 jours
        if ($daysSinceSent >= 3 && !$emailSend->opened_at) {
            $recommendations[] = [
                'type' => 'first_follow_up',
                'priority' => 'high',
                'template' => 'Relance - Pas de réponse première prospection',
                'reason' => 'Email non ouvert après 3 jours',
                'delay_days' => 0, // Maintenant
            ];
        }
        
        // Seconde relance pour emails non ouverts après 7 jours
        if ($daysSinceSent >= 7 && !$emailSend->opened_at) {
            $recommendations[] = [
                'type' => 'second_follow_up',
                'priority' => 'urgent',
                'template' => 'Relance - Seconde relance',
                'reason' => 'Email non ouvert après 7 jours',
                'delay_days' => 0, // Maintenant
            ];
        }
        
        // Relance pour emails ouverts mais sans clic après 2 jours
        if ($emailSend->opened_at && !$emailSend->clicked_at) {
            $daysSinceOpened = now()->diffInDays($emailSend->opened_at);
            if ($daysSinceOpened >= 2) {
                $recommendations[] = [
                    'type' => 'opened_no_response',
                    'priority' => 'medium',
                    'template' => 'Email ouvert mais pas de réponse',
                    'reason' => 'Email ouvert mais pas de clic après 2 jours',
                    'delay_days' => 1, // Dans 1 jour
                ];
            }
        }
        
        return $recommendations;
    }
    
    public function getEmailStats(EmailSend $emailSend): array
    {
        return [
            'sent_at' => $emailSend->sent_at,
            'opened_at' => $emailSend->opened_at,
            'clicked_at' => $emailSend->clicked_at,
            'bounced_at' => $emailSend->bounced_at,
            'unsubscribed_at' => $emailSend->unsubscribed_at,
            'status' => $emailSend->status,
            'is_opened' => !is_null($emailSend->opened_at),
            'is_clicked' => !is_null($emailSend->clicked_at),
            'days_since_sent' => now()->diffInDays($emailSend->sent_at),
            'engagement_score' => $this->calculateEngagementScore($emailSend),
        ];
    }
    
    private function generateTrackingPixel(string $trackingId): string
    {
        $trackingUrl = route('email.track-open', ['trackingId' => $trackingId]);
        return "<img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\">";
    }
    
    private function calculateEngagementScore(EmailSend $emailSend): int
    {
        $score = 0;
        
        if ($emailSend->opened_at) {
            $score += 30; // Points pour ouverture
        }
        
        if ($emailSend->clicked_at) {
            $score += 50; // Points pour clic
        }
        
        // Pénalité pour les emails anciens non ouverts
        $daysSinceSent = now()->diffInDays($emailSend->sent_at);
        if ($daysSinceSent > 7 && !$emailSend->opened_at) {
            $score -= 20;
        }
        
        return max(0, min(100, $score));
    }
}