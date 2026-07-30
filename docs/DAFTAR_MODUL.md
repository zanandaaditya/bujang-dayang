# Daftar Modul dan Halaman

## Portal Publik

| Halaman | File | Fungsi |
|---|---|---|
| Beranda | `index.php` | Hero, status event, top 3, finalis pilihan, jadwal, FAQ, sponsor |
| Nama Pemenang | `winners.php` | Timeline pemenang Bujang dan Dayang per tahun |
| E-Voting | `evoting.php` | Daftar seluruh finalis, filter, pencarian, modal vote |
| Profil Finalis | `finalist.php` | Biodata, gagasan, asal daerah, tombol vote |
| Leaderboard | `leaderboard.php` | Podium top 3 dan peringkat lengkap Bujang/Dayang |
| Proses Vote | `vote-process.php` | Validasi data, pembuatan order, pembuatan Payment Session |
| Status Pembayaran | `payment-status.php` | Status transaksi dan polling otomatis |
| Cek Pembayaran | `payment-check.php` | Pencarian order menggunakan nomor transaksi dan telepon |
| Pembayaran Dibatalkan | `payment-cancel.php` | Informasi ketika user kembali dari checkout |
| Syarat | `terms.php` | Ketentuan e-voting dan pembayaran |
| Privasi | `privacy.php` | Penjelasan pengolahan data user |

## Dashboard Superadmin

| Halaman | File | Fungsi |
|---|---|---|
| Dashboard | `admin/index.php` | KPI, grafik, top finalis, transaksi terbaru |
| Event | `admin/events.php` | Periode, status, mode beranda, freeze leaderboard |
| Finalis | `admin/finalists.php` | CRUD finalis dan unggah foto |
| Pemenang | `admin/winners.php` | Riwayat pemenang dari tahun ke tahun |
| Paket Voting | `admin/packages.php` | Nominal, poin dasar, bonus, badge |
| Transaksi | `admin/transactions.php` | Pencarian, filter, detail, ekspor |
| Leaderboard | `admin/leaderboard.php` | Peringkat live dan penyesuaian ledger |
| Konten | `admin/content.php` | Hero, profil acara, jadwal, sponsor, kontak |
| FAQ | `admin/faqs.php` | Pertanyaan dan jawaban publik |
| Akun | `admin/users.php` | Akun Superadmin |
| Webhook | `admin/webhooks.php` | Log penerimaan dan pemrosesan webhook |
| Audit Log | `admin/audit-logs.php` | Jejak perubahan administratif |
| Pengaturan | `admin/settings.php` | Pemeriksaan konfigurasi aplikasi dan Xendit |

## Endpoint Sistem

| Endpoint | Metode | Fungsi |
|---|---|---|
| `api/leaderboard.php` | GET | Data JSON leaderboard |
| `api/payment-status.php` | GET | Status order untuk polling user |
| `webhook/xendit.php` | POST | Penerimaan event pembayaran Xendit |
| `cron/reconcile.php` | GET/CLI | Rekonsiliasi transaksi pending |
