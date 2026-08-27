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

            'Buka Ruko Exata',
            'Matikan Lampu Teras',
            'Keluarkan Rak Sepatu',
            'Nyalakan Lampu Dan Ac Tiap Lantai Exata Lt.1 Lt.2 Lt.3 Kreatif, Pantri',
            'Nyalakan Pengharum Ruangan',
            'Nyalain Monitor Cctv Pak Novi',
            'Buka Ruko Yanoshi ',
            'Matikan Lampu Teras & Lampu Banner',
            'Keluarkan Rak Sepatu',
            'Ambil Piring Kotor',
            'Bersihkan Meja Pantri & Meja Kursi Di Luar Untuk Sarapan',
            'Sapu Teras Dan Halaman Exata & Yanoshi',
            'Sapu Lantai 1,2,3 Exata',
            'Cuci Gelas & Piring Yanoshi Yang Kotor',
            'Rapikan Piring Yang Sudah Ditiriskan Ke Rak Piring',
            'Bersihin Toilet Lantai 1, 2 Exata',
            'Sapu Lantai Lt.1   Lt.2   Lt.3  ',
            'Lap Meja Kerja & Meja Packing Yanoshi',
            'Lap Pintu Kaca Yanoshi Lt.1 & Lt.3',
            'Ngepel Lantai 1,2,3 Yanoshi',
            'Bersihkan Toilet Lantai 1, 2 Yanoshi',
            'Cuci Mobil',
            'Kozzong (Lap Partisi Display Baju + Meja Display Yanoshi)',
            'Siapin Catering',
            'Istirahat',
            'Membersihkan Meja Sehabis Makan',
            'Intermezo, Cek Kebersihan Toilet Lt.1 & Lt.2 + Galon',
            'Cuci Piring Exata',
            'Belanja Pantry',
            'Kosong',
            'Bersihkan Piring Kotor Yanoshi',
            'Buang Sampah Lt.1  Lt2  Lt3',
            'Sapu Lt.1 Lt.2 Lt.3',
            'Pel Lt.1 Lt.2 Lt.3',
            'Lap Meja Kantor & Meja Meeting',
            'Pel Lantai Wastafel Belakang & Sikat Alas Karet Di Pantry',
            'Cek Kebersihan Yanoshi',
            'Matikan Pengharum Ruangan ',
            'Matikan Lampu Dan Ac Tiap Lantai Exata Lt.1 Lt.2 Lt.3 Kreatif, Pantri',
            'Masukkan Rak Sepatu',
            'Nyalakan Lampu Teras',
            'Matikan Monitor Cctv Pak Novi',
            'Tutup Ruko Exata',
        ];

        // Days from senin to minggu (Monday to Sunday)
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        foreach ($days as $day) {
            $order = 1;
            foreach ($jobdesk as $line) {
                $activity = trim($line);

                // Clean the numbering prefix if any, e.g. "1. " or "2. "
                $cleanedActivity = trim(preg_replace('/^\d+\.\s*/', '', $activity));

                HelperJobdeskRoutine::create([
                    'day' => $day,
                    'activity_name' => $cleanedActivity,
                    'note' => null,
                    'order' => $order++,
                ]);
            }
        }
    }
}
