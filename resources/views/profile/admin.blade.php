<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __("Profil Admin") }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session("status") === "profile-updated")
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    Profil berhasil diperbarui.
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                    <p class="font-bold mb-1">Harap perbaiki kesalahan berikut:</p>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Informasi Akun</h3>
                <form method="POST" action="{{ route("profile.update") }}" class="space-y-4">
                    @csrf
                    @method("PATCH")
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama</label>
                        <input type="text" name="name" value="{{ old("name", $user->name) }}" required
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error("name")<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old("email", $user->email) }}" required
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error("email")<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-black text-white rounded-md text-xs font-semibold uppercase tracking-wider shadow-sm transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Ubah Password</h3>
                <form method="POST" action="{{ route("password.update") }}" class="space-y-4">
                    @csrf
                    @method("PUT")
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password Saat Ini</label>
                        <input type="password" name="current_password" autocomplete="current-password"
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error("current_password", "updatePassword")<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error("password", "updatePassword")<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               class="w-full text-sm border border-gray-300 rounded-md py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-black text-white rounded-md text-xs font-semibold uppercase tracking-wider shadow-sm transition">
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
