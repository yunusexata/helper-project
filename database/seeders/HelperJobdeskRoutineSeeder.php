<?php

namespace Database\Seeders;

use App\Models\HelperJobdeskRoutine;
use Illuminate\Database\Seeder;

class HelperJobdeskRoutineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobdesk = [
            'Grup 1' => [
                'Buka Ruko Exata',
                'Matikan Lampu Teras',
                'Keluarkan Rak Sepatu',
                'Nyalakan Lampu Dan Ac Tiap Lantai Exata Lt.1 Lt.2 Lt.3 Kreatif, Pantri',
                'Nyalakan Pengharum Ruangan',
                'Nyalain Monitor Cctv Pak Novi',
            ],
            'Grup 2' => [
                'Buka Ruko Yanoshi',
                'Matikan Lampu Teras & Lampu Banner',
                'Keluarkan Rak Sepatu',
                'Ambil Piring Kotor',
            ],
            'Grup 3' => [
                'Bersihkan Meja Pantri & Meja Kursi Di Luar Untuk Sarapan',
            ],
            'Grup 4' => [
                'Sapu Teras Dan Halaman Exata & Yanoshi',
            ],
            'Grup 5' => [
                'Sapu Lantai 1,2,3 Exata',
            ],
            'Grup 6' => [
                'Cuci Gelas & Piring Yanoshi Yang Kotor',
                'Rapikan Piring Yang Sudah Ditiriskan Ke Rak Piring',
            ],
            'Grup 7' => [
                'Bersihin Toilet Lantai 1, 2 Exata',
            ],
            'Grup 8' => [
                'Sapu Lantai Lt.1   Lt.2   Lt.3',
                'Lap Meja Kerja & Meja Packing Yanoshi',
                'Lap Pintu Kaca Yanoshi Lt.1 & Lt.3',
            ],
            'Grup 9' => [
                'Ngepel Lantai 1,2,3 Yanoshi',
                'Bersihkan Toilet Lantai 1, 2 Yanoshi',
            ],
            'Grup 10' => [
                'Cuci Mobil',
            ],
            'Grup 11' => [
                'Siapin Catering',
            ],
            'Grup 12' => [
                'Membersihkan Meja Sehabis Makan',
            ],
            'Grup 13' => [
                'Intermezo, Cek Kebersihan Toilet Lt.1 & Lt.2 + Galon',
            ],
            'Grup 14' => [
                'Cuci Piring Exata',
            ],
            'Grup 15' => [
                'Belanja Pantry',
            ],
            'Grup 16' => [
                'Bersihkan Piring Kotor Yanoshi',
            ],
            'Grup 17' => [
                'Buang Sampah Lt.1  Lt2  Lt3',
                'Sapu Lt.1 Lt.2 Lt.3',
                'Pel Lt.1 Lt.2 Lt.3',
                'Lap Meja Kantor & Meja Meeting',
                'Pel Lantai Wastafel Belakang & Sikat Alas Karet Di Pantry',
            ],
            'Grup 18' => [
                'Cek Kebersihan Yanoshi',
            ],
            'Grup 19' => [
                'Matikan Pengharum Ruangan',
                'Matikan Lampu Dan Ac Tiap Lantai Exata Lt.1 Lt.2 Lt.3 Kreatif, Pantri',
                'Masukkan Rak Sepatu',
                'Nyalakan Lampu Teras',
                'Matikan Monitor Cctv Pak Novi',
                'Tutup Ruko Exata',
            ],
        ];

        // Days from senin to minggu (Monday to Sunday)
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        // Disable foreign key checks if any, truncate table
        HelperJobdeskRoutine::truncate();

        foreach ($days as $day) {
            $order = 1;
            foreach ($jobdesk as $groupName => $activities) {
                foreach ($activities as $activity) {
                    $cleanedActivity = trim(preg_replace('/^\d+\.\s*/', '', $activity));
                    HelperJobdeskRoutine::create([
                        'day' => $day,
                        'task_group' => $groupName,
                        'activity_name' => $cleanedActivity,
                        'note' => null,
                        'order' => $order++,
                    ]);
                }
            }
        }
    }
}
