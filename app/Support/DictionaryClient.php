<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class DictionaryClient
{
    public function lookup(string $word): array
    {
        $word = mb_strtolower(trim($word));

        if (! (bool) config('learning_tools.dictionary.enabled', true)) {
            return ['status' => 'disabled', 'word' => $word];
        }

        $cacheKey = 'nacs.learning.dictionary.'.sha1($word);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $endpoint = rtrim((string) config('learning_tools.dictionary.endpoint'), '/');
        $timeout = (int) config('learning_tools.dictionary.timeout_seconds', 6);

        try {
            $response = Http::acceptJson()
                ->timeout($timeout)
                ->get($endpoint.'/'.rawurlencode($word));
        } catch (\Throwable) {
            return ['status' => 'unavailable', 'word' => $word];
        }

        if ($response->status() === 404) {
            return ['status' => 'not_found', 'word' => $word];
        }

        if (! $response->successful()) {
            return ['status' => 'unavailable', 'word' => $word];
        }

        $entries = $response->json();

        if (! is_array($entries) || ! isset($entries[0]) || ! is_array($entries[0])) {
            return ['status' => 'unavailable', 'word' => $word];
        }

        $entry = $entries[0];
        $phonetic = trim((string) ($entry['phonetic'] ?? ''));

        if ($phonetic === '') {
            foreach (($entry['phonetics'] ?? []) as $candidate) {
                $candidateText = trim((string) ($candidate['text'] ?? ''));

                if ($candidateText !== '') {
                    $phonetic = $candidateText;
                    break;
                }
            }
        }

        $meanings = [];

        foreach (array_slice((array) ($entry['meanings'] ?? []), 0, 8) as $meaning) {
            if (! is_array($meaning)) {
                continue;
            }

            $definitions = [];

            foreach (array_slice((array) ($meaning['definitions'] ?? []), 0, 4) as $definition) {
                if (! is_array($definition) || blank($definition['definition'] ?? null)) {
                    continue;
                }

                $definitions[] = [
                    'definition' => trim((string) $definition['definition']),
                    'example' => trim((string) ($definition['example'] ?? '')),
                    'synonyms' => array_values(array_slice(array_filter(array_map('strval', (array) ($definition['synonyms'] ?? []))), 0, 8)),
                    'antonyms' => array_values(array_slice(array_filter(array_map('strval', (array) ($definition['antonyms'] ?? []))), 0, 8)),
                ];
            }

            if ($definitions !== []) {
                $meanings[] = [
                    'part_of_speech' => trim((string) ($meaning['partOfSpeech'] ?? '')),
                    'definitions' => $definitions,
                ];
            }
        }

        $result = [
            'status' => $meanings === [] ? 'not_found' : 'ok',
            'word' => trim((string) ($entry['word'] ?? $word)),
            'phonetic' => $phonetic,
            'meanings' => $meanings,
        ];

        if ($result['status'] === 'ok') {
            Cache::put(
                $cacheKey,
                $result,
                now()->addSeconds((int) config('learning_tools.dictionary.cache_seconds', 86400))
            );
        }

        return $result;
    }
}
