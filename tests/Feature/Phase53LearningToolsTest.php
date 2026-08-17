<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase53LearningToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_tools_page_is_public_and_has_required_attribution_and_privacy_notice(): void
    {
        $this->get(route('learning-tools.index'))
            ->assertOk()
            ->assertSee('Dictionary & Grammar')
            ->assertSee('Free Dictionary API')
            ->assertSee('LanguageTool')
            ->assertSee('Do not paste grades, student records');
    }

    public function test_dictionary_lookup_is_manual_server_side_and_sanitized(): void
    {
        Cache::flush();

        Http::fake([
            'api.dictionaryapi.dev/*' => Http::response([[
                'word' => 'grace',
                'phonetic' => '/ɡreɪs/',
                'meanings' => [[
                    'partOfSpeech' => 'noun',
                    'definitions' => [[
                        'definition' => 'Courteous goodwill.',
                        'example' => 'She accepted with grace.',
                        'synonyms' => ['courtesy'],
                        'antonyms' => [],
                    ]],
                ]],
            ]], 200),
        ]);

        $this->post(route('learning-tools.dictionary'), ['word' => 'grace'])
            ->assertOk()
            ->assertSee('Courteous goodwill.')
            ->assertSee('courtesy');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/entries/en/grace'));
    }

    public function test_grammar_check_is_manual_limited_and_does_not_need_client_side_api_access(): void
    {
        Http::fake([
            'api.languagetool.org/*' => Http::response([
                'language' => ['name' => 'English (US)'],
                'matches' => [[
                    'message' => 'Use “is” with a singular subject.',
                    'shortMessage' => 'Agreement',
                    'offset' => 5,
                    'length' => 3,
                    'context' => ['text' => 'This are a test.'],
                    'replacements' => [['value' => 'is']],
                ]],
            ], 200),
        ]);

        $this->post(route('learning-tools.grammar'), [
            'grammar_text' => 'This are a test.',
            'grammar_language' => 'en-US',
        ])
            ->assertOk()
            ->assertSee('Use “is” with a singular subject.')
            ->assertSee('Agreement');

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === config('learning_tools.grammar.endpoint'));
    }

    public function test_grammar_input_rejects_text_over_the_configured_limit(): void
    {
        config(['learning_tools.grammar.max_chars' => 250]);

        $this->post(route('learning-tools.grammar'), [
            'grammar_text' => str_repeat('a', 251),
            'grammar_language' => 'en-US',
        ])->assertSessionHasErrors('grammar_text');
    }
}
