<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

/**
 * @property int $id
 * @property string $employee_id
 * @property string $email
 * @property string $name
 * @property string|null $division
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['employee_id', 'email', 'name', 'division'])]
class EmployeeWhitelist extends Model
{
    use HasFactory, HasTrackHistory, SoftDeletes;

    /**
     * Get the user who created this whitelist entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this whitelist entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this whitelist entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get all daily histories linked to this employee.
     *
     * @return HasMany<HelperJobdeskDailyHistory, $this>
     */
    public function dailyHistories(): HasMany
    {
        return $this->hasMany(HelperJobdeskDailyHistory::class, 'employee_whitelists_id');
    }

    /**
     * Get all jobdesk requests linked to this employee.
     *
     * @return HasMany<HelperJobdeskRequest, $this>
     */
    public function requests(): HasMany
    {
        return $this->hasMany(HelperJobdeskRequest::class, 'employee_whitelists_id');
    }
}
