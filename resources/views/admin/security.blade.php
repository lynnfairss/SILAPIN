@extends('adminlte::page')

@section('title', 'Keamanan Akun')

@section('content_header')
    <h1>Keamanan Akun</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="row">

    {{-- 2FA --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-mobile-alt mr-2"></i>Autentikasi Dua Faktor (2FA)</h3>
            </div>
            <div class="card-body">

                @if($user->twoFactorEnabled())
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge badge-success" style="font-size:.9rem;"><i class="fas fa-check-circle mr-1"></i>Aktif</span>
                        <span class="ml-2 text-muted" style="font-size:.85rem;">Terverifikasi sejak {{ $user->two_factor_confirmed_at->format('d M Y') }}</span>
                    </div>
                    <p class="text-muted" style="font-size:.9rem;">
                        Setiap kali masuk dengan kata sandi, Anda akan diminta memasukkan kode 6 digit dari aplikasi authenticator.
                    </p>

                    <form method="POST" action="{{ route('security.2fa.disable') }}" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="code" class="form-control" inputmode="numeric"
                                   maxlength="6" placeholder="Kode OTP saat ini" required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Nonaktifkan autentikasi dua faktor?');">
                                    <i class="fas fa-unlock mr-1"></i>Nonaktifkan
                                </button>
                            </div>
                        </div>
                        @error('code')
                        <span class="text-danger" style="font-size:.8rem;">{{ $message }}</span>
                        @enderror
                    </form>

                @elseif($user->two_factor_secret)
                    <div class="alert alert-warning" style="font-size:.9rem;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Pindai QR di bawah dengan aplikasi Google Authenticator, lalu masukkan kode untuk mengaktifkan.
                    </div>

                    <div class="text-center mb-3">
                        <img src="data:image/svg+xml;base64,{{ base64_encode($user->getTwoFactorQrCode()) }}"
                             alt="QR Code 2FA" style="width:220px; height:220px; border:1px solid #dee2e6; border-radius:8px;">
                    </div>

                    <p class="text-center text-muted mb-3" style="font-size:.8rem;">
                        Tidak bisa memindai? Masukkan kunci manual:
                        <code>{{ $user->two_factor_secret }}</code>
                    </p>

                    <form method="POST" action="{{ route('security.2fa.confirm') }}">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="code" class="form-control" inputmode="numeric"
                                   maxlength="6" placeholder="Kode OTP dari aplikasi" required autofocus>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check mr-1"></i>Aktifkan
                                </button>
                            </div>
                        </div>
                        @error('code')
                        <span class="text-danger" style="font-size:.8rem;">{{ $message }}</span>
                        @enderror
                    </form>

                @else
                    <p class="text-muted" style="font-size:.9rem;">
                        Autentikasi dua faktor menambah lapisan keamanan: selain kata sandi, Anda perlu memasukkan kode
                        sekali pakai dari aplikasi <strong>Google Authenticator</strong>.
                    </p>

                    <form method="POST" action="{{ route('security.2fa.enable') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-qrcode mr-1"></i>Aktifkan 2FA
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>

    {{-- Passkey --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-fingerprint mr-2"></i>Passkey (Masuk Tanpa Kata Sandi)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" id="btnAddPasskey">
                        <i class="fas fa-plus mr-1"></i>Tambah Passkey
                    </button>
                </div>
            </div>
            <div class="card-body">

                <p class="text-muted" style="font-size:.9rem;">
                    Passkey memungkinkan Anda masuk dengan sidik jari, wajah, atau PIN perangkat — tanpa memasukkan
                    kata sandi. Daftarkan passkey dari perangkat yang Anda percaya.
                </p>

                @if($passkeys->isEmpty())
                    <div class="alert alert-info" style="font-size:.9rem;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Belum ada passkey terdaftar.
                    </div>
                @else
                    <table class="table table-bordered table-striped">
                        <thead class="text-center">
                            <tr>
                                <th>Nama</th>
                                <th>Didaftarkan</th>
                                <th width="60">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($passkeys as $key)
                            <tr>
                                <td><i class="fas fa-key mr-1 text-muted"></i>{{ $key->name }}</td>
                                <td class="text-center">{{ $key->created_at->format('d M Y H:i') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deletePasskey('{{ route('passkey.destroy', $key) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif

                <div id="passkeyAlert" class="alert alert-danger d-none mt-2" style="font-size:.85rem;"></div>

            </div>
        </div>
    </div>

</div>

@stop

@section('js')
<script src="{{ asset('vendor/webauthn/webauthn.js') }}"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function showAlert(message) {
        const alert = document.getElementById('passkeyAlert');
        alert.textContent = message;
        alert.classList.remove('d-none');
    }

    function reloadStale() {
        sessionStorage.setItem('passkey_stale', '1');
        window.location.reload();
    }

    if (sessionStorage.getItem('passkey_stale') === '1') {
        sessionStorage.removeItem('passkey_stale');
        showAlert('Sesi halaman sudah diperbarui. Silakan coba tambahkan passkey lagi.');
    }

    document.getElementById('btnAddPasskey').addEventListener('click', function () {
        const name = prompt('Nama untuk passkey ini (contoh: HP Pribadi):') || 'Passkey';

        fetch('{{ route('passkey.register.options') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        })
        .then(r => {
            if (r.status === 419) {
                reloadStale();
                return new Promise(() => {});
            }
            return r.json().then(d => ({ ok: r.ok, d }));
        })
        .then(({ ok, d }) => {
            if (!ok) {
                showAlert(d.message || 'Gagal mempersiapkan registrasi passkey.');
                return;
            }

            const webauthn = new WebAuthn();
            webauthn.register(d.publicKey, function (credential) {
                credential.name = name;
                fetch('{{ route('passkey.register') }}', {
                    method: 'POST',
                    headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
                    body: JSON.stringify(credential),
                })
                .then(r => {
            if (r.status === 419) {
                reloadStale();
                return new Promise(() => {});
            }
            return r.json().then(d => ({ ok: r.ok, d }));
        })
                .then(({ ok, d }) => {
                    if (ok && d.callback) {
                        window.location.href = d.callback;
                    } else {
                        showAlert(d.message || 'Registrasi passkey gagal.');
                    }
                })
                .catch(() => showAlert('Terjadi kesalahan saat registrasi passkey.'));
            });
        })
        .catch(() => showAlert('Terjadi kesalahan saat mempersiapkan registrasi.'));
    });

    function deletePasskey(url) {
        if (!confirm('Hapus passkey ini? Anda tidak akan bisa masuk tanpa kata sandi dari perangkat ini.')) {
            return;
        }

        fetch(url, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        })
        .then(r => {
            if (r.status === 419) {
                reloadStale();
                return new Promise(() => {});
            }
            return r.json();
        })
        .then(d => {
            if (d.result) {
                window.location.reload();
            } else {
                showAlert('Gagal menghapus passkey.');
            }
        })
        .catch(() => showAlert('Terjadi kesalahan saat menghapus passkey.'));
    }
</script>
@stop
