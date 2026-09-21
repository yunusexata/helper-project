<?php

namespace App\Exports\Sheets;

use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskRequest;
use App\Models\HelperJobdeskRoutine;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyJobdeskPerDateSheet implements Export, FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * Create a new sheet instance for a specific date.
     */
    public function __construct(
        public int $petugasId,
        public string $date,
    ) {}

    /**
     * Get the title for this sheet tab.
     * Excel sheet title maximum length is 31 characters.
     */
    public function title(): string
    {
        $formatted = Carbon::parse($this->date)->locale('id')->isoFormat('dddd, DD-MM-YYYY');

        return mb_substr($formatted, 0, 31);
    }

    /**
     * Define the column headings.
     */
    public function headings(): array
    {
        return [
            'Tipe / Kategori',
            'Nama Aktivitas',
            'Status',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi',
            'Catatan Laporan',
        ];
    }

    /**
     * Fetch and transform the rows for the given date.
     */
    public function array(): array
    {
        $user = User::find($this->petugasId);
        if (! $user) {
            return [];
        }

        $whitelist = EmployeeWhitelist::where('email', $user->email)->first();
        $whitelistId = $whitelist?->id ?? 0;

        $dayName = strtolower(Carbon::parse($this->date)->locale('id')->dayName);
        $driver = DB::connection()->getDriverName();
        $groupConcat = $driver === 'sqlite'
            ? "GROUP_CONCAT(r.activity_name, '|||')"
            : "STRING_AGG(r.activity_name, '|||')";

        $orderCol = DB::connection()->getDriverName() === 'mysql' ? 'r.`order`' : 'r."order"';

        // 1. Grouped Routines for the selected date
        $routinesQuery = DB::table('helper_jobdesk_routines as r')
            ->leftJoin('helper_jobdesk_daily_histories as h', function ($join) use ($whitelistId) {
                $join->on('h.subject_id', '=', 'r.id')
                    ->where('h.subject_type', '=', HelperJobdeskRoutine::class)
                    ->where('h.employee_whitelists_id', '=', $whitelistId)
                    ->whereDate('h.created_at', '=', $this->date)
                    ->whereNull('h.deleted_at');
            })
            ->where('r.day', '=', $dayName)
            ->whereNull('r.deleted_at')
            ->groupBy('r.task_group')
            ->select([
                DB::raw("'Rutinitas' as task_type"),
                'r.task_group as category',
                DB::raw("{$groupConcat} as activity_name"),
                DB::raw('MAX(h.id) as history_id'),
                DB::raw('MAX(h.start_at) as start_at'),
                DB::raw('MAX(h.finish_at) as finish_at'),
                DB::raw('MAX(h.note) as logged_note'),
                DB::raw("MIN({$orderCol}) as sort_order"),
            ]);

        // 2. Ad-hoc Requests logged for that helper on that date
        $requestsQuery = DB::table('helper_jobdesk_requests as req')
            ->leftJoin('helper_jobdesk_daily_histories as h', function ($join) use ($whitelistId) {
                $join->on('h.subject_id', '=', 'req.id')
                    ->where('h.subject_type', '=', HelperJobdeskRequest::class)
                    ->where('h.employee_whitelists_id', '=', $whitelistId)
                    ->whereDate('h.created_at', '=', $this->date)
                    ->whereNull('h.deleted_at');
            })
            ->where('req.employee_whitelists_id', '=', $whitelistId)
            ->whereDate('req.created_at', '=', $this->date)
            ->whereNull('req.deleted_at')
            ->select([
                DB::raw("'Request' as task_type"),
                DB::raw("'Request' as category"),
                'req.activity_name as activity_name',
                'h.id as history_id',
                'h.start_at as start_at',
                'h.finish_at as finish_at',
                'h.note as logged_note',
                DB::raw('9999 as sort_order'),
            ]);

        $unionQuery = $routinesQuery->unionAll($requestsQuery);
        $records = DB::query()
            ->fromSub($unionQuery, 'combined_jobdesks')
            ->orderByRaw('CASE WHEN start_at IS NULL THEN 1 ELSE 0 END, start_at asc, sort_order ASC')
            ->get();

        $rows = [];

        foreach ($records as $item) {
            // Tipe / Kategori
            $type = $item->task_type === 'Request' ? 'Request' : 'Rutinitas';

            // Nama Aktivitas
            if ($item->task_type === 'Rutinitas') {
                $rawActivities = explode('|||', $item->activity_name ?? '');
                $activityList = [];
                foreach ($rawActivities as $act) {
                    $trimmed = trim($act);
                    if ($trimmed !== '') {
                        $activityList[] = '• '.$trimmed;
                    }
                }
                $activityText = "Grup {$item->category} (".count($activityList)." Aktivitas):\n".implode("\n", $activityList);
            } else {
                $activityText = $item->activity_name ?? '-';
            }

            // Status
            if (! empty($item->finish_at)) {
                $status = 'Selesai';
            } elseif (! empty($item->start_at)) {
                $status = 'Berjalan';
            } else {
                $status = 'Belum Selesai';
            }

            // Jam Mulai
            $jamMulai = ! empty($item->start_at)
                ? Carbon::parse($item->start_at)->setTimezone('Asia/Jakarta')->format('H:i')
                : '-';

            // Jam Selesai
            if (! empty($item->finish_at)) {
                $jamSelesai = Carbon::parse($item->finish_at)->setTimezone('Asia/Jakarta')->format('H:i');
            } elseif (! empty($item->start_at)) {
                $jamSelesai = 'Berjalan';
            } else {
                $jamSelesai = '-';
            }

            // Durasi
            if (empty($item->start_at) || empty($item->finish_at)) {
                $durasi = '-';
            } else {
                $start = Carbon::parse($item->start_at)->setTimezone('Asia/Jakarta');
                $finish = Carbon::parse($item->finish_at)->setTimezone('Asia/Jakarta');

                $diffInSeconds = max(0, $start->diffInSeconds($finish));
                $hours = floor($diffInSeconds / 3600);
                $minutes = floor(($diffInSeconds % 3600) / 60);

                if ($hours > 0) {
                    $durasi = "{$hours} jam {$minutes} mnt";
                } elseif ($minutes > 0) {
                    $durasi = "{$minutes} menit";
                } else {
                    $seconds = $diffInSeconds % 60;
                    $durasi = "{$seconds} dtk";
                }
            }

            // Catatan Laporan
            $catatan = ! empty($item->logged_note) ? $item->logged_note : '-';

            $rows[] = [
                $type,
                $activityText,
                $status,
                $jamMulai,
                $jamSelesai,
                $durasi,
                $catatan,
            ];
        }

        return $rows;
    }

    /**
     * Style the spreadsheet sheet.
     */
    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'G';

        // Header Row Styling (Row 1)
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E40AF'], // Tailwind Blue-800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Data Rows Styling
        if ($highestRow > 1) {
            // Apply all borders and vertical alignment
            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFE2E8F0'], // Tailwind Slate-200
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);

            // Alignment per column
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Wrap text for Activity (Column B) and Notes (Column G)
            $sheet->getStyle("B2:B{$highestRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("G2:G{$highestRow}")->getAlignment()->setWrapText(true);
        }

        return [];
    }
}
