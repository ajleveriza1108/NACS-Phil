<?php

namespace App\Models;

use App\Models\Concerns\HasContentAudit;
use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    use HasContentAudit;

    protected $fillable = [
        'page',
        'key',
        'value',
    ];

    public static function valuesFor(string $page, array $defaults = []): array
    {
        $stored = static::query()
            ->where('page', $page)
            ->pluck('value', 'key')
            ->all();

        return array_replace($defaults, $stored);
    }

    public static function storeValues(string $page, array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                ['page' => $page, 'key' => $key],
                ['value' => is_string($value) ? trim($value) : $value]
            );
        }
    }
}