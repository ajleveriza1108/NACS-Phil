<?php

namespace App\Support;

use App\Models\ContentAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuditRecorder
{
    public static function record(Model $model, string $action, array $details = []): void
    {
        if ($model instanceof ContentAudit) {
            return;
        }

        $user = Auth::user();

        ContentAudit::create([
            'actor_id' => $user?->id,
            'actor_name' => $user?->name,
            'actor_role' => $user !== null && method_exists($user, 'staffRoleLabel') ? $user->staffRoleLabel() : null,
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'label' => self::label($model),
            'details' => empty($details) ? null : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
        ]);
    }

    private static function label(Model $model): string
    {
        foreach (['title', 'name'] as $attribute) {
            $value = $model->getAttribute($attribute);
            if (is_string($value) && trim($value) !== '') {
                return mb_substr(trim($value), 0, 250);
            }
        }

        if ($model->getAttribute('page') && $model->getAttribute('key')) {
            return mb_substr(
                (string) $model->getAttribute('page').' / '.(string) $model->getAttribute('key'),
                0,
                250
            );
        }

        return class_basename($model).' #'.($model->getKey() ?? 'new');
    }

    public static function changedFields(Model $model): array
    {
        return array_values(array_filter(
            array_keys($model->getChanges()),
            fn (string $field): bool => ! in_array($field, ['updated_at', 'deleted_at'], true)
        ));
    }
}