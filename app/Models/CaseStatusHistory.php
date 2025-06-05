<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'from_status',
        'to_status',
        'note',
        'changed_by'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the case that this history belongs to
     */
    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    /**
     * Get the user who made the status change
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get formatted status change description
     */
    public function getDescriptionAttribute(): string
    {
        return "Status changed from {$this->from_status} to {$this->to_status}";
    }

    /**
     * Scope to get history for a specific case
     */
    public function scopeForCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * Scope to get recent status changes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}