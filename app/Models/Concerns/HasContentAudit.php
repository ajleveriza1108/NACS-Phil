<?php

namespace App\Models\Concerns;

use App\Support\AuditRecorder;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasContentAudit
{
    public static function bootHasContentAudit(): void
    {
        static::created(function ($model): void {
            AuditRecorder::record($model, 'created');
        });

        static::updated(function ($model): void {
            $fields = AuditRecorder::changedFields($model);

            if ($fields !== []) {
                AuditRecorder::record($model, 'updated', ['fields' => $fields]);
            }
        });

        static::deleted(function ($model): void {
            $forceDeleting = method_exists($model, 'isForceDeleting')
                ? $model->isForceDeleting()
                : false;

            if (! $forceDeleting) {
                AuditRecorder::record($model, 'trashed');
            }
        });

        $usesSoftDeletes = in_array(
            SoftDeletes::class,
            class_uses_recursive(static::class),
            true
        );

        if ($usesSoftDeletes) {
            static::restored(function ($model): void {
                AuditRecorder::record($model, 'restored');
            });

            static::forceDeleted(function ($model): void {
                AuditRecorder::record($model, 'permanently_deleted');
            });
        }
    }
}