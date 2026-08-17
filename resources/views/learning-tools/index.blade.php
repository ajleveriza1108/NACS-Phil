@extends('layouts.site-current')

@section('title', 'Dictionary & Grammar')
@section('meta_description', 'Free online English dictionary and grammar learning tools for the NACS-Phil school community.')

@section('content')
<section class="lt-hero">
    <div class="nacs11-shell">
        <p class="lt-kicker">Learning Tools</p>
        <h1>Dictionary &amp; Grammar</h1>
        <p>Look up English words and manually check short passages for spelling and grammar suggestions.</p>
    </div>
</section>

<section class="lt-section">
    <div class="nacs11-shell lt-grid">
        <article class="lt-card">
            <div class="lt-card__head">
                <span class="lt-icon" aria-hidden="true">Aa</span>
                <div>
                    <p class="lt-kicker">Dictionary</p>
                    <h2>Look up a word</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('learning-tools.dictionary') }}" class="lt-form">
                @csrf
                <label for="dictionary-word">English word</label>
                <div class="lt-inline">
                    <input id="dictionary-word" name="word" value="{{ old('word', $dictionaryWord) }}" maxlength="64" required autocomplete="off" spellcheck="false" placeholder="e.g. perseverance">
                    <button type="submit">Define</button>
                </div>
                @error('word')<p class="lt-error">{{ $message }}</p>@enderror
            </form>

            @if(is_array($dictionaryResult))
                <div class="lt-result" aria-live="polite">
                    @if(($dictionaryResult['status'] ?? '') === 'ok')
                        <div class="lt-word">
                            <h3>{{ $dictionaryResult['word'] }}</h3>
                            @if(filled($dictionaryResult['phonetic'] ?? null))
                                <span>{{ $dictionaryResult['phonetic'] }}</span>
                            @endif
                        </div>

                        @foreach($dictionaryResult['meanings'] as $meaning)
                            <section class="lt-meaning">
                                @if(filled($meaning['part_of_speech']))<h4>{{ ucfirst($meaning['part_of_speech']) }}</h4>@endif
                                <ol>
                                    @foreach($meaning['definitions'] as $definition)
                                        <li>
                                            <p>{{ $definition['definition'] }}</p>
                                            @if(filled($definition['example']))<p class="lt-example">Example: “{{ $definition['example'] }}”</p>@endif
                                            @if($definition['synonyms'] !== [])<p class="lt-meta"><strong>Synonyms:</strong> {{ implode(', ', $definition['synonyms']) }}</p>@endif
                                            @if($definition['antonyms'] !== [])<p class="lt-meta"><strong>Antonyms:</strong> {{ implode(', ', $definition['antonyms']) }}</p>@endif
                                        </li>
                                    @endforeach
                                </ol>
                            </section>
                        @endforeach
                    @elseif(($dictionaryResult['status'] ?? '') === 'not_found')
                        <p>No dictionary entry was found for “{{ $dictionaryResult['word'] ?? $dictionaryWord }}”. Check the spelling and try again.</p>
                    @elseif(($dictionaryResult['status'] ?? '') === 'disabled')
                        <p>The online dictionary is temporarily disabled by the school administrator.</p>
                    @else
                        <p>The dictionary service is temporarily unavailable. Please try again later.</p>
                    @endif
                </div>
            @endif

            <p class="lt-provider">Definitions provided by <a href="https://dictionaryapi.dev/" target="_blank" rel="noopener">Free Dictionary API</a>.</p>
        </article>

        <article class="lt-card">
            <div class="lt-card__head">
                <span class="lt-icon" aria-hidden="true">✓</span>
                <div>
                    <p class="lt-kicker">Grammar</p>
                    <h2>Check a short passage</h2>
                </div>
            </div>

            <div class="lt-privacy" role="note">
                <strong>Privacy reminder:</strong> Do not paste grades, student records, admissions information, passwords, or other confidential/personal data. Text is sent to the configured grammar service only when you press <em>Check Grammar</em>; NACS-Phil does not store the submitted passage.
            </div>

            <form method="POST" action="{{ route('learning-tools.grammar') }}" class="lt-form">
                @csrf
                <label for="grammar-language">English variant</label>
                <select id="grammar-language" name="grammar_language">
                    @foreach($grammarLanguages as $code => $label)
                        <option value="{{ $code }}" @selected(old('grammar_language', $grammarLanguage) === $code)>{{ $label }}</option>
                    @endforeach
                </select>

                <label for="grammar-text">Text to check</label>
                <textarea id="grammar-text" name="grammar_text" maxlength="{{ $grammarMaxChars }}" required rows="9" placeholder="Type or paste a short, non-confidential passage...">{{ old('grammar_text', $grammarText) }}</textarea>
                <div class="lt-form__foot">
                    <small>Maximum {{ number_format($grammarMaxChars) }} characters.</small>
                    <button type="submit">Check Grammar</button>
                </div>
                @error('grammar_text')<p class="lt-error">{{ $message }}</p>@enderror
                @error('grammar_language')<p class="lt-error">{{ $message }}</p>@enderror
            </form>

            @if(is_array($grammarResult))
                <div class="lt-result" aria-live="polite">
                    @if(($grammarResult['status'] ?? '') === 'ok')
                        @if(($grammarResult['matches'] ?? []) === [])
                            <p><strong>No suggestions found.</strong> Automated grammar tools can miss context, so review important writing carefully.</p>
                        @else
                            <h3>{{ count($grammarResult['matches']) }} suggestion{{ count($grammarResult['matches']) === 1 ? '' : 's' }}</h3>
                            <ol class="lt-suggestions">
                                @foreach($grammarResult['matches'] as $match)
                                    <li>
                                        <strong>{{ $match['short_message'] ?: 'Review suggestion' }}</strong>
                                        <p>{{ $match['message'] }}</p>
                                        @if(filled($match['context']))<p class="lt-context">{{ $match['context'] }}</p>@endif
                                        @if($match['replacements'] !== [])<p class="lt-meta"><strong>Suggestions:</strong> {{ implode(', ', $match['replacements']) }}</p>@endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    @elseif(($grammarResult['status'] ?? '') === 'disabled')
                        <p>The grammar checker is temporarily disabled by the school administrator.</p>
                    @else
                        <p>The grammar service is temporarily unavailable. Your text was not stored by NACS-Phil.</p>
                    @endif
                </div>
            @endif

            <p class="lt-provider">Grammar suggestions powered by <a href="https://languagetool.org/" target="_blank" rel="noopener">LanguageTool</a>. Suggestions are advisory and should be reviewed by the writer or teacher.</p>
        </article>
    </div>
</section>
@endsection
