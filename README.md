# Website E-Voting Bujang Dayang Bangka Belitung

Paket website lengkap berbasis **PHP Native**, **MySQL**, Bootstrap, dan Xendit Payment Session. Antarmuka menggunakan **emerald sebagai warna primer** dan **gold sebagai warna sekunder**.

## Modul yang Tersedia

### Portal Publik

- Beranda dinamis berdasarkan status event.
- Hero, countdown, statistik transaksi, dan tiga besar sementara.
- Daftar finalis Bujang dan Dayang.
- Profil lengkap finalis.
- Modal e-voting dan pilihan paket dukungan; nilai internal tidak ditampilkan kepada User.
- Hosted Checkout Xendit untuk QRIS/dompet digital yang aktif.
- Halaman status pembayaran dengan polling otomatis.
- Cek transaksi menggunakan nomor transaksi dan nomor telepon.
- Leaderboard Bujang dan Dayang.
- Perjalanan pemenang tahun ke tahun.
- Jadwal kegiatan, FAQ, sponsor, syarat, dan kebijakan privasi.

### Dashboard Superadmin

- Login Superadmin.
- Ringkasan pendapatan, poin, transaksi pending, dan jumlah finalis.
- Grafik pendapatan 14 hari.
- Manajemen event dan periode voting.
- Manajemen finalis Bujang/Dayang.
- Manajemen pemenang.
- Manajemen paket voting dan bonus poin.
- Daftar transaksi serta ekspor CSV.
- Leaderboard dan penyesuaian poin berbasis ledger.
- Konten beranda, jadwal, FAQ, serta sponsor.
- Manajemen akun Superadmin.
- Audit log.
- Log webhook Xendit.
- Halaman pemeriksaan konfigurasi sistem.

## Persyaratan Server

- PHP 8.2 atau lebih baru; kompatibel dengan PHP 8.5.
- Ekstensi PHP: `pdo_mysql`, `curl`, `openssl`, `fileinfo`, `mbstring`.
- MySQL 8 atau MariaDB yang kompatibel.
- HTTPS aktif untuk penggunaan Xendit produksi.
- Apache dengan `.htaccess` atau konfigurasi proteksi file setara.

## Instalasi di Hostinger

### 1. Unggah File

Ekstrak seluruh isi paket ke folder domain, misalnya:

```text
public_html/
```

Domain harus mengarah ke folder yang berisi `index.php`.

### 2. Buat Database

Di hPanel Hostinger:

1. Buka **Databases → Management**.
2. Buat database MySQL.
3. Buat pengguna database.
4. Hubungkan pengguna ke database.
5. Catat host, port, nama database, username, dan password.

### 3. Buat File `.env`

Salin `.env.example` menjadi `.env`, kemudian isi:

```dotenv
APP_NAME="Bujang Dayang Bangka Belitung"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.com
APP_TIMEZONE=Asia/Jakarta
APP_INSTALL_KEY=kunci-instalasi-yang-panjang
APP_ENCRYPTION_KEY=base64:KUNCI_32_BYTE_ANDA
CRON_SECRET=kunci-cron-yang-panjang

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database

XENDIT_API_URL=https://api.xendit.co
XENDIT_SECRET_KEY=xnd_development_atau_production_key
XENDIT_BUSINESS_ID=business_id_xendit
XENDIT_WEBHOOK_TOKEN=token_webhook_xendit
XENDIT_ALLOWED_CHANNELS=QRIS,DANA,OVO,SHOPEEPAY,LINKAJA
PAYMENT_EXPIRY_MINUTES=30
```

Untuk membuat encryption key melalui komputer yang memiliki PHP:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

### 4. Jalankan Installer

Buka:

```text
https://domainanda.com/install.php?key=KUNCI_APP_INSTALL_KEY
```

Masukkan nama, email, dan password Superadmin. Installer akan:

- Membuat seluruh tabel.
- Memasukkan tujuh kabupaten/kota.
- Membuat event contoh.
- Membuat finalis dan data leaderboard contoh.
- Membuat paket voting Rp10.000 sampai Rp5.000.000.
- Membuat akun Superadmin.

Setelah berhasil, **hapus atau ubah nama `install.php`**.

### 5. Masuk Dashboard

```text
https://domainanda.com/admin/login.php
```

Data finalis, pemenang, tanggal voting, paket dukungan, dan konten website dapat diganti dari dashboard.

## Konfigurasi Xendit

Website menggunakan **Payment Session** dengan mode `PAYMENT_LINK`. Server membuat session melalui `POST /sessions`, lalu pemilih diarahkan ke Hosted Checkout Xendit.

Dokumentasi resmi:

- Create Session: `https://docs.xendit.co/apidocs/create-session`
- Get Session: `https://docs.xendit.co/apidocs/get-session`
- Payment Session Overview: `https://docs.xendit.co/docs/payment-sessions-overview/`
- Handling Webhooks: `https://docs.xendit.co/docs/handling-webhooks`

### Webhook URL

Gunakan URL:

```text
https://domainanda.com/webhook/xendit.php
```

Aktifkan minimal:

- Payment Session Completed.
- Payment Session Expired.
- Payment succeeded/capture.
- Payment failure.

Endpoint memverifikasi webhook dengan dua mekanisme:

1. `x-callback-token` untuk Payments API.
2. Pemeriksaan ulang status Session ke API Xendit untuk event Payment Session.

Poin hanya dicatat setelah pembayaran terverifikasi. Redirect halaman sukses tidak pernah digunakan sebagai bukti pembayaran.

### Kanal Pembayaran

Kanal yang muncul bergantung pada kanal yang telah aktif di akun Xendit. Daftar pada `XENDIT_ALLOWED_CHANNELS` hanya membatasi kanal yang diizinkan; kode kanal harus disesuaikan dengan akun merchant.

## Rekonsiliasi Transaksi Pending

Webhook menjadi mekanisme utama. Sebagai cadangan, tersedia:

```text
/cron/reconcile.php?secret=CRON_SECRET
```

Atur Cron Job Hostinger, misalnya setiap 10 menit:

```bash
curl -fsS "https://domainanda.com/cron/reconcile.php?secret=KUNCI_CRON" >/dev/null
```

Cron memeriksa maksimal 100 session pending pada setiap eksekusi.

## Aturan Poin

- `CREATED` dan `PENDING`: tersimpan di admin, tidak masuk leaderboard.
- `PAID`: total poin paket masuk ke `point_ledgers`.
- `FAILED`, `EXPIRED`, `CANCELED`: tidak masuk leaderboard.
- Koreksi admin tidak mengubah transaksi; sistem membuat jurnal `ADJUSTMENT`.
- Webhook duplikat tidak menggandakan poin karena terdapat unique constraint dan pemeriksaan idempotensi.

## Paket Poin Contoh

| Pembayaran | Poin Dasar | Bonus | Total |
|---:|---:|---:|---:|
| Rp10.000 | 10.000 | 0 | 10.000 |
| Rp20.000 | 20.000 | 0 | 20.000 |
| Rp50.000 | 50.000 | 10.000 | 60.000 |
| Rp100.000 | 100.000 | 25.000 | 125.000 |
| Rp250.000 | 250.000 | 75.000 | 325.000 |
| Rp500.000 | 500.000 | 175.000 | 675.000 |
| Rp1.000.000 | 1.000.000 | 400.000 | 1.400.000 |
| Rp2.500.000 | 2.500.000 | 1.250.000 | 3.750.000 |
| Rp5.000.000 | 5.000.000 | 3.000.000 | 8.000.000 |

Semua paket dapat diubah dari dashboard.

## Skema Warna

```css
--emerald-950: #022c22;
--emerald-900: #064e3b;
--emerald-700: #047857;
--emerald-600: #059669;
--gold-700:    #9a6b16;
--gold-500:    #d4af37;
--gold-400:    #e4c65b;
```

## Keamanan yang Sudah Diterapkan

- PDO prepared statements.
- CSRF token.
- `password_hash()` dan `password_verify()`.
- Cookie `HttpOnly`, `Secure`, dan `SameSite=Lax`.
- Enkripsi AES-256-GCM untuk nomor telepon jika `APP_ENCRYPTION_KEY` diisi.
- Hash nomor telepon untuk pencarian dan deteksi pola.
- Rate limit pembuatan transaksi.
- Validasi MIME dan ukuran upload.
- Pemblokiran eksekusi PHP pada folder upload.
- Secret API Xendit hanya digunakan di server.
- Webhook verification dan idempotensi.
- Point ledger dan audit log.
- Database transaction saat konfirmasi pembayaran.

## Sebelum Website Dipublikasikan

1. Ganti seluruh data finalis contoh.
2. Ganti riwayat pemenang contoh.
3. Ganti email, WhatsApp, alamat, dan media sosial.
4. Atur tanggal voting.
5. Hapus semua point ledger berdeskripsi `Data demonstrasi awal` jika leaderboard harus dimulai dari nol.
6. Uji Xendit menggunakan test key.
7. Uji webhook completed, expired, failure, dan duplikat.
8. Pastikan `APP_DEBUG=false`.
9. Pastikan domain menggunakan HTTPS.
10. Hapus `install.php`.
11. Backup database sebelum membuka voting.

## Struktur Folder

```text
app/                 Logika aplikasi dan layanan
admin/               Dashboard Superadmin
api/                 Endpoint JSON publik
assets/              CSS, JavaScript, dan gambar
config/              Konfigurasi aplikasi
cron/                Rekonsiliasi transaksi
database/            Skema dan data awal
uploads/              Foto finalis, pemenang, sponsor
views/                Partial header, footer, modal
webhook/              Penerima webhook Xendit
index.php             Beranda
winners.php           Riwayat pemenang
evoting.php           Daftar finalis dan voting
leaderboard.php       Peringkat Bujang dan Dayang
```

## Catatan Data Contoh

Nama finalis dan pemenang dalam installer merupakan **data demonstrasi**, bukan data resmi. Ganti seluruhnya sebelum publikasi.
