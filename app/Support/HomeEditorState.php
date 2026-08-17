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
    private const MAX_REVISIONS = 20;

    public static function hideableFields(): array
    {
        return array_keys(VisualEditorSchema::home());
    }

    public static function hiddenFields(): array
    {
        $stored = SiteContent::valuesFor(self::STATE_PAGE, [self::HIDDEN_KEY => '[]']);
        $decoded = json_decode((string) ($stored[self::HIDDEN_KEY] ?? '[]'), true);
        if (! is_array($decoded)) return [];
        $allowed = array_fill_keys(self::hideableFields(), true);
        $safe = array_values(array_unique(array_filter(array_map('strval', $decoded), fn ($v) => isset($allowed[$v]))));
        sort($safe);
        return $safe;
    }

    public static function setHiddenFields(array $fields): void
    {
        $allowed = array_fill_keys(self::hideableFields(), true);
        $safe = array_values(array_unique(array_filter(array_map('strval', $fields), fn ($v) => isset($allowed[$v]))));
        sort($safe);
        SiteContent::storeValues(self::STATE_PAGE, [self::HIDDEN_KEY => json_encode($safe, JSON_UNESCAPED_SLASHES)]);
    }

    public static function revisions(int $limit = self::MAX_REVISIONS): array
    {
        return SiteContent::query()->where('page', self::REVISION_PAGE)->latest('id')
            ->limit(max(1, min(self::MAX_REVISIONS, $limit)))->get()
            ->map(function (SiteContent $row): array {
                $data = json_decode((string) $row->value, true);
                return [
                    'key' => (string) $row->key,
                    'reason' => is_array($data) ? (string) ($data['reason'] ?? 'publish') : 'publish',
                    'actor' => is_array($data) ? (string) ($data['actor'] ?? 'Unknown editor') : 'Unknown editor',
                    'saved_at' => is_array($data) ? (string) ($data['saved_at'] ?? '') : '',
                    'content' => is_array($data) && is_array($data['content'] ?? null) ? $data['content'] : [],
                    'hidden' => is_array($data) && is_array($data['hidden'] ?? null) ? $data['hidden'] : [],
                ];
            })->all();
    }

    public static function recordRevision(?User $actor, string $reason = 'publish'): string
    {
        $key = now()->format('YmdHis').'-'.Str::lower(Str::random(10));
        SiteContent::query()->create([
            'page' => self::REVISION_PAGE,
            'key' => $key,
            'value' => json_encode([
                'version' => 1,
                'reason' => $reason,
                'actor' => $actor?->name ?? 'System',
                'actor_id' => $actor?->id,
                'saved_at' => now()->toIso8601String(),
                'content' => SiteContent::valuesFor('home', HomeContent::defaults()),
                'hidden' => self::hiddenFields(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        $expired = SiteContent::query()->where('page', self::REVISION_PAGE)->latest('id')->skip(self::MAX_REVISIONS)->take(500)->pluck('id');
        if ($expired->isNotEmpty()) SiteContent::query()->whereIn('id', $expired)->delete();
        return $key;
    }

    public static function restoreRevision(string $key, ?User $actor): bool
    {
        $row = SiteContent::query()->where('page', self::REVISION_PAGE)->where('key', $key)->first();
        if (! $row) return false;
        $data = json_decode((string) $row->value, true);
        if (! is_array($data) || ! is_array($data['content'] ?? null)) return false;
        self::recordRevision($actor, 'before_revision_restore');
        $allowed = array_fill_keys(array_keys(HomeContent::defaults()), true);
        $content = array_intersect_key($data['content'], $allowed);
        SiteContent::storeValues('home', array_replace(HomeContent::defaults(), $content));
        self::setHiddenFields(is_array($data['hidden'] ?? null) ? $data['hidden'] : []);
        self::recordRevision($actor, 'revision_restored');
        return true;
    }

    public static function resetOriginal(?User $actor): void
    {
        self::recordRevision($actor, 'before_original_reset');
        SiteContent::storeValues('home', HomeContent::defaults());
        self::setHiddenFields([]);
        self::recordRevision($actor, 'original_reset');
    }
}
