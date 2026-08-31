<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Operasional Petugas | PT Sumber Rezeki Exata Indonesia</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased flex flex-col justify-between">
    
    <!-- Top Navigation Header -->
    <header class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between">
            <!-- Brand Logo & Name (Adaptive text on mobile) -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3">
                <div class="size-8 sm:size-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs font-black text-base sm:text-lg shrink-0">
                    E
                </div>
                <div class="leading-tight">
                    <!-- Mobile View Name -->
                    <span class="text-xs font-black tracking-tight text-slate-900 dark:text-white block sm:hidden">
                        EXATA INDONESIA
                    </span>
                    <span class="text-[9px] font-semibold text-slate-500 dark:text-slate-400 block sm:hidden">
                        Monitoring Portal
                    </span>

                    <!-- Desktop View Name -->
                    <span class="hidden sm:block text-sm font-extrabold tracking-tight text-slate-900 dark:text-white">
                        PT SUMBER REZEKI EXATA INDONESIA
                    </span>
                    <span class="hidden sm:block text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                        Helper Management System
                    </span>
                </div>
            </a>

            <!-- Right Action / Auth Status -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @auth
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ auth()->user()->name }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
                    </div>
                    <a href="{{ route('dashboard') }}" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                        <span class="material-symbols-outlined text-sm">dashboard</span>
                        <span class="hidden sm:inline">Buka</span> <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition whitespace-nowrap">
                        <span class="material-symbols-outlined text-sm">login</span>
                        <span class="inline sm:hidden">Masuk</span>
                        <span class="hidden sm:inline">Masuk ke Portal</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow py-5 sm:py-8 md:py-12">
        <div class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8 space-y-6 sm:space-y-10">
            
            <!-- Page Header Introduction -->
            <div class="text-center max-w-3xl mx-auto space-y-2.5 px-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/40 text-blue-700 dark:text-blue-300 text-[10px] sm:text-xs font-bold uppercase tracking-wider">
                    <span class="size-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                    Portal Operasional & Pelaporan Harian
                </div>

                <h1 class="text-xl sm:text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                    Monitoring Progres Harian Petugas (Live)
                </h1>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-350 leading-relaxed max-w-2xl mx-auto">
                    Pantau status pengerjaan rutinitas dan permintaan tugas petugas harian secara real-time di lingkungan kerja PT Sumber Rezeki Exata Indonesia.
                </p>
            </div>

            <!-- Live Monitoring Dashboard Component -->
            <div class="w-full">
                <livewire:dashboard />
            </div>

            <!-- 3 Formal Pillar Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-6 pt-2 sm:pt-4 px-1 sm:px-0">
                <!-- Pillar 1 -->
                <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3">
                    <div class="size-9 sm:size-11 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-xl sm:text-2xl">category</span>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Manajemen Rutinitas Berkelompok</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                            Penjadwalan tugas harian terstruktur berdasarkan kelompok area kerja (Pantry, Lobby, Lantai 1–3, Toilet) untuk memastikan setiap zona tertata rapi.
                        </p>
                    </div>
                </div>

                <!-- Pillar 2 -->
                <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3">
                    <div class="size-9 sm:size-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-xl sm:text-2xl">assignment_add</span>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Pencatatan Permintaan Khusus</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                            Fasilitas pelaporan pekerjaan ad-hoc di luar rutinitas dengan pencatatan waktu otomatis, deskripsi aktivitas, catatan laporan, dan bukti foto.
                        </p>
                    </div>
                </div>

                <!-- Pillar 3 -->
                <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3">
                    <div class="size-9 sm:size-11 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-xl sm:text-2xl">analytics</span>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Monitoring Progres & Durasi</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                            Dasbor pemantauan komprehensif bagi pimpinan dan staf dengan rekapitulasi waktu kerja (WIB), kalkulasi durasi tugas, dan audit kepatuhan harian.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Security & Notice Banner -->
            <div class="bg-slate-100/80 dark:bg-slate-900/60 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left mx-1 sm:mx-0">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-xl sm:text-2xl shrink-0">verified_user</span>
                    <div>
                        <h4 class="text-[11px] sm:text-xs font-extrabold uppercase tracking-wide text-slate-800 dark:text-slate-200">Sistem Terotentikasi Khusus Internal</h4>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Akses portal dibatasi khusus untuk karyawan, petugas, dan manajemen PT Sumber Rezeki Exata Indonesia.</p>
                    </div>
                </div>
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 whitespace-nowrap self-center sm:self-auto">
                    Versi 1.2
                </span>
            </div>

        </div>
    </main>

    <!-- Institutional Footer -->
    <footer class="border-t border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-3 text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 text-center sm:text-left">
            <div>
                © {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. Seluruh hak cipta dilindungi.
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] sm:text-[11px] text-slate-400">Server: WIB (Asia/Jakarta)</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
