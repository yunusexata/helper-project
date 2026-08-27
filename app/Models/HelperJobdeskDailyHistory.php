<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

/**
 * @property int $id
 * @property int|null $employee_whitelists_id
 * @property string|null $employee_whitelists_name
 * @property int $subject_id
 * @property string $subject_type
 * @property Carbon|null $start_at
 * @property Carbon|null $finish_at
 * @property string|null $note
 * @property float|null $amount
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'employee_whitelists_id',
    'employee_whitelists_name',
    'subject_id',
    'subject_type',
    'start_at',
    'finish_at',
    'note',
    'amount',
])]
class HelperJobdeskDailyHistory extends Model
{
    use HasFactory, HasTrackHistory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'finish_at' => 'datetime',
            'amount' => 'double',
        ];
    }

    /**
     * Get the user who created this history entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this history entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this history entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the associated employee whitelist entry.
     *
     * @return BelongsTo<EmployeeWhitelist, $this>
     */
    public function employeeWhitelist(): BelongsTo
    {
        return $this->belongsTo(EmployeeWhitelist::class, 'employee_whitelists_id');
    }

    /**
     * Get the polymorphic subject model (e.g. Routine, Request).
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all attachments associated with this daily history record.
     *
     * @return HasMany<HelperJobdeskDailyHistoryAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(HelperJobdeskDailyHistoryAttachment::class, 'helper_jobdesk_daily_histories');
    }
}
