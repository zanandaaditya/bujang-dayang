# Pembaruan Tampilan Publik Menjadi Persentase

Versi 1.1.0 mengubah seluruh indikator dukungan pada halaman User menjadi persentase. Nilai internal tetap disimpan dan hanya digunakan oleh server serta dashboard Superadmin.

## Halaman yang Diubah

- Beranda
- Leaderboard
- Daftar E-Voting
- Profil finalis
- Modal paket dukungan
- Status pembayaran
- API leaderboard publik
- Syarat dan ketentuan
- Kebijakan privasi

## Rumus Persentase

Persentase finalis dihitung terhadap total dukungan sah pada kelompok yang sama. Kelompok Bujang dan Dayang masing-masing memiliki total 100% ketika sudah terdapat dukungan sah.

## Instalasi Lama

Tidak diperlukan perubahan struktur database. Kolom internal tetap dipertahankan untuk audit. Untuk memperbarui FAQ bawaan pada database lama, ubah redaksi melalui menu Superadmin > FAQ atau jalankan SQL berikut:

```sql
UPDATE faqs
SET answer = 'Pilih finalis, isi nama dan nomor telepon, pilih paket dukungan, setujui ketentuan, lalu selesaikan pembayaran melalui Xendit.'
WHERE question = 'Bagaimana cara melakukan voting?';

UPDATE faqs
SET question = 'Kapan dukungan masuk ke leaderboard?',
    answer = 'Persentase dukungan diperbarui setelah pembayaran berhasil dan webhook Xendit dikonfirmasi oleh server. Transaksi pending tidak dihitung.'
WHERE question = 'Kapan poin masuk ke leaderboard?';
```
