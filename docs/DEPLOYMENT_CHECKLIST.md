# Checklist Deployment Produksi

## Hosting dan PHP

- [ ] PHP minimal 8.2.
- [ ] Ekstensi `pdo_mysql`, `curl`, `openssl`, `fileinfo`, dan `mbstring` aktif.
- [ ] SSL/HTTPS aktif.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_URL` menggunakan domain HTTPS yang benar.
- [ ] Folder `uploads` dapat ditulis PHP.
- [ ] Folder `storage/logs` dapat ditulis PHP.

## Database

- [ ] Database dan user khusus aplikasi dibuat.
- [ ] Password database kuat.
- [ ] Installer berhasil dijalankan.
- [ ] Backup awal dibuat.
- [ ] Point demonstrasi dihapus bila diperlukan.

## Superadmin

- [ ] Email admin valid.
- [ ] Password minimal 12 karakter.
- [ ] Akun admin cadangan dibuat.
- [ ] `install.php` dihapus.

## Konten

- [ ] Logo dan hero resmi diunggah.
- [ ] Seluruh finalis dan foto diperiksa.
- [ ] Nomor finalis tidak duplikat.
- [ ] Riwayat pemenang diperiksa.
- [ ] Jadwal dan tanggal voting benar.
- [ ] FAQ, syarat, privasi, dan kontak diperiksa.

## Xendit

- [ ] Test key tersimpan di `.env` selama pengujian.
- [ ] Business ID diisi.
- [ ] Webhook token diisi.
- [ ] Kanal pembayaran merchant aktif.
- [ ] Webhook URL diuji.
- [ ] Satu transaksi QRIS test berhasil.
- [ ] Transaksi paid menambah poin satu kali.
- [ ] Webhook duplikat tidak menggandakan poin.
- [ ] Transaksi expired tidak menambah poin.
- [ ] Live key baru dipasang setelah seluruh pengujian lulus.

## Operasional

- [ ] Cron rekonsiliasi aktif setiap 10–15 menit.
- [ ] Backup database harian aktif.
- [ ] Tim memiliki prosedur penanganan transaksi pending.
- [ ] Tim memiliki prosedur koreksi poin dan bukti pendukung.
- [ ] Freeze leaderboard telah diuji.
- [ ] Kontak bantuan pemilih aktif.
