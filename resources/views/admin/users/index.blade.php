@extends('layouts.admin-master')

@section('title', 'Kelola User')
@section('page_key', 'users')

@php
    $total = $users->count();
    $jumlahSuper = $users->where('role', 'super_admin')->count();
    $jumlahAdmin = $users->where('role', 'admin')->count();
    $authId = auth()->id();

    $kartu = 'rounded-2xl border border-slate-200/80 bg-white shadow-sm';

    $inisial = function (string $nama): string {
        $kata = array_filter(preg_split('/\s+/', trim($nama)));
        $huruf = array_map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)), array_slice($kata, 0, 2));

        return $huruf ? implode('', $huruf) : '-';
    };
@endphp

@section('content')

    {{-- ==================== JUDUL ==================== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3.5">
            <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#0F172A] text-blue-400 shadow-md">
                <i class="fas fa-user-shield text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Kelola User Admin</h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    Manajemen hak akses administrator, NIP, dan peran pengelola sistem SILAPIN.
                </p>
            </div>
        </div>

        <a href="{{ route('users.create') }}"
           class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 md:self-auto">
            <i class="fas fa-user-plus text-[11px]"></i> Tambah Admin
        </a>
    </div>

    {{-- ==================== KARTU METRIK ==================== --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Total Akun Admin</p>
                <div class="mt-1 flex items-center gap-2.5">
                    <h3 class="text-2xl font-bold text-slate-900">{{ $total }}</h3>
                    <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">
                        Terdaftar
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Seluruh pengguna dengan akses panel admin</p>
            </div>
            <div class="shrink-0 rounded-xl border border-blue-100 bg-blue-50 p-3 text-blue-600">
                <i class="fas fa-users text-lg"></i>
            </div>
        </div>

        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Super Admin</p>
                <div class="mt-1 flex items-center gap-2.5">
                    <h3 class="text-2xl font-bold text-slate-900">{{ $jumlahSuper }}</h3>
                    <span class="rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700">
                        Akses Penuh
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Kontrol penuh sistem dan manajemen akun</p>
            </div>
            <div class="shrink-0 rounded-xl border border-slate-700 bg-[#0F172A] p-3 text-indigo-400">
                <i class="fas fa-shield-alt text-lg"></i>
            </div>
        </div>

        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Admin Operasional</p>
                <div class="mt-1 flex items-center gap-2.5">
                    <h3 class="text-2xl font-bold text-slate-900">{{ $jumlahAdmin }}</h3>
                    <span class="rounded-full border border-teal-200 bg-teal-50 px-2 py-0.5 text-[10px] font-bold text-teal-700">
                        Transaksi &amp; Data
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Pengelola permohonan, pengembalian, dan surat</p>
            </div>
            <div class="shrink-0 rounded-xl border border-teal-100 bg-teal-50 p-3 text-teal-600">
                <i class="fas fa-user-check text-lg"></i>
            </div>
        </div>
    </div>

    {{-- ==================== DAFTAR AKUN ==================== --}}
    <div class="{{ $kartu }} overflow-hidden">

        <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/50 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-blue-400">
                    <i class="fas fa-users text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Akun Admin</h2>
                    <p class="text-xs text-slate-500">
                        Menampilkan <span id="jumlahTampil" class="font-bold text-slate-700">{{ $total }}</span>
                        dari <span class="font-bold text-slate-700">{{ $total }}</span> akun administrator terdaftar
                    </p>
                </div>
            </div>

            <div class="flex flex-col items-stretch gap-2.5 sm:flex-row sm:items-center">
                <div class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-slate-400"></i>
                    <input type="search" id="cariUser" placeholder="Cari nama, NIP, atau email..."
                           class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-8 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:w-64">
                </div>

                <div class="relative">
                    <i class="fas fa-filter pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-slate-400"></i>
                    <select id="filterRole"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-8 pr-8 text-xs font-medium text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:w-44">
                        <option value="">Semua Role</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                    </select>
                    <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                </div>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table id="tabelAdmin" class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-bold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-14 px-5 py-3.5 text-center">No</th>
                        <th class="px-4 py-3.5">Profil &amp; Nama</th>
                        <th class="whitespace-nowrap px-4 py-3.5">NIP</th>
                        <th class="px-4 py-3.5">Email</th>
                        <th class="whitespace-nowrap px-4 py-3.5 text-center">Role</th>
                        <th class="whitespace-nowrap px-4 py-3.5">Tanggal Dibuat</th>
                        <th class="w-32 px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($users as $item)
                        @php
                            $super = $item->isSuperAdmin();
                            $diriSendiri = $item->id === $authId;
                            $kataCari = mb_strtolower($item->name . ' ' . (string) $item->nip . ' ' . $item->email);

                            if ($super) {
                                $alasanDilindungi = 'Akun Super Admin tidak dapat dihapus dari halaman ini.';
                            } elseif ($diriSendiri) {
                                $alasanDilindungi = 'Akun yang sedang dipakai masuk tidak dapat dihapus.';
                            } else {
                                $alasanDilindungi = null;
                            }
                        @endphp
                        <tr class="users-row group transition-colors hover:bg-slate-50/90"
                            data-cari="{{ $kataCari }}" data-role="{{ $item->role }}">

                            <td class="no-row px-5 py-4 text-center font-semibold text-slate-500">{{ $loop->iteration }}</td>

                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border text-xs font-bold shadow-sm
                                        {{ $super ? 'border-slate-700 bg-[#0F172A] text-blue-400' : 'border-teal-200 bg-teal-50 text-teal-700' }}">
                                        {{ $inisial($item->name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="truncate text-sm font-bold text-slate-900">{{ $item->name }}</span>
                                            @if($super)
                                                <i class="fas fa-user-shield shrink-0 text-[11px] text-blue-600"
                                                   title="Akun Super Admin"></i>
                                            @endif
                                            @if($diriSendiri)
                                                <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500">
                                                    Anda
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-slate-400">
                                            {{ $super ? 'Administrator Utama' : 'Staf Pengelola' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @if(filled($item->nip))
                                    <div class="inline-flex items-center gap-2 rounded-lg border border-slate-200/80 bg-slate-50 px-2.5 py-1">
                                        <code class="font-mono text-[11px] font-semibold tracking-wide text-slate-700">{{ $item->nip }}</code>
                                        <button type="button" data-salin="{{ $item->nip }}" title="Salin NIP"
                                                class="text-slate-400 transition hover:text-blue-600">
                                            <i class="far fa-copy text-[11px]"></i>
                                        </button>
                                    </div>
                                @else
                                    <a href="{{ route('users.edit', $item->id) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 transition hover:bg-amber-100">
                                        <i class="fas fa-exclamation-circle text-[10px]"></i> NIP belum diisi
                                    </a>
                                @endif
                            </td>

                            <td class="px-4 py-4 font-medium text-slate-600">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-envelope shrink-0 text-[11px] text-slate-400"></i>
                                    <span class="truncate">{{ $item->email }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($super)
                                        <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-indigo-500/30 bg-[#0F172A] px-3 py-1 text-[11px] font-bold text-indigo-300 shadow-sm">
                                            <i class="fas fa-shield-alt text-[9px] text-indigo-400"></i> Super Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-[11px] font-bold text-teal-700">
                                            <i class="fas fa-user-check text-[9px] text-teal-600"></i> Admin
                                        </span>
                                    @endif

                                    @if($item->status !== 'approved')
                                        <span class="whitespace-nowrap rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                                            Belum disetujui
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <i class="far fa-calendar text-[11px] text-slate-400"></i>
                                    <span>{{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}</span>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('users.edit', $item->id) }}" title="Edit Akun Admin"
                                       class="rounded-lg border border-blue-200 bg-blue-50 p-2 text-blue-600 transition-all hover:border-blue-600 hover:bg-blue-600 hover:text-white">
                                        <i class="fas fa-pen text-[11px]"></i>
                                    </a>

                                    @if($alasanDilindungi)
                                        <button type="button" disabled title="{{ $alasanDilindungi }}"
                                                class="cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 p-2 text-slate-400">
                                            <i class="fas fa-user-shield text-[11px]"></i>
                                        </button>
                                    @else
                                        <button type="button" data-hapus-user
                                                data-url="{{ route('users.destroy', $item->id) }}"
                                                data-nama="{{ $item->name }}"
                                                title="Hapus Akun Admin"
                                                class="rounded-lg border border-rose-200 bg-rose-50 p-2 text-rose-600 transition-all hover:border-rose-600 hover:bg-rose-600 hover:text-white">
                                            <i class="fas fa-trash text-[11px]"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-xs text-slate-400">
                                Belum ada akun administrator. Klik "Tambah Admin" untuk membuat akun pertama.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="barisKosong" class="hidden">
                        <td colspan="7" class="px-5 py-10 text-center text-xs text-slate-400">
                            Tidak ada akun admin yang sesuai dengan pencarian atau filter.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3 text-[11px] text-slate-500">
            <span class="flex items-center gap-1.5">
                <i class="fas fa-info-circle text-slate-400"></i>
                Hanya Super Admin yang dapat membuka halaman ini.
            </span>
            <span>Hapus akun ikut mencabut passkey dan sesi login akun tersebut.</span>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var baris = Array.prototype.slice.call(document.querySelectorAll('#tabelAdmin tbody tr.users-row'));
            var inputCari = document.getElementById('cariUser');
            var pilihRole = document.getElementById('filterRole');
            var jumlahTampil = document.getElementById('jumlahTampil');
            var barisKosong = document.getElementById('barisKosong');

            function terapkanFilter() {
                var kata = (inputCari.value || '').toLowerCase().trim();
                var role = pilihRole.value;
                var tampil = 0;

                baris.forEach(function (tr) {
                    var cocok = tr.dataset.cari.indexOf(kata) !== -1 && (role === '' || tr.dataset.role === role);

                    tr.classList.toggle('hidden', !cocok);
                    if (cocok) {
                        tampil++;
                        tr.querySelector('.no-row').textContent = tampil;
                    }
                });

                jumlahTampil.textContent = tampil;
                barisKosong.classList.toggle('hidden', tampil !== 0 || baris.length === 0);
            }

            inputCari.addEventListener('input', terapkanFilter);
            pilihRole.addEventListener('change', terapkanFilter);

            /* ---------------- Salin NIP ---------------- */

            function salin(teks, sukes) {
                function gagal() {
                    AdminUI.toast('Salin otomatis butuh HTTPS. Salin manual dari layar.', 'error');
                }

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(teks).then(sukes, gagal);
                    return;
                }

                var sementara = document.createElement('textarea');
                sementara.value = teks;
                sementara.setAttribute('readonly', 'readonly');
                sementara.style.position = 'fixed';
                sementara.style.left = '-9999px';
                document.body.appendChild(sementara);
                sementara.select();

                var berhasil = false;
                try {
                    berhasil = document.execCommand('copy');
                } catch (error) {
                    berhasil = false;
                }

                document.body.removeChild(sementara);

                if (berhasil) {
                    sukes();
                } else {
                    gagal();
                }
            }

            document.querySelectorAll('[data-salin]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    var ikon = tombol.querySelector('i');

                    salin(tombol.dataset.salin, function () {
                        AdminUI.toast('NIP ' + tombol.dataset.salin + ' berhasil disalin.', 'info');

                        if (!ikon) return;

                        ikon.className = 'fas fa-check text-[11px] text-emerald-600';
                        setTimeout(function () {
                            ikon.className = 'far fa-copy text-[11px]';
                        }, 2000);
                    });
                });
            });

            /* ---------------- Hapus akun ---------------- */

            document.querySelectorAll('[data-hapus-user]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    AdminUI.confirmDelete(tombol.dataset.url, 'akun ' + tombol.dataset.nama);
                });
            });
        })();
    </script>
@endpush
