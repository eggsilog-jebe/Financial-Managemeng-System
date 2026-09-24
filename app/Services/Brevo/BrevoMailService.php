<?php

declare(strict_types=1);

namespace App\Services\Brevo;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin wrapper around the Brevo Transactional Email HTTP API (v3).
 *
 * Docs: https://developers.brevo.com/reference/sendtransacemail
 *
 * We use the HTTP API instead of SMTP because the Brevo free plan
 * restricts SMTP relay by IP address. The HTTP API has no such restriction.
 */
final class BrevoMailService
{
    private readonly string $apiKey;
    private readonly string $apiUrl;
    private readonly string $senderEmail;
    private readonly string $senderName;

    public function __construct()
    {
        $this->apiKey      = (string) config('services.brevo.api_key');
        $this->apiUrl      = (string) config('services.brevo.api_url', 'https://api.brevo.com/v3');
        $this->senderEmail = (string) config('services.brevo.sender_email');
        $this->senderName  = (string) config('services.brevo.sender_name');
    }

    /**
     * Send a transactional HTML email via the Brevo API.
     *
     * @param  string  $toEmail    Recipient email address
     * @param  string  $toName     Recipient display name
     * @param  string  $subject    Email subject line
     * @param  string  $htmlContent Full HTML body
     * @param  string  $textContent Plain-text fallback body
     *
     * @throws RuntimeException if the API returns a non-2xx response
     */
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlContent,
        string $textContent = '',
    ): void {
        $payload = [
            'sender' => [
                'email' => $this->senderEmail,
                'name'  => $this->senderName,
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ];

        if ($textContent !== '') {
            $payload['textContent'] = $textContent;
        }

        /** @var Response $response */
        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post("{$this->apiUrl}/smtp/email", $payload);

        if ($response->failed()) {
            $error = $response->json('message', 'Unknown Brevo API error');
            $code  = $response->status();

            Log::error('[BrevoMailService] Failed to send email', [
                'to'         => $toEmail,
                'subject'    => $subject,
                'http_code'  => $code,
                'error'      => $error,
                'body'       => $response->body(),
            ]);

            throw new RuntimeException(
                "[BrevoMailService] API error {$code}: {$error}"
            );
        }

        Log::info('[BrevoMailService] Email sent successfully', [
            'to'         => $toEmail,
            'subject'    => $subject,
            'message_id' => $response->json('messageId', 'N/A'),
        ]);
    }
}
