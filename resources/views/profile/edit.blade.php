<x-storefront-layout title="Data Diri — Profil Saya">
    <div class="min-h-screen bg-[#FAF7F2] py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if(in_array(session('status'), ['avatar-updated', 'avatar-removed'], true))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    @if(session('status') === 'avatar-updated')
                        Foto profil berhasil diperbarui.
                    @else
                        Foto profil dihapus. Avatar inisial kembali digunakan.
                    @endif
                </div>
            @endif
            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8">
                {{-- Kolom Kiri: Sidebar Profil & Navigasi --}}
                <div class="lg:col-span-4">
                    <x-customer-sidebar active="profile" :user="$user" :alamatCount="$alamats->count()" :pesananAktif="$pesananAktif" />
                </div>

                {{-- Kolom Kanan: Informasi Akun --}}
                <div class="space-y-6 lg:col-span-8" x-data="{ editModal: false }">
                    {{-- Informasi Akun & Data Diri --}}
                    <div class="rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-xs sm:p-7">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-stone-900 sm:text-xl">Informasi Akun & Data Diri</h3>
                                <p class="mt-1 text-xs text-stone-500 sm:text-sm">Kelola identitas diri dan pengaturan privasi profil Anda.</p>
                            </div>
                            <button @click="editModal = true"
                                    type="button"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#201A17] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-stone-800">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                                <span>Edit Profil</span>
                            </button>
                        </div>

                        <div class="mt-6 border-t border-stone-100 pt-6">
                            <dl class="grid grid-cols-1 gap-y-7 gap-x-8 sm:grid-cols-2">
                                {{-- Nama Lengkap --}}
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-stone-400">NAMA LENGKAP</dt>
                                    <dd class="mt-1 text-base font-semibold text-stone-900">{{ $user->name ?: '-' }}</dd>
                                </div>

                                {{-- Jenis Kelamin --}}
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-stone-400">JENIS KELAMIN</dt>
                                    <dd class="mt-1 text-base font-semibold text-stone-900">{{ $user->jenis_kelamin ?: '-' }}</dd>
                                </div>

                                {{-- Alamat Email --}}
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-stone-400">ALAMAT EMAIL</dt>
                                    <dd class="mt-1 text-base font-semibold text-stone-900">{{ $user->email }}</dd>
                                    @if ($user->hasVerifiedEmail())
                                        <div class="mt-1.5">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F4EDE4] px-2.5 py-0.5 text-[10px] font-bold text-[#4A3E31]">
                                                <svg class="h-3 w-3 text-[#4A3E31]" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Terverifikasi</span>
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Nomor Telepon --}}
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-stone-400">NOMOR TELEPON</dt>
                                    <dd class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="text-base font-semibold text-stone-900">{{ $user->phone ?: '-' }}</span>
                                        @if (!empty($user->phone))
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F4EDE4] px-2.5 py-0.5 text-[10px] font-bold text-[#4A3E31]">
                                                <svg class="h-3 w-3 text-[#4A3E31]" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Terverifikasi</span>
                                            </span>
                                        @endif
                                    </dd>
                                </div>

                                {{-- Tanggal Lahir --}}
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-wider text-stone-400">TANGGAL LAHIR</dt>
                                    <dd class="mt-1 text-base font-semibold text-stone-900">
                                        @if(!empty($user->tanggal_lahir))
                                            @php
                                                try {
                                                    $formattedDate = \Carbon\Carbon::parse($user->tanggal_lahir)->translatedFormat('d F Y');
                                                } catch (\Exception $e) {
                                                    $formattedDate = $user->tanggal_lahir;
                                                }
                                            @endphp
                                            {{ $formattedDate }}
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Modal Edit Profil --}}
                    <div x-cloak x-show="editModal"
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="editModal = false"></div>

                        <div class="relative w-full max-w-lg rounded-3xl border border-[#ECE4D8] bg-white p-6 shadow-2xl sm:p-8"
                             @click.stop>
                            <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-stone-900">Edit Data Diri</h3>
                                    <p class="text-xs text-stone-500 mt-0.5">Perbarui informasi profil dan kontak Anda.</p>
                                </div>
                                <button @click="editModal = false" class="rounded-full p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-500">Nama Lengkap</label>
                                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                           class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-stone-500">Nomor Telepon</label>
                                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890"
                                           class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-500">Alamat Email</label>
                                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                           class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="jenis_kelamin" class="block text-xs font-bold uppercase tracking-wider text-stone-500">Jenis Kelamin</label>
                                        <select id="jenis_kelamin" name="jenis_kelamin" class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                            <option value="" {{ old('jenis_kelamin', $user->jenis_kelamin) ? '' : 'selected' }}>Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="Perempuan" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tanggal_lahir" class="block text-xs font-bold uppercase tracking-wider text-stone-500">Tanggal Lahir</label>
                                        @php
                                            $valTanggal = old('tanggal_lahir', $user->tanggal_lahir);
                                            // Konversi jika format sebelumnya DD/MM/YYYY agar cocok dengan input type="date" (YYYY-MM-DD)
                                            if ($valTanggal && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valTanggal)) {
                                                $parts = explode('/', $valTanggal);
                                                $valTanggal = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                                            } elseif ($valTanggal) {
                                                try {
                                                    $valTanggal = \Carbon\Carbon::parse($valTanggal)->format('Y-m-d');
                                                } catch (\Exception $e) {
                                                    // tetap gunakan apa adanya
                                                }
                                            }
                                        @endphp
                                        <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ $valTanggal }}"
                                               max="{{ now()->format('Y-m-d') }}"
                                               class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm focus:border-stone-400 focus:outline-none">
                                        @error('tanggal_lahir')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center justify-end gap-3 pt-3">
                                    <button @click="editModal = false" type="button" class="rounded-xl px-4 py-2.5 text-xs font-semibold text-stone-600 hover:bg-stone-100">Batal</button>
                                    <button type="submit" class="rounded-xl bg-[#201A17] px-6 py-2.5 text-xs font-semibold text-white hover:bg-stone-800">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-storefront-layout>
