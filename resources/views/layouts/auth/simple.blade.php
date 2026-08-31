<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased flex flex-col items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md space-y-6">
            <!-- Institutional Brand Header -->
            <div class="text-center space-y-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <div class="size-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-xl shadow-sm transition group-hover:scale-105">
                        E
                    </div>
                    <div class="text-left">
                        <span class="text-sm font-extrabold tracking-tight text-slate-900 dark:text-white block leading-tight">
                            PT SUMBER REZEKI EXATA INDONESIA
                        </span>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 block">
                            Helper Management Portal
                        </span>
                    </div>
                </a>
            </div>

            <!-- Elevated Auth Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">
                {{ $slot }}
            </div>

            <!-- Footer Security Note -->
            <div class="text-center space-y-1">
                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                    Sistem internal resmi PT Sumber Rezeki Exata Indonesia
                </p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500">
                    © {{ date('Y') }} Seluruh hak cipta dilindungi.
                </p>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
