<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin client for the DeepSeek chat-completions API (OpenAI-compatible).
 * Used for grounded, JSON-mode generation — callers pass a system + user
 * prompt and get back the decoded JSON object.
 */
class DeepSeekClient
{
    public function configured(): bool
    {
        return ! empty(config('services.deepseek.key'));
    }

    /**
     * Run a JSON-mode chat completion and return the decoded object.
     *
     * @return array<string, mixed>|null  null on failure (network, bad JSON, etc.)
     */
    public function jsonCompletion(string $system, string $user, int $maxTokens = 800): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        try {
            $response = Http::withToken(config('services.deepseek.key'))
                ->timeout(25)
                ->acceptJson()
                ->post(rtrim((string) config('services.deepseek.base_url'), '/').'/chat/completions', [
                    'model'           => config('services.deepseek.model'),
                    'response_format' => ['type' => 'json_object'],
                    'temperature'     => 0.4,
                    'max_tokens'      => $maxTokens,
                    'messages'        => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $user],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('DeepSeek request failed', ['status' => $response->status()]);
                return null;
            }

            $content = $response->json('choices.0.message.content');
            if (! is_string($content) || $content === '') {
                return null;
            }

            $decoded = json_decode($content, true);

            return is_array($decoded) ? $decoded : null;
        } catch (RuntimeException $e) {
            Log::warning('DeepSeek request threw', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
