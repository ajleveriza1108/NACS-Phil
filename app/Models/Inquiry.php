<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    use HasFactory;

    public const STATUSES = [
        'new' => 'New',
        'assigned' => 'Assigned',
        'contacted' => 'Contacted',
        'follow_up' => 'Follow-up',
        'waiting_for_family' => 'Waiting for Family',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    protected $fillable = [
        'guardian_name','email','phone','student_name','level_interested',
        'message','status','admin_notes','privacy_consent_at','ip_hash','user_agent',
        'assigned_to_user_id','follow_up_at','source','interest_level','last_contacted_at',
    ];

    protected $hidden = ['ip_hash','user_agent'];

    protected function casts(): array
    {
        return [
            'privacy_consent_at' => 'datetime',
            'follow_up_at' => 'datetime',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
