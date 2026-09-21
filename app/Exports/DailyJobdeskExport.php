<?php

namespace App\Exports;

use App\Exports\Sheets\DailyJobdeskPerDateSheet;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailyJobdeskExport implements Export, WithMultipleSheets
{
    /**
     * Create a new export instance.
     */
    public function __construct(
        public int $petugasId,
        public string $startDate,
        public string $endDate,
    ) {}

    /**
     * Generate the sheets array for each date in the period.
     *
     * @return array<int, DailyJobdeskPerDateSheet>
     */
    public function sheets(): array
    {
        $sheets = [];
        $period = CarbonPeriod::create($this->startDate, $this->endDate);

        foreach ($period as $date) {
            $sheets[] = new DailyJobdeskPerDateSheet(
                $this->petugasId,
                $date->format('Y-m-d')
            );
        }

        return $sheets;
    }
}
