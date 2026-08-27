<?php

namespace Database\Seeders;

use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskDailyHistory;
use App\Models\HelperJobdeskRoutine;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HelperJobdeskHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 3 Helpers (Users + EmployeeWhitelist)
        $helpersData = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@exata.co.id',
                'employee_id' => 'EMP-001',
                'division' => 'Helper Office',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@exata.co.id',
                'employee_id' => 'EMP-002',
                'division' => 'Helper Pantry',
            ],
            [
                'name' => 'Agus Wijaya',
                'email' => 'agus.wijaya@exata.co.id',
                'employee_id' => 'EMP-003',
                'division' => 'Helper Driver',
            ],
        ];

        $firstWhitelist = null;

        foreach ($helpersData as $index => $data) {
            // Create user
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('123exata'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign Spatie Role
            if (! $user->hasRole('Helper')) {
                $user->assignRole('Helper');
            }

            // Create Whitelist
            $whitelist = EmployeeWhitelist::firstOrCreate(
                ['email' => $data['email']],
                [
                    'employee_id' => $data['employee_id'],
                    'name' => $data['name'],
                    'division' => $data['division'],
                ]
            );

            if ($index === 0) {
                $firstWhitelist = $whitelist;
            }
        }

        // 2. Create sample history logs for today for Budi Santoso (first helper)
        if ($firstWhitelist) {
            $todayDayName = strtolower(now()->locale('id')->dayName);

            // Get routines of today
            $routines = HelperJobdeskRoutine::where('day', $todayDayName)
                ->orderBy('order')
                ->get();

            // Mark the first two routines as completed today
            foreach ($routines->take(2) as $index => $routine) {
                HelperJobdeskDailyHistory::firstOrCreate(
                    [
                        'employee_whitelists_id' => $firstWhitelist->id,
                        'subject_id' => $routine->id,
                        'subject_type' => HelperJobdeskRoutine::class,
                    ],
                    [
                        'employee_whitelists_name' => $firstWhitelist->name,
                        'start_at' => now()->subHours(4 - $index),
                        'finish_at' => now()->subHours(3 - $index),
                        'note' => 'Aktivitas rutin dikerjakan dengan baik.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
