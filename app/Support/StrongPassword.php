<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StrongPassword
{
    public static function rules(array $personalValues = [], string $presence = 'required'): array
    {
        $tokens = self::personalTokens($personalValues);

        return [
            $presence,
            'string',
            'confirmed',
            'max:128',
            Password::min(12)->letters()->mixedCase()->numbers()->symbols(),
            function (string $attribute, mixed $value, \Closure $fail) use ($tokens): void {
                $password = Str::lower((string) $value);

                foreach ($tokens as $token) {
                    if (Str::contains($password, $token)) {
                        $fail('The password must not contain your name, email, or student number.');
                        return;
                    }
                }
            },
        ];
    }

    private static function personalTokens(array $values): array
    {
        $tokens = [];

        foreach ($values as $value) {
            $value = Str::lower(trim((string) $value));

            if ($value === '') {
                continue;
            }

            if (str_contains($value, '@')) {
                $local = Str::before($value, '@');

                if (mb_strlen($local) >= 4) {
                    $tokens[] = $local;
                }
            }

            foreach (preg_split('/[^\pL\pN]+/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                if (mb_strlen($part) >= 4) {
                    $tokens[] = $part;
                }
            }
        }

        return array_values(array_unique($tokens));
    }
}
