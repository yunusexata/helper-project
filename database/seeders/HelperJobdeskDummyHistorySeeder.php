<?php

namespace Database\Seeders;

use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskDailyHistory;
use App\Models\HelperJobdeskRequest;
use App\Models\HelperJobdeskRoutine;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class HelperJobdeskDummyHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Resolve or create Helper User 1
        $helperUser = User::role('Helper')->first();

        if (! $helperUser) {
            $helperUser = User::firstOrCreate(
                ['email' => 'musa@exata-indonesia.id'],
                [
                    'name' => 'Pak Musa',
                    'password' => Hash::make('123exata'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $helperUser->hasRole('Helper')) {
                $helperUser->assignRole('Helper');
            }
        }

        // 2. Resolve or create Employee Whitelist
        $whitelist = EmployeeWhitelist::firstOrCreate(
            ['email' => $helperUser->email],
            [
                'employee_id' => 'EMP-001',
                'name' => $helperUser->name,
                'division' => 'Helper Office',
            ]
        );

        // 3. Define date range: 30 days ago to 30 days in the future
        $startDate = Carbon::today('Asia/Jakarta')->subDays(30);
        $endDate = Carbon::today('Asia/Jakarta')->addDays(30);

        // Clean up previous dummy entries for this helper in this date range to ensure clean idempotency
        HelperJobdeskDailyHistory::where('employee_whitelists_id', $whitelist->id)
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay()->utc(),
                $endDate->copy()->endOfDay()->utc(),
            ])
            ->delete();

        HelperJobdeskRequest::where('employee_whitelists_id', $whitelist->id)
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay()->utc(),
                $endDate->copy()->endOfDay()->utc(),
            ])
            ->delete();

        $sampleRequestActivities = [
            'Bantu angkat berkas arsip ke ruang rapat lantai 2',
            'Beli galon tambahan dan tisu dapur ke minimarket',
            'Bersihkan kaca depan dan pintu masuk lobby utama',
            'Rapikan tumpukan kardus paket di area ekspedisi',
            'Bantu pasang banner dan perlengkapan event meeting',
            'Perbaiki kran wastafel pantry yang longgar',
        ];

        // 4. Loop day-by-day across the 60-day span
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayName = strtolower($date->locale('id')->dayName);

            // Fetch scheduled routines for this day, grouped by task_group
            $routineGroups = HelperJobdeskRoutine::where('day', $dayName)
                ->orderBy('order')
                ->get()
                ->groupBy('task_group');

            if ($routineGroups->isEmpty()) {
                continue;
            }

            // --- PAST DAYS ($date < today) ---
            if ($date->lt(Carbon::today('Asia/Jakarta'))) {
                // Complete 70% to 90% of groups, leave 10%-30% as "Belum Selesai"
                $groupsCount = $routineGroups->count();
                $completedLimit = max(1, (int) round($groupsCount * 0.75));

                $hour = 8;
                $minute = 0;
                $currentGroupIndex = 0;

                foreach ($routineGroups as $groupName => $activities) {
                    $firstActivity = $activities->first();

                    if ($currentGroupIndex < $completedLimit) {
                        // CASE 1: Routine Selesai (Success)
                        $startTime = $date->copy()->setTime($hour, $minute, 0);
                        $durationMinutes = rand(25, 55);
                        $finishTime = $startTime->copy()->addMinutes($durationMinutes);

                        HelperJobdeskDailyHistory::create([
                            'employee_whitelists_id' => $whitelist->id,
                            'employee_whitelists_name' => $whitelist->name,
                            'subject_id' => $firstActivity->id,
                            'subject_type' => HelperJobdeskRoutine::class,
                            'start_at' => $startTime->utc(),
                            'finish_at' => $finishTime->utc(),
                            'note' => 'Aktivitas grup '.strtolower($groupName).' diselesaikan dengan rapi.',
                            'created_at' => $startTime->utc(),
                            'updated_at' => $finishTime->utc(),
                        ]);

                        // Advance time for next group
                        $minute += $durationMinutes + rand(5, 15);
                        if ($minute >= 60) {
                            $hour += (int) floor($minute / 60);
                            $minute = $minute % 60;
                        }
                    }
                    // CASE 3: Routine Belum Selesai (Leave without history record)

                    $currentGroupIndex++;
                }

                // Add 1 completed ad-hoc request every 2-3 days
                if ($date->day % 2 === 0) {
                    $reqActivity = $sampleRequestActivities[$date->day % count($sampleRequestActivities)];
                    $reqStartTime = $date->copy()->setTime(11, 15, 0);
                    $reqFinishTime = $reqStartTime->copy()->addMinutes(rand(20, 45));

                    $request = HelperJobdeskRequest::create([
                        'day' => $dayName,
                        'employee_whitelists_id' => $whitelist->id,
                        'employee_whitelists_name' => $whitelist->name,
                        'activity_name' => $reqActivity,
                        'created_at' => $reqStartTime->utc(),
                        'updated_at' => $reqFinishTime->utc(),
                    ]);

                    HelperJobdeskDailyHistory::create([
                        'employee_whitelists_id' => $whitelist->id,
                        'employee_whitelists_name' => $whitelist->name,
                        'subject_id' => $request->id,
                        'subject_type' => HelperJobdeskRequest::class,
                        'start_at' => $reqStartTime->utc(),
                        'finish_at' => $reqFinishTime->utc(),
                        'note' => 'Permintaan ad-hoc telah selesai dibantu.',
                        'created_at' => $reqStartTime->utc(),
                        'updated_at' => $reqFinishTime->utc(),
                    ]);
                }
            }

            // --- TODAY ($date == today) ---
            elseif ($date->isToday()) {
                $hour = 8;
                $minute = 0;
                $currentGroupIndex = 0;

                foreach ($routineGroups as $groupName => $activities) {
                    $firstActivity = $activities->first();

                    if ($currentGroupIndex < 2) {
                        // CASE 1: Routine Selesai (Morning finished tasks)
                        $startTime = $date->copy()->setTime($hour, $minute, 0);
                        $durationMinutes = rand(30, 45);
                        $finishTime = $startTime->copy()->addMinutes($durationMinutes);

                        HelperJobdeskDailyHistory::create([
                            'employee_whitelists_id' => $whitelist->id,
                            'employee_whitelists_name' => $whitelist->name,
                            'subject_id' => $firstActivity->id,
                            'subject_type' => HelperJobdeskRoutine::class,
                            'start_at' => $startTime->utc(),
                            'finish_at' => $finishTime->utc(),
                            'note' => 'Tugas pagi grup '.strtolower($groupName).' sudah bersih dan tuntas.',
                            'created_at' => $startTime->utc(),
                            'updated_at' => $finishTime->utc(),
                        ]);

                        $hour += 1;
                        $minute = 10;
                    } elseif ($currentGroupIndex === 2) {
                        // CASE 2: Routine Sedang Berjalan (Active / In-progress)
                        $startTime = now('Asia/Jakarta')->subMinutes(35);

                        HelperJobdeskDailyHistory::create([
                            'employee_whitelists_id' => $whitelist->id,
                            'employee_whitelists_name' => $whitelist->name,
                            'subject_id' => $firstActivity->id,
                            'subject_type' => HelperJobdeskRoutine::class,
                            'start_at' => $startTime->utc(),
                            'finish_at' => null,
                            'note' => null,
                            'created_at' => $startTime->utc(),
                            'updated_at' => $startTime->utc(),
                        ]);
                    }
                    // CASE 3: Remaining groups today are Belum Selesai (Pending)

                    $currentGroupIndex++;
                }

                // Add 1 in-progress ad-hoc request today
                $reqStartTime = now('Asia/Jakarta')->subMinutes(15);
                $request = HelperJobdeskRequest::create([
                    'day' => $dayName,
                    'employee_whitelists_id' => $whitelist->id,
                    'employee_whitelists_name' => $whitelist->name,
                    'activity_name' => 'Bantu pasang kabel proyektor di ruang meeting lantai 3',
                    'created_at' => $reqStartTime->utc(),
                    'updated_at' => $reqStartTime->utc(),
                ]);

                HelperJobdeskDailyHistory::create([
                    'employee_whitelists_id' => $whitelist->id,
                    'employee_whitelists_name' => $whitelist->name,
                    'subject_id' => $request->id,
                    'subject_type' => HelperJobdeskRequest::class,
                    'start_at' => $reqStartTime->utc(),
                    'finish_at' => null,
                    'note' => null,
                    'created_at' => $reqStartTime->utc(),
                    'updated_at' => $reqStartTime->utc(),
                ]);
            }

            // --- FUTURE DAYS ($date > today) ---
            else {
                // To test all cases across the future month:
                // Mostly unstarted ("Belum Selesai"), but simulate 1 finished routine group on tomorrow to test future dates
                if ($date->diffInDays(Carbon::today('Asia/Jakarta')) === 1) {
                    $firstGroup = $routineGroups->first();
                    if ($firstGroup) {
                        $firstActivity = $firstGroup->first();
                        $startTime = $date->copy()->setTime(8, 0, 0);
                        $finishTime = $startTime->copy()->addMinutes(40);

                        HelperJobdeskDailyHistory::create([
                            'employee_whitelists_id' => $whitelist->id,
                            'employee_whitelists_name' => $whitelist->name,
                            'subject_id' => $firstActivity->id,
                            'subject_type' => HelperJobdeskRoutine::class,
                            'start_at' => $startTime->utc(),
                            'finish_at' => $finishTime->utc(),
                            'note' => 'Simulasi jadwal besok telah diselesaikan.',
                            'created_at' => $startTime->utc(),
                            'updated_at' => $finishTime->utc(),
                        ]);
                    }
                }
                // All other future groups are naturally "Belum Selesai"
            }
        }
    }
}
