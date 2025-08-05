<?php

namespace App\Jobs;

use App\Models\EmailSend;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSingleEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public EmailSend $emailSend
    ) {}

    public function handle(): void
    {
        try {
            $campaign = $this->emailSend->campaign;
            $contact = $this->emailSend->contact;
            $template = $campaign->template;

            if (!$contact->email) {
                throw new \Exception('Contact has no email address');
            }

            $variables = [
                'business_name' => $contact->business_name,
                'contact_name' => $contact->business_name,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'unsubscribe_url' => route('email.unsubscribe', $this->emailSend->tracking_id),
                'tracking_pixel' => route('email.track-open', $this->emailSend->tracking_id)
            ];

            $subject = $template->renderSubject($variables);
            $content = $template->renderContent($variables);
            
            // Ajouter le pixel de tracking
            $content .= '<img src="' . $variables['tracking_pixel'] . '" width="1" height="1" style="display:none;" />';
            
            // Ajouter le lien de désabonnement
            $content .= '<br><small><a href="' . $variables['unsubscribe_url'] . '">Se désabonner</a></small>';

            Mail::html($content, function ($message) use ($contact, $subject) {
                $message->to($contact->email, $contact->business_name)
                       ->subject($subject)
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->emailSend->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            Log::info('Email sent successfully', [
                'email_send_id' => $this->emailSend->id,
                'contact_email' => $contact->email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'email_send_id' => $this->emailSend->id,
                'error' => $e->getMessage()
            ]);

            $this->emailSend->update([
                'status' => 'failed',
                'error_details' => [
                    'message' => $e->getMessage(),
                    'failed_at' => now()->toISOString()
                ]
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->emailSend->update([
            'status' => 'failed',
            'error_details' => [
                'message' => $exception->getMessage(),
                'failed_permanently_at' => now()->toISOString()
            ]
        ]);
    }
}
