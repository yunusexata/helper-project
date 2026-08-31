<x-layouts::auth :title="__('Masuk ke Portal Operasional')">
    <div class="flex flex-col gap-5">
        <!-- Form Header -->
        <div class="text-left border-b border-slate-100 dark:border-slate-800 pb-4">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Portal Masuk Petugas & Staf</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Masukkan kredensial email dan kata sandi Anda untuk melanjutkan.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-left" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            <!-- Email Address -->
            <flux:field>
                <flux:label>Alamat Email</flux:label>
                <flux:input
                    name="email"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="nama@exata-indonesia.id"
                />
                <flux:error name="email" />
            </flux:field>

            <!-- Password -->
            <flux:field>
                <div class="flex items-center justify-between">
                    <flux:label>Kata Sandi</flux:label>
                    @if (Route::has('password.request'))
                        <flux:link class="text-xs text-blue-600 dark:text-blue-400 hover:underline" :href="route('password.request')" wire:navigate>
                            Lupa kata sandi?
                        </flux:link>
                    @endif
                </div>
                <flux:input
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    viewable
                />
                <flux:error name="password" />
            </flux:field>

            <!-- Remember Me -->
            <div class="pt-1">
                <flux:checkbox name="remember" label="Ingat saya di perangkat ini" :checked="old('remember')" />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <flux:button variant="primary" type="submit" class="w-full font-bold shadow-sm" data-test="login-button">
                    Masuk ke Sistem
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
