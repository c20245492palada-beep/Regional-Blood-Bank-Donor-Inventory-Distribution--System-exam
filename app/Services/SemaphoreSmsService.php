<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

class SemaphoreSmsService
{
    public function isConfigured(): bool
    {
        $apiKey = config('services.semaphore.key');

        return is_string($apiKey) && trim($apiKey) !== '';
    }

    /**
     * @throws ConnectionException
     */
    public function send(string $phoneNumber, string $message): void
    {
        $apiKey = config('services.semaphore.key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('The Semaphore API key is not configured.');
        }

        if (! preg_match('/^\+639\d{9}$/', $phoneNumber)) {
            throw new InvalidArgumentException('The phone number must use the +639XXXXXXXXX format.');
        }

        $payload = [
            'apikey' => $apiKey,
            'number' => '0'.substr($phoneNumber, 3),
            'message' => $message,
        ];

        $senderName = config('services.semaphore.sender_name');

        if (is_string($senderName) && $senderName !== '') {
            $payload['sendername'] = $senderName;
        }

        $response = Http::asForm()
            ->connectTimeout(5)
            ->timeout(10)
            ->post('https://api.semaphore.co/api/v4/messages', $payload);

        $response->throw();

        $messages = $response->json();

        if (! is_array($messages) || $messages === []) {
            throw new UnexpectedValueException('Semaphore returned an unexpected SMS response.');
        }

        foreach ($messages as $smsMessage) {
            if (is_array($smsMessage) && strtolower((string) ($smsMessage['status'] ?? '')) === 'failed') {
                throw new RuntimeException('Semaphore could not queue the SMS message.');
            }
        }
    }
}
