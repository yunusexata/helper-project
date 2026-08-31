<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

/**
 * @property int $id
 * @property string $day
 * @property string $activity_name
 * @property string|null $note
 * @property int $order
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'day',
    'task_group',
    'activity_name',
    'note',
    'order',
])]
class HelperJobdeskRoutine extends Model
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
            'order' => 'integer',
        ];
    }

    /**
     * Get the user who created this routine.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this routine.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this routine.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get all daily histories logged for this routine.
     *
     * @return MorphMany<HelperJobdeskDailyHistory, $this>
     */
    public function dailyHistories(): MorphMany
    {
        return $this->morphMany(HelperJobdeskDailyHistory::class, 'subject');
    }

    /**
     * Override the TrackHistory insertHistory method to quote columns for PostgreSQL.
     */
    public function insertHistory(): void
    {
        $arrayObj = $this->getAttributes();
        $arrayObj['obj_id'] = $arrayObj['id'];

        unset($arrayObj['id']);
        unset($arrayObj['uuid']);

        $arrayObj['created_at'] = ! empty($arrayObj['created_at']) ? Carbon::parse($arrayObj['created_at'])->format('Y-m-d H:i:s') : null;
        $arrayObj['updated_at'] = ! empty($arrayObj['updated_at']) ? Carbon::parse($arrayObj['updated_at'])->format('Y-m-d H:i:s') : null;
        $arrayObj['deleted_at'] = ! empty($arrayObj['deleted_at']) ? Carbon::parse($arrayObj['deleted_at'])->format('Y-m-d H:i:s') : null;

        $tableName = '_history_'.$this->getTable();

        // Safely double-quote all column names to prevent PostgreSQL syntax errors on reserved keywords like "order"
        $quotedColumns = array_map(fn ($col) => '"'.$col.'"', array_keys($arrayObj));

        $columns = '('.implode(',', $quotedColumns).')';
        $binding = '(:'.implode(',:', array_keys($arrayObj)).')';

        DB::connection($this->connection)->insert(
            "INSERT INTO $tableName $columns VALUES $binding",
            $arrayObj
        );
    }
}
