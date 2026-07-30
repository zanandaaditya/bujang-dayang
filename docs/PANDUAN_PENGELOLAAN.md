# Panduan Pengelolaan Website

## 1. Ringkasan Role

Website menggunakan dua role:

1. **User/publik** — tidak wajib membuat akun. User dapat melihat finalis, leaderboard, pemenang, membuat transaksi vote, dan mengecek status pembayaran.
2. **Superadmin** — masuk melalui `/admin/login.php` dan mengelola seluruh data serta konfigurasi operasional.

## 2. Urutan Persiapan Event

1. Masuk sebagai Superadmin.
2. Buka **Event** dan atur nama, tahun, tema, waktu mulai, waktu tutup, serta status.
3. Buka **Finalis** dan masukkan seluruh finalis Bujang/Dayang.
4. Buka **Paket Voting** dan periksa nominal, poin dasar, bonus, serta total poin.
5. Buka **Konten** untuk mengganti hero, deskripsi, kontak, jadwal, FAQ, dan sponsor.
6. Uji satu transaksi menggunakan Xendit test mode.
7. Setelah pengujian selesai, ubah status event menjadi `VOTING_ACTIVE`.

## 3. Status Event

- `DRAFT`: belum tampil sebagai event aktif.
- `PUBLISHED`: informasi event dapat dipublikasikan, tetapi voting belum aktif.
- `VOTING_ACTIVE`: user dapat membuat transaksi vote.
- `VOTING_CLOSED`: pembuatan transaksi baru dihentikan.
- `ARCHIVED`: event disimpan sebagai arsip.

## 4. Pengelolaan Finalis

Data finalis memuat kelompok, nomor, nama, kabupaten/kota, foto, biodata, motto, pendidikan, program/gagasan, Instagram, status aktif, dan penanda unggulan.

Ketentuan penting:

- Nomor finalis harus unik untuk kelompok dan event yang sama.
- Nonaktifkan finalis untuk menghentikan penerimaan vote baru tanpa menghapus histori.
- Gunakan foto vertikal berkualitas, disarankan rasio 4:5 dan ukuran di bawah 5 MB.
- Jangan menghapus finalis yang sudah memiliki transaksi.

## 5. Pengelolaan Paket Voting

Setiap paket memiliki nominal pembayaran, poin dasar, bonus poin, total poin, badge, urutan, dan status aktif.

Saat transaksi dibuat, sistem menyimpan snapshot paket. Perubahan paket setelah transaksi tidak mengubah transaksi lama.

## 6. Pengelolaan Transaksi

Status transaksi:

- `CREATED`: order tercatat, session Xendit belum berhasil dilampirkan.
- `PENDING`: menunggu pembayaran.
- `PAID`: pembayaran terkonfirmasi dan poin masuk ke ledger.
- `FAILED`: pembayaran gagal.
- `EXPIRED`: waktu pembayaran habis.
- `CANCELED`: transaksi dibatalkan.
- `REFUNDED`/`REVERSED`: digunakan untuk penanganan khusus dan harus disertai jurnal pembalik.

Gunakan filter transaksi untuk mencari nomor transaksi, finalis, status, atau tanggal. Ekspor CSV tersedia untuk rekonsiliasi dan pelaporan.

## 7. Leaderboard dan Freeze

Leaderboard publik dihitung dari `point_ledgers`, tetapi User hanya melihat persentase dukungan per kelompok dan bukan angka internal. Transaksi pending tidak dihitung. Ketika opsi **Bekukan tampilan publik** diaktifkan, sistem membuat snapshot Bujang dan Dayang. Dashboard Superadmin tetap dapat melihat posisi live.

Untuk membuka kembali leaderboard real-time, nonaktifkan opsi freeze. Snapshot lama tetap tersimpan sebagai jejak historis.

## 8. Penyesuaian Poin

Penyesuaian tidak mengubah angka total secara langsung. Superadmin membuat jurnal `ADJUSTMENT` yang berisi finalis, nilai positif/negatif, alasan, akun pelaksana, dan waktu perubahan.

Contoh:

- `+10.000` untuk koreksi kekurangan poin.
- `-60.000` untuk pembalikan transaksi yang dinyatakan tidak sah.

Setiap koreksi harus memiliki alasan operasional dan bukti pendukung.

## 9. Webhook dan Rekonsiliasi

Webhook Xendit menjadi sumber konfirmasi utama. Log webhook dapat diperiksa pada menu **Webhook Xendit**. Bila transaksi tetap pending meskipun sudah dibayar, jalankan cron rekonsiliasi atau periksa session dari dashboard/penyedia pembayaran.

Jangan mengubah transaksi menjadi `PAID` hanya berdasarkan tangkapan layar user. Pastikan status benar-benar terverifikasi oleh Xendit.

## 10. Penutupan Voting

1. Pastikan waktu penutupan benar.
2. Aktifkan freeze bila ingin menyembunyikan pergerakan akhir.
3. Ubah status menjadi `VOTING_CLOSED` setelah masa vote berakhir.
4. Biarkan masa toleransi pembayaran berjalan sesuai kebijakan.
5. Jalankan rekonsiliasi final.
6. Ekspor transaksi dan leaderboard.
7. Buat backup database.
8. Kunci hasil akhir melalui snapshot.

## 11. Backup dan Audit

- Lakukan backup database harian saat voting aktif.
- Simpan backup sebelum dan sesudah penutupan.
- Jangan menghapus transaksi, ledger, webhook, atau audit log.
- Batasi akses dashboard hanya kepada petugas yang ditunjuk.
- Ganti password akun yang sudah tidak digunakan.


## Persentase Publik

Halaman User hanya menampilkan persentase. Angka internal tetap dapat dilihat Superadmin untuk audit. Persentase Bujang dihitung terhadap total dukungan sah Bujang, sedangkan persentase Dayang dihitung terhadap total dukungan sah Dayang.
