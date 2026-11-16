# Deploy SILAPIN ke InfinityFree

Panduan upload paket deploy ke hosting InfinityFree. Database `if0_43453736_silapin`
sudah diimport, jadi tidak ada langkah import SQL di sini.

## 1. Isi paket

| File | Isi |
|---|---|
| `silapin-app.zip` | Aplikasi Laravel lengkap: `app/`, `bootstrap/`, `config/`, `database/`, `lang/`, `resources/`, `routes/`, `storage/`, `vendor/`, `artisan`, `composer.json`, `composer.lock` |
| `silapin-public.zip` | Isi folder `public/` + file `index.php` & `.htaccess` versi hosting + folder `storage/` berisi berkas upload lama |
| `.env.production.example` | Template `.env` untuk hosting |
| `htdocs/index.php` | Entry point hosting (sudah ada di dalam `silapin-public.zip`) |
| `htdocs/.htaccess` | Rewrite Laravel (sudah ada di dalam `silapin-public.zip`) |

## 2. Struktur akhir di server

```
/ (root akun InfinityFree)
├── htdocs/                  <- isi silapin-public.zip
│   ├── index.php
│   ├── .htaccess
│   ├── build/ css/ js/ images/ templates/ vendor/
│   └── storage/             <- berkas upload (foto KTP, surat, inventaris)
└── silapin/                 <- isi silapin-app.zip
    ├── app/ bootstrap/ config/ database/ lang/ resources/ routes/
    ├── storage/ vendor/ artisan composer.json composer.lock
    └── .env                 <- dibuat dari .env.production.example
```

`.env` **wajib** di `silapin/.env`, jangan di `htdocs/.env`.

## 3. Upload lewat FileZilla

1. Buka panel InfinityFree -> **FTP Accounts**, catat:
   - Host: `ftpupload.net`
   - Username: `if0_43453736`
   - Password: password FTP (bukan password database)
   - Port: `21`
2. Sambungkan, lalu masuk ke root akun (di situ terlihat folder `htdocs`).
3. Upload `silapin-app.zip` dan `silapin-public.zip` ke root akun (sejajar `htdocs`).
4. Extract kedua zip lewat **File Manager** InfinityFree (tombol kanan -> Extract), atau
   lewat FileZilla jika mendukung.
   - `silapin-app.zip` -> menghasilkan folder `silapin/`
   - `silapin-public.zip` -> extract ke dalam `htdocs/`
5. Hapus kedua file zip setelah selesai.

Kalau tidak ada fitur extract: extract di komputer, lalu upload foldernya satu per satu
(vendor paling lama, ±14.700 file).

## 4. Buat `.env`

1. Buka `.env.production.example`, ganti semua `GANTI-...`:
   - `APP_URL` -> `https://domain-anda.infinityfreeapp.com`
   - `DB_PASSWORD` -> password database dari panel (MySQL Password)
   - `PASSKEYS_RELYING_PARTY_ID` -> domain tanpa `https://`
   - `PASSKEYS_ALLOWED_ORIGINS` -> domain dengan `https://`
   - `MAIL_FROM_ADDRESS` -> alamat email domain Anda
2. Simpan sebagai `silapin/.env` di server.
3. Kalau **Free SSL belum aktif**, ubah `SESSION_SECURE_COOKIE=false` dulu.

## 5. Aktifkan Free SSL

Panel InfinityFree -> **SSL Certificates** -> pilih domain -> install.
Tunggu sampai `https://domain-anda` bisa dibuka tanpa peringatan, baru set
`SESSION_SECURE_COOKIE=true`. Passkey/WebAuthn hanya berfungsi di HTTPS.

## 6. Verifikasi

| Cek | Harapan |
|---|---|
| `https://domain-anda/` | Redirect ke halaman login |
| `/login/admin` | Tampilan login biru tampil, CSS/JS termuat |
| `/login/superadmin` | Sama, tombol passkey muncul |
| Login `superadmin@gmail.com` | Masuk ke `/dashboard` |
| Upload foto KTP di form peminjaman | Berhasil, file bisa dibuka di `/storage/foto-ktp/...` |
| Halaman inventaris | Foto tampil |
| phpMyAdmin | 21 tabel ada di `if0_43453736_silapin` |
| Console browser | Tidak ada error 404 asset |

## 7. Troubleshooting

**HTTP 500 / halaman putih**
- Pastikan folder bernama tepat `silapin` (huruf kecil) sejajar `htdocs`.
- Pastikan `silapin/vendor/autoload.php` ada.
- Hapus `silapin/bootstrap/cache/config.php` bila ada.
- Sementara set `APP_DEBUG=true` untuk melihat error, lalu kembalikan `false`.

**Error 419 saat login**
- `SESSION_SECURE_COOKIE=true` padahal situs masih `http://`. Set `false`
  sampai SSL aktif.
- Pastikan `silapin/storage/framework/sessions` ada dan bisa ditulis.

**CSS/JS tidak termuat**
- Pastikan `htdocs/build/manifest.json` dan `htdocs/build/assets/` ada.
- Jangan upload `public/hot` (sudah dikecualikan dari paket).
- `APP_URL` harus domain hosting, bukan `localhost`.

**Gambar upload 404**
- Pastikan folder `htdocs/storage/` berisi `foto-ktp/`, `surat-tugas/`,
  `bukti-pengembalian/`, `inventaris/`.
- Disk `public` sekarang menulis ke `public_path('storage')` = `htdocs/storage`,
  jadi tidak butuh symlink.

**Passkey tidak bisa didaftarkan**
- Harus HTTPS valid.
- `PASSKEYS_RELYING_PARTY_ID` harus sama persis dengan domain (tanpa `https://`).
- `PASSKEYS_ALLOWED_ORIGINS` harus memakai `https://`.

## 8. Keamanan

- Password database sempat terlihat di screenshot. Setelah aplikasi jalan,
  ganti password di panel InfinityFree lalu update `DB_PASSWORD` di `silapin/.env`.
- Jangan taruh `.env` di `htdocs`.
- Jangan commit `.env` ke Git.
