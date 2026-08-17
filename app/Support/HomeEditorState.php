<?php
namespace App\Support;

use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Support\Str;

final class HomeEditorState
{
    private const STATE_PAGE = '__editor_home';
    private const REVISION_PAGE = '__editor_revision_home';
    private const HIDDEN_KEY = 'hidden_fields';
    private const STYLE_KEY = 'style_overrides';
    private const MAX_REVISIONS = 20;
    private const STYLE_SCOPES = ['base', 'tablet', 'phone'];

    private const NUMERIC_STYLE_LIMITS = [
        'font_size' => [10.0, 96.0],
        'line_height' => [0.8, 2.2],
        'letter_spacing' => [-2.0, 6.0],
        'max_width' => [0.0, 1600.0],
        'padding_x' => [0.0, 96.0],
        'padding_y' => [0.0, 96.0],
        'min_height' => [0.0, 800.0],
    ];

    private const ENUM_STYLE_VALUES = [
        'font_weight' => ['400', '500', '600', '700', '800', '900'],
        'text_align' => ['left', 'center', 'right'],
        'flow' => ['normal', 'nowrap', 'balance'],
    ];

    public static function hideableFields(): array
    {
        return array_keys(VisualEditorSchema::home());
    }

    public static function hiddenFields(): array
    {
        $stored = SiteContent::valuesFor(self::STATE_PAGE, [self::HIDDEN_KEY => '[]']);
        $decoded = json_decode((string) ($stored[self::HIDDEN_KEY] ?? '[]'), true);

        if (! is_array($decoded)) {
            return [];
        }

        $allowed = array_fill_keys(self::hideableFields(), true);
        $safe = array_values(array_unique(array_filter(
            array_map('strval', $decoded),
            fn ($value) => isset($allowed[$value])
        )));

        sort($safe);

        return $safe;
    }

    public static function setHiddenFields(array $fields): void
    {
        $allowed = array_fill_keys(self::hideableFields(), true);
        $safe = array_values(array_unique(array_filter(
            array_map('strval', $fields),
            fn ($value) => isset($allowed[$value])
        )));

        sort($safe);

        SiteContent::storeValues(self::STATE_PAGE, [
            self::HIDDEN_KEY => json_encode($safe, JSON_UNESCAPED_SLASHES),
        ]);
    }

    public static function styleOverrides(): array
    {
        $stored = SiteContent::valuesFor(self::STATE_PAGE, [self::STYLE_KEY => '{}']);
        $decoded = json_decode((string) ($stored[self::STYLE_KEY] ?? '{}'), true);

        return self::sanitizeStyleOverrides(is_array($decoded) ? $decoded : []);
    }

    public static function setStyleOverrides(array $styles): void
    {
        SiteContent::storeValues(self::STATE_PAGE, [
            self::STYLE_KEY => json_encode(
                self::sanitizeStyleOverrides($styles),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
        ]);
    }

    public static function styleCss(): string
    {
        $sections = [
            'base' => [],
            'tablet' => [],
            'phone' => [],
        ];

        foreach (self::styleOverrides() as $field => $scopes) {
            $safeField = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $field);

            if ($safeField === '') {
                continue;
            }

            $selector = '[data-visual-field="'.$safeField.'"]';

            foreach (self::STYLE_SCOPES as $scope) {
                $rules = $scopes[$scope] ?? null;

                if (! is_array($rules) || $rules === []) {
                    continue;
                }

                $declarations = self::styleDeclarations($rules);

                if ($declarations !== '') {
                    $sections[$scope][] = $selector.'{'.$declarations.'}';
                }
            }
        }

        $css = [];

        if ($sections['base'] !== []) {
            $css[] = implode('', $sections['base']);
        }

        if ($sections['tablet'] !== []) {
            $css[] = '@media(max-width:900px){'.implode('', $sections['tablet']).'}';
        }

        if ($sections['phone'] !== []) {
            $css[] = '@media(max-width:560px){'.implode('', $sections['phone']).'}';
        }

        return implode("\n", $css);
    }

    public static function revisions(int $limit = self::MAX_REVISIONS): array
    {
        return SiteContent::query()
            ->where('page', self::REVISION_PAGE)
            ->latest('id')
            ->limit(max(1, min(self::MAX_REVISIONS, $limit)))
            ->get()
            ->map(function (SiteContent $row): array {
                $data = json_decode((string) $row->value, true);

                return [
                    'key' => (string) $row->key,
                    'reason' => is_array($data) ? (string) ($data['reason'] ?? 'publish') : 'publish',
                    'actor' => is_array($data) ? (string) ($data['actor'] ?? 'Unknown editor') : 'Unknown editor',
                    'saved_at' => is_array($data) ? (string) ($data['saved_at'] ?? '') : '',
                    'content' => is_array($data) && is_array($data['content'] ?? null) ? $data['content'] : [],
                    'hidden' => is_array($data) && is_array($data['hidden'] ?? null) ? $data['hidden'] : [],
                    'styles' => is_array($data) && is_array($data['styles'] ?? null) ? self::sanitizeStyleOverrides($data['styles']) : [],
                ];
            })
            ->all();
    }

    public static function recordRevision(?User $actor, string $reason = 'publish'): string
    {
        $key = now()->format('YmdHis').'-'.Str::lower(Str::random(10));

        SiteContent::query()->create([
            'page' => self::REVISION_PAGE,
            'key' => $key,
            'value' => json_encode([
                'version' => 2,
                'reason' => $reason,
                'actor' => $actor?->name ?? 'System',
                'actor_id' => $actor?->id,
                'saved_at' => now()->toIso8601String(),
                'content' => SiteContent::valuesFor('home', HomeContent::defaults()),
                'hidden' => self::hiddenFields(),
                'styles' => self::styleOverrides(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $expired = SiteContent::query()
            ->where('page', self::REVISION_PAGE)
            ->latest('id')
            ->skip(self::MAX_REVISIONS)
            ->take(500)
            ->pluck('id');

        if ($expired->isNotEmpty()) {
            SiteContent::query()->whereIn('id', $expired)->delete();
        }

        return $key;
    }

    public static function restoreRevision(string $key, ?User $actor): bool
    {
        $row = SiteContent::query()
            ->where('page', self::REVISION_PAGE)
            ->where('key', $key)
            ->first();

        if (! $row) {
            return false;
        }

        $data = json_decode((string) $row->value, true);

        if (! is_array($data) || ! is_array($data['content'] ?? null)) {
            return false;
        }

        self::recordRevision($actor, 'before_revision_restore');

        $allowed = array_fill_keys(array_keys(HomeContent::defaults()), true);
        $content = array_intersect_key($data['content'], $allowed);

        SiteContent::storeValues('home', array_replace(HomeContent::defaults(), $content));
        self::setHiddenFields(is_array($data['hidden'] ?? null) ? $data['hidden'] : []);
        self::setStyleOverrides(is_array($data['styles'] ?? null) ? $data['styles'] : []);

        self::recordRevision($actor, 'revision_restored');

        return true;
    }

    public static function resetOriginal(?User $actor): void
    {
        self::recordRevision($actor, 'before_original_reset');
        SiteContent::storeValues('home', HomeContent::defaults());
        self::setHiddenFields([]);
        self::setStyleOverrides([]);
        self::recordRevision($actor, 'original_reset');
    }

    private static function sanitizeStyleOverrides(array $styles): array
    {
        $allowedFields = array_fill_keys(self::hideableFields(), true);
        $safe = [];

        foreach ($styles as $field => $scopes) {
            $field = (string) $field;

            if (! isset($allowedFields[$field]) || ! is_array($scopes)) {
                continue;
            }

            $safeScopes = [];

            foreach (self::STYLE_SCOPES as $scope) {
                $values = $scopes[$scope] ?? null;

                if (! is_array($values)) {
                    continue;
                }

                $safeValues = [];

                foreach (self::NUMERIC_STYLE_LIMITS as $property => [$minimum, $maximum]) {
                    if (! array_key_exists($property, $values) || ! is_numeric($values[$property])) {
                        continue;
                    }

                    $number = (float) $values[$property];
                    $number = max($minimum, min($maximum, $number));
                    $safeValues[$property] = round($number, 2);
                }

                foreach (self::ENUM_STYLE_VALUES as $property => $allowedValues) {
                    if (! array_key_exists($property, $values)) {
                        continue;
                    }

                    $value = (string) $values[$property];

                    if (in_array($value, $allowedValues, true)) {
                        $safeValues[$property] = $value;
                    }
                }

                if ($safeValues !== []) {
                    $safeScopes[$scope] = $safeValues;
                }
            }

            if ($safeScopes !== []) {
                $safe[$field] = $safeScopes;
            }
        }

        ksort($safe);

        return $safe;
    }

    private static function styleDeclarations(array $rules): string
    {
        $declarations = [];
        $frameAdjusted = false;

        if (isset($rules['font_size'])) {
            $declarations[] = 'font-size:'.self::cssNumber((float) $rules['font_size']).'px!important';
        }

        if (isset($rules['line_height'])) {
            $declarations[] = 'line-height:'.self::cssNumber((float) $rules['line_height']).'!important';
        }

        if (isset($rules['letter_spacing'])) {
            $declarations[] = 'letter-spacing:'.self::cssNumber((float) $rules['letter_spacing']).'px!important';
        }

        if (isset($rules['max_width']) && (float) $rules['max_width'] > 0) {
            $declarations[] = 'max-width:'.self::cssNumber((float) $rules['max_width']).'px!important';
            $frameAdjusted = true;
        }

        if (isset($rules['padding_x'])) {
            $value = self::cssNumber((float) $rules['padding_x']);
            $declarations[] = 'padding-left:'.$value.'px!important';
            $declarations[] = 'padding-right:'.$value.'px!important';
            $frameAdjusted = true;
        }

        if (isset($rules['padding_y'])) {
            $value = self::cssNumber((float) $rules['padding_y']);
            $declarations[] = 'padding-top:'.$value.'px!important';
            $declarations[] = 'padding-bottom:'.$value.'px!important';
            $frameAdjusted = true;
        }

        if (isset($rules['min_height']) && (float) $rules['min_height'] > 0) {
            $declarations[] = 'min-height:'.self::cssNumber((float) $rules['min_height']).'px!important';
            $frameAdjusted = true;
        }

        if (isset($rules['font_weight'])) {
            $declarations[] = 'font-weight:'.$rules['font_weight'].'!important';
        }

        if (isset($rules['text_align'])) {
            $declarations[] = 'text-align:'.$rules['text_align'].'!important';
        }

        if (isset($rules['flow'])) {
            if ($rules['flow'] === 'nowrap') {
                $declarations[] = 'white-space:nowrap!important';
                $declarations[] = 'text-wrap:nowrap!important';
                $declarations[] = 'overflow-wrap:normal!important';
            } elseif ($rules['flow'] === 'balance') {
                $declarations[] = 'white-space:normal!important';
                $declarations[] = 'text-wrap:balance!important';
            } else {
                $declarations[] = 'white-space:normal!important';
                $declarations[] = 'text-wrap:wrap!important';
                $declarations[] = 'overflow-wrap:anywhere!important';
            }
        }

        if ($frameAdjusted) {
            $declarations[] = 'display:inline-block!important';
            $declarations[] = 'box-sizing:border-box!important';
        }

        return $declarations === [] ? '' : implode(';', $declarations).';';
    }

    private static function cssNumber(float $value): string
    {
        $formatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return $formatted === '-0' ? '0' : $formatted;
    }
}
