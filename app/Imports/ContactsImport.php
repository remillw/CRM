<?php

namespace App\Imports;

use App\Models\Contact;
use App\Models\Campaign;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Str;

class ContactsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    protected Campaign $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function model(array $row)
    {
        // Normalize phone number
        $phone = $this->normalizePhone($row['phone'] ?? $row['telephone'] ?? null);
        
        if (!$phone) {
            return null; // Skip rows without phone numbers
        }

        // Check if contact already exists for this campaign
        $existingContact = Contact::where('campaign_id', $this->campaign->id)
            ->where('phone', $phone)
            ->first();

        if ($existingContact) {
            return null; // Skip duplicates
        }

        return new Contact([
            'campaign_id' => $this->campaign->id,
            'business_name' => $row['business_name'] ?? $row['name'] ?? $row['nom'] ?? $row['entreprise'] ?? 'Sans nom',
            'owner_name' => $row['owner_name'] ?? $row['proprietaire'] ?? null,
            'phone' => $phone,
            'email' => $this->normalizeEmail($row['email'] ?? $row['mail'] ?? null),
            'website' => $this->normalizeWebsite($row['website'] ?? $row['site'] ?? $row['site_web'] ?? null),
            'address' => $row['address'] ?? $row['adresse'] ?? null,
            'city' => $row['city'] ?? $row['ville'] ?? null,
            'postal_code' => $row['postal_code'] ?? $row['code_postal'] ?? $row['cp'] ?? null,
            'activity_type' => $row['activity_type'] ?? $row['business_type'] ?? $row['type'] ?? $row['activite'] ?? null,
            'google_rating' => $this->normalizeRating($row['rating'] ?? $row['note'] ?? null),
            'review_count' => $this->normalizeNumber($row['review_count'] ?? $row['reviews_count'] ?? $row['avis'] ?? $row['nb_avis'] ?? null),
            'additional_data' => [
                'source' => 'import',
                'import_date' => now()->toDateTimeString(),
                'original_data' => $row
            ],
            'scraped_at' => now(),
        ]);
    }

    public function rules(): array
    {
        return [
            '*.business_name' => 'nullable|string|max:255',
            '*.name' => 'nullable|string|max:255',
            '*.nom' => 'nullable|string|max:255',
            '*.entreprise' => 'nullable|string|max:255',
            '*.owner_name' => 'nullable|string|max:255',
            '*.proprietaire' => 'nullable|string|max:255',
            '*.phone' => 'nullable|string',
            '*.telephone' => 'nullable|string',
            '*.email' => 'nullable|string', // Remove email validation to handle malformed emails
            '*.mail' => 'nullable|string',
            '*.website' => 'nullable|string', // Remove URL validation to handle incomplete URLs
            '*.site' => 'nullable|string',
            '*.site_web' => 'nullable|string',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    protected function normalizePhone($phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Add +33 if it's a French number without country code
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '+33' . substr($phone, 1);
        }

        // Ensure it starts with + for international format
        if (strlen($phone) > 10 && substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        return $phone ?: null;
    }

    protected function normalizeEmail($email): ?string
    {
        if (!$email) {
            return null;
        }

        $email = trim(strtolower($email));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    protected function normalizeWebsite($website): ?string
    {
        if (!$website) {
            return null;
        }

        $website = trim(strtolower($website));

        // Add https:// if no protocol specified
        if (!preg_match('/^https?:\/\//', $website)) {
            $website = 'https://' . $website;
        }

        return filter_var($website, FILTER_VALIDATE_URL) ? $website : null;
    }

    protected function normalizeRating($rating): ?float
    {
        if (!$rating) {
            return null;
        }

        $rating = str_replace(',', '.', $rating);
        $rating = floatval($rating);

        return ($rating >= 0 && $rating <= 5) ? $rating : null;
    }

    protected function normalizeNumber($number): ?int
    {
        if (!$number) {
            return null;
        }

        return intval(preg_replace('/[^0-9]/', '', $number));
    }
}