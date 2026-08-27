<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

/**
 * @property int $id
 * @property string $day
 * @property string $activity_name
 * @property string|null $note
 * @property int $employee_whitelists_id
 * @property string $employee_whitelists_name
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'day',
    'activity_name',
    'note',
    'employee_whitelists_id',
    'employee_whitelists_name',
])]
class HelperJobdeskRequest extends Model
{
    use HasFactory, HasTrackHistory, SoftDeletes;

    /**
     * Get the user who created this request.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this request.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this request.
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
     * Get all daily histories logged for this request.
     *
     * @return MorphMany<HelperJobdeskDailyHistory, $this>
     */
    public function dailyHistories(): MorphMany
    {
        return $this->morphMany(HelperJobdeskDailyHistory::class, 'subject');
    }
}
