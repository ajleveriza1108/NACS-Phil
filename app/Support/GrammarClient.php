<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

final class GrammarClient
{
    public function check(string $text, string $language): array
    {
        if (! (bool) config('learning_tools.grammar.enabled', true)) {
            return ['status' => 'disabled', 'matches' => []];
        }

        $endpoint = (string) config('learning_tools.grammar.endpoint');
        $timeout = (int) config('learning_tools.grammar.timeout_seconds', 8);

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout($timeout)
                ->post($endpoint, [
                    'text' => $text,
                    'language' => $language,
                ]);
        } catch (\Throwable) {
            return ['status' => 'unavailable', 'matches' => []];
        }

        if (! $response->successful()) {
            return ['status' => 'unavailable', 'matches' => []];
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            return ['status' => 'unavailable', 'matches' => []];
        }

        $matches = [];

        foreach (array_slice((array) ($payload['matches'] ?? []), 0, 50) as $match) {
            if (! is_array($match)) {
                continue;
            }

            $replacements = [];

            foreach (array_slice((array) ($match['replacements'] ?? []), 0, 5) as $replacement) {
                $value = trim((string) ($replacement['value'] ?? ''));

                if ($value !== '') {
                    $replacements[] = $value;
                }
            }

            $matches[] = [
                'message' => trim((string) ($match['message'] ?? 'Review this text.')),
                'short_message' => trim((string) ($match['shortMessage'] ?? '')),
                'offset' => (int) ($match['offset'] ?? 0),
                'length' => (int) ($match['length'] ?? 0),
                'context' => trim((string) data_get($match, 'context.text', '')),
                'replacements' => $replacements,
            ];
        }

        return [
            'status' => 'ok',
            'language' => trim((string) data_get($payload, 'language.name', $language)),
            'matches' => $matches,
        ];
    }
}
