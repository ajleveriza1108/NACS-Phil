<?php

namespace App\Http\Controllers;

use App\Support\DictionaryClient;
use App\Support\GrammarClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class LearningToolsController extends Controller
{
    public function index(): Response
    {
        return response()
            ->view('learning-tools.index', $this->viewData())
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function dictionary(Request $request, DictionaryClient $dictionary): Response
    {
        $data = $request->validate([
            'word' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z][A-Za-z\'-]*$/'],
        ]);

        return response()
            ->view('learning-tools.index', $this->viewData([
                'dictionaryWord' => $data['word'],
                'dictionaryResult' => $dictionary->lookup($data['word']),
            ]))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function grammar(Request $request, GrammarClient $grammar): Response
    {
        $maxChars = (int) config('learning_tools.grammar.max_chars', 2000);
        $languages = (array) config('learning_tools.grammar.languages', ['en-US' => 'English (US)']);

        $data = $request->validate([
            'grammar_text' => ['required', 'string', 'max:'.$maxChars],
            'grammar_language' => ['required', Rule::in(array_keys($languages))],
        ]);

        return response()
            ->view('learning-tools.index', $this->viewData([
                'grammarText' => $data['grammar_text'],
                'grammarLanguage' => $data['grammar_language'],
                'grammarResult' => $grammar->check($data['grammar_text'], $data['grammar_language']),
            ]))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    private function viewData(array $extra = []): array
    {
        return array_merge([
            'title' => 'Dictionary & Grammar',
            'assetBundle' => 'learning-tools',
            'dictionaryWord' => '',
            'dictionaryResult' => null,
            'grammarText' => '',
            'grammarLanguage' => 'en-US',
            'grammarResult' => null,
            'grammarMaxChars' => (int) config('learning_tools.grammar.max_chars', 2000),
            'grammarLanguages' => (array) config('learning_tools.grammar.languages', []),
        ], $extra);
    }
}
