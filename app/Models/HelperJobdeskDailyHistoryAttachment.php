<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

/**
 * @property int $id
 * @property int|null $helper_jobdesk_daily_histories
 * @property string $disk
 * @property string $path
 * @property string|null $note
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'helper_jobdesk_daily_histories',
    'disk',
    'path',
    'note',
])]
class HelperJobdeskDailyHistoryAttachment extends Model
{
    use HasFactory, HasTrackHistory, SoftDeletes;

    /**
     * Get the user who created this attachment record.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this attachment record.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this attachment record.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the daily history record associated with this attachment.
     *
     * @return BelongsTo<HelperJobdeskDailyHistory, $this>
     */
    public function dailyHistory(): BelongsTo
    {
        return $this->belongsTo(HelperJobdeskDailyHistory::class, 'helper_jobdesk_daily_histories');
    }
}
