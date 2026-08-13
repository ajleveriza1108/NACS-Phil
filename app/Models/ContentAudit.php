<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentAudit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'action',
        'auditable_type',
        'auditable_id',
        'label',
        'details',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}