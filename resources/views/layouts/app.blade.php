<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Nenkin Portal') | by PT Sumber Rezeki Exata Indonesia</title>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    @stack('css')

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-slate-950');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-slate-950');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-slate-950');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-slate-950');
            }
        })();
    </script>
    
</head>

<body
    class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-200 antialiased min-h-screen"
    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    @auth
        <div class="min-h-screen xl:flex">
            @include('layouts.backdrop')
            @include('layouts.sidebar')

            <div class="flex-1 transition-all duration-300 ease-in-out flex flex-col min-h-screen"
                :class="{
                    'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                    'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                    'ml-0': $store.sidebar.isMobileOpen
                }">
                <!-- app header start -->
                @include('layouts.app-header')
                <!-- app header end -->
                
                <main class="flex-grow p-4 mx-auto w-full max-w-7xl md:p-6">
                    @yield('header')
                    {{ $slot ?? '' }}
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <!-- Guest Clean Layout (No Sidebar, No Navbar Menus) -->
        <div class="min-h-screen flex flex-col justify-between">
            <header class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="size-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-sm font-black text-lg">
                            E
                        </div>
                        <div>
                            <span class="text-sm font-extrabold tracking-tight text-slate-900 dark:text-white block leading-tight">
                                PT SUMBER REZEKI EXATA INDONESIA
                            </span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 block">
                                Helper Management Portal
                            </span>
                        </div>
                    </a>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('home') }}" class="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-blue-600 transition">
                            Beranda
                        </a>
                        <a href="{{ route('login') }}" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                            <span class="material-symbols-outlined text-sm">login</span>
                            <span>Masuk ke Portal</span>
                        </a>
                    </div>
                </div>
            </header>

            <main class="flex-grow p-4 mx-auto w-full max-w-6xl md:p-6">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

            <footer class="border-t border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 py-4 text-center text-xs text-slate-400">
                © {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. Seluruh hak cipta dilindungi.
            </footer>
        </div>
    @endauth

</body>

    @livewireScripts
    @fluxScripts
<script>
    
        Livewire.on("{{ \App\Helpers\Alert::EVENT_INFO }}", (event) => {
            Swal.fire({
                icon: event[0],
                title: event[1],
                text: event[2],
            });
        });

        Livewire.on("{{ \App\Helpers\Alert::EVENT_CONSOLE_LOG }}", (event) => {
            console.log(event[0])
        });

        Livewire.on("{{ \App\Helpers\Alert::EVENT_CONFIRMATION }}", (event) => {
            Swal.fire({
                icon: event[0],
                title: event[1],
                text: event[2],
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: event[3],
                cancelButtonText: event[4],
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch(event[5]);
                } else {
                    Livewire.dispatch(event[6]);
                }
            });
        });
        Livewire.on("{{ \App\Helpers\Alert::EVENT_INFORMATION }}", (event) => {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: event[0],
                title: event[1],
                showConfirmButton: false,
                timer: event[2] || 2000,
                timerProgressBar: true
            });
        });

        Livewire.on('refresh-page', (data) => {
            location.reload();
        });

        Livewire.on('consoleLog', (data) => {
            console.log(data)
        });
        window.copyToClipboard = function(text)
        {
            navigator.clipboard.writeText(text)
            .then(() => {
                Swal.fire({
                    position: "top-end",
                    icon: 'success',
                    title: 'Berhasil Copy Data!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
</script>
@stack('scripts')

</html>
