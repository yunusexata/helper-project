<form wire:submit="store">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Nama --}}
        <div>
            <label class="mb-2.5 block text-sm font-medium text-dark dark:text-white">
                Nama
            </label>

            <input
                type="text"
                wire:model.blur="name"
                class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-dark outline-none transition focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white @error('name') border-danger @enderror"
            />

            @error('name')
                <p class="mt-1 text-sm text-danger">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="mb-2.5 block text-sm font-medium text-dark dark:text-white">
                Email
            </label>

            <input
                type="email"
                wire:model.blur="email"
                class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-dark outline-none transition focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white @error('email') border-danger @enderror"
            />

            @error('email')
                <p class="mt-1 text-sm text-danger">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Role --}}
        <div>
            <label class="mb-2.5 block text-sm font-medium text-dark dark:text-white">
                Jabatan
            </label>

            <select
                wire:model.blur="role"
                class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-dark outline-none transition focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white @error('role') border-danger @enderror"
            >
                @foreach ($roles as $role_name)
                    <option value="{{ $role_name }}">
                        {{ $role_name }}
                    </option>
                @endforeach
            </select>

            @error('role')
                <p class="mt-1 text-sm text-danger">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="lg:col-span-3">
            <label class="mb-2.5 block text-sm font-medium text-dark dark:text-white">
                Password
            </label>

            @if ($objId)
                <p class="mb-2 text-sm italic text-body">
                    *Diisi jika ingin mengubah password
                </p>
            @endif

            <input
                type="password"
                wire:model.blur="password"
                class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-dark outline-none transition focus:border-primary dark:border-dark-3 dark:bg-dark-2 dark:text-white @error('password') border-danger @enderror"
            />

            @error('password')
                <p class="mt-1 text-sm text-danger">
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2.5 rounded bg-meta-3 px-6 py-3 text-center bg-blue-500 rounded font-medium text-white hover:bg-opacity-90 lg:px-8"
        >
            Save
        </button>
    </div>
</form>