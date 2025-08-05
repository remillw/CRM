<?php

namespace App\Http\Controllers;

use App\Models\EmailSend;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    public function trackOpen(string $trackingId): Response
    {
        $emailSend = EmailSend::where('tracking_id', $trackingId)->first();
        
        if ($emailSend) {
            $emailSend->markAsOpened();
        }

        // Retourner un pixel de tracking transparent
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Content-Length' => strlen($pixel),
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ]);
    }

    public function trackClick(Request $request, string $trackingId)
    {
        $emailSend = EmailSend::where('tracking_id', $trackingId)->first();
        $url = $request->get('url');
        
        if ($emailSend) {
            $emailSend->markAsClicked();
        }

        return redirect($url ?? '/');
    }

    public function unsubscribe(string $trackingId)
    {
        $emailSend = EmailSend::where('tracking_id', $trackingId)->first();
        
        if ($emailSend) {
            $emailSend->update([
                'unsubscribed_at' => now(),
                'status' => 'unsubscribed'
            ]);
        }

        return view('emails.unsubscribed');
    }
}
