<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Pengaturan Toko') }}
                </h2>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                    Atur kota dan asal pengiriman toko untuk informasi checkout pelanggan
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm">
                    <span class="font-medium">{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-lg font-bold leading-none text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 shadow-sm">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs sm:text-sm">• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <form action="{{ route('admin.pengaturan.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Nama Toko <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_toko" value="{{ old('nama_toko', $setting->nama_toko) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">No. Telepon Toko</label>
                            <input type="text" name="no_telepon_toko" value="{{ old('no_telepon_toko', $setting->no_telepon_toko) }}" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Kota Asal <span class="text-rose-500">*</span></label>
                            <input type="text" name="kota_asal" value="{{ old('kota_asal', $setting->kota_asal) }}" required placeholder="Contoh: Kota Surakarta (Solo)" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Provinsi Asal <span class="text-rose-500">*</span></label>
                            <input type="text" name="provinsi_asal" value="{{ old('provinsi_asal', $setting->provinsi_asal) }}" required placeholder="Contoh: Jawa Tengah" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Kode Pos Asal</label>
                            <input type="text" name="kode_pos_asal" value="{{ old('kode_pos_asal', $setting->kode_pos_asal) }}" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Alamat Lengkap Asal <span class="text-rose-500">*</span></label>
                        <textarea name="alamat_asal" rows="3" required class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('alamat_asal', $setting->alamat_asal) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Email Toko</label>
                            <input type="email" name="email_toko" value="{{ old('email_toko', $setting->email_toko) }}" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700">Deskripsi Singkat Toko</label>
                            <input type="text" name="deskripsi_toko" value="{{ old('deskripsi_toko', $setting->deskripsi_toko) }}" class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                        <button type="submit" class="rounded-md bg-gray-900 px-5 py-2 text-xs font-semibold uppercase tracking-wider text-white shadow-sm transition hover:bg-black">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
