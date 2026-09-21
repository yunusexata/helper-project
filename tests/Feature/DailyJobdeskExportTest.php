<?php

use App\Exports\DailyJobdeskExport;
use App\Exports\Sheets\DailyJobdeskPerDateSheet;
use App\Livewire\Dashboard;
use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskDailyHistory;
use App\Models\HelperJobdeskRequest;
use App\Models\HelperJobdeskRoutine;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::firstOrCreate(['name' => 'Helper']);
});

test('guests cannot export excel', function () {
    Livewire::test(Dashboard::class)
        ->call('exportExcel')
        ->assertStatus(403);
});

test('authenticated users can open and close export modal with default dates', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $expectedHelperId = User::role('Helper')->first()?->id;

    Livewire::test(Dashboard::class)
        ->call('openExportModal')
        ->assertSet('showExportModal', true)
        ->assertSet('exportPetugasId', $expectedHelperId)
        ->assertSet('exportTanggalAwal', now()->format('Y-m-d'))
        ->assertSet('exportTanggalAkhir', now()->format('Y-m-d'))
        ->assertDispatched('open-export-modal')
        ->call('closeExportModal')
        ->assertSet('showExportModal', false)
        ->assertDispatched('close-export-modal');
});

test('export validation requires valid helper and dates', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->set('exportPetugasId', null)
        ->set('exportTanggalAwal', '')
        ->set('exportTanggalAkhir', '')
        ->call('exportExcel')
        ->assertHasErrors(['exportPetugasId', 'exportTanggalAwal', 'exportTanggalAkhir']);
});

test('export validation fails when tanggal akhir is before tanggal awal', function () {
    $user = User::factory()->create();
    $helper = User::factory()->create();
    $helper->assignRole('Helper');

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->set('exportPetugasId', $helper->id)
        ->set('exportTanggalAwal', '2026-09-20')
        ->set('exportTanggalAkhir', '2026-09-10')
        ->call('exportExcel')
        ->assertHasErrors(['exportTanggalAkhir' => 'after_or_equal']);
});

test('authenticated user can successfully export excel with multiple date sheets', function () {
    Excel::fake();

    $user = User::factory()->create();
    $helper = User::factory()->create(['name' => 'Budi Santoso']);
    $helper->assignRole('Helper');

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->set('exportPetugasId', $helper->id)
        ->set('exportTanggalAwal', '2026-09-01')
        ->set('exportTanggalAkhir', '2026-09-03')
        ->call('exportExcel')
        ->assertHasNoErrors();

    Excel::assertDownloaded('laporan-jobdesk-budi-santoso-2026-09-01-sd-2026-09-03.xlsx', function (DailyJobdeskExport $export) {
        $sheets = $export->sheets();

        return count($sheets) === 3;
    });
});

test('daily jobdesk per date sheet outputs correct headings and structure without bukti foto', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $helper = User::factory()->create(['email' => 'helper-test@example.com']);
    $helper->assignRole('Helper');

    $whitelist = EmployeeWhitelist::create([
        'employee_id' => 'EMP-TEST-001',
        'name' => 'Helper Test Custom',
        'email' => 'helper-test@example.com',
    ]);

    $today = now()->format('Y-m-d');
    $todayDayName = strtolower(now()->locale('id')->dayName);

    // Create custom routine for today
    $routine = HelperJobdeskRoutine::create([
        'day' => $todayDayName,
        'task_group' => 'Grup Test Khusus',
        'activity_name' => 'Bersihkan Ruang Rapat Khusus',
        'order' => 999,
    ]);

    // Create ad-hoc request
    $request = HelperJobdeskRequest::create([
        'day' => $todayDayName,
        'activity_name' => 'Bantu Angkat Meja Khusus',
        'employee_whitelists_id' => $whitelist->id,
        'employee_whitelists_name' => $whitelist->name,
    ]);

    // History for routine
    HelperJobdeskDailyHistory::create([
        'employee_whitelists_id' => $whitelist->id,
        'employee_whitelists_name' => $whitelist->name,
        'subject_id' => $routine->id,
        'subject_type' => HelperJobdeskRoutine::class,
        'start_at' => now()->setTime(8, 0, 0),
        'finish_at' => now()->setTime(9, 0, 0),
        'note' => 'Ruangan telah bersih',
    ]);

    // History for request
    HelperJobdeskDailyHistory::create([
        'employee_whitelists_id' => $whitelist->id,
        'employee_whitelists_name' => $whitelist->name,
        'subject_id' => $request->id,
        'subject_type' => HelperJobdeskRequest::class,
        'start_at' => now()->setTime(9, 15, 0),
        'finish_at' => now()->setTime(9, 45, 0),
        'note' => 'Meja selesai dipindahkan',
    ]);

    $sheet = new DailyJobdeskPerDateSheet($helper->id, $today);

    $headings = $sheet->headings();
    expect($headings)->toBe([
        'Tipe / Kategori',
        'Nama Aktivitas',
        'Status',
        'Jam Mulai',
        'Jam Selesai',
        'Durasi',
        'Catatan Laporan',
    ]);
    expect($headings)->not->toContain('Bukti Foto');

    $rows = $sheet->array();
    expect(count($rows))->toBeGreaterThanOrEqual(2);

    // Assert custom request row exists and is formatted correctly
    $requestRow = collect($rows)->first(fn ($r) => $r[1] === 'Bantu Angkat Meja Khusus');
    expect($requestRow)->not->toBeNull();
    expect($requestRow[0])->toBe('Request');
    expect($requestRow[2])->toBe('Selesai');
    expect($requestRow[3])->toBe('09:15');
    expect($requestRow[4])->toBe('09:45');
    expect($requestRow[5])->toBe('30 menit');
    expect($requestRow[6])->toBe('Meja selesai dipindahkan');

    // Assert custom routine row exists and contains bullet formatted activity
    $routineRow = collect($rows)->first(fn ($r) => str_contains($r[1], 'Grup Test Khusus'));
    expect($routineRow)->not->toBeNull();
    expect($routineRow[0])->toBe('Rutinitas');
    expect($routineRow[1])->toContain('• Bersihkan Ruang Rapat Khusus');
    expect($routineRow[2])->toBe('Selesai');
    expect($routineRow[6])->toBe('Ruangan telah bersih');
});
