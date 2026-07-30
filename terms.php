<?php
require __DIR__ . '/app/bootstrap.php';
$pageTitle = 'Syarat dan Ketentuan';
require __DIR__ . '/views/partials/header.php';
?>
<section class="section-space">
    <article class="legal-page content-card p-4 p-lg-5">
        <p class="lead">Dengan melakukan voting, pemilih dianggap telah membaca, memahami, dan menyetujui ketentuan berikut.</p>

        <h2>1. Ketentuan Umum</h2>
        <p>E-voting merupakan sarana dukungan masyarakat kepada finalis Bujang Dayang Bangka Belitung. Persentase dukungan e-voting tidak secara otomatis menentukan juara utama dan dapat menjadi salah satu unsur penilaian sesuai keputusan penyelenggara.</p>

        <h2>2. Pembuatan Transaksi</h2>
        <p>Pemilih wajib memilih finalis, mengisi nama dan nomor telepon yang benar, menentukan paket dukungan, serta menyetujui seluruh pernyataan sebelum diarahkan ke halaman pembayaran.</p>

        <h2>3. Penghitungan Persentase Dukungan</h2>
        <p>Dukungan hanya dihitung setelah pembayaran dinyatakan berhasil dan dikonfirmasi melalui webhook penyedia pembayaran. Transaksi berstatus menunggu, gagal, kedaluwarsa, atau dibatalkan tidak memengaruhi leaderboard.</p>
        <p>Persentase setiap finalis dihitung terhadap total dukungan sah pada kelompok yang sama. Persentase Bujang dihitung hanya dari seluruh finalis Bujang, sedangkan persentase Dayang dihitung hanya dari seluruh finalis Dayang.</p>

        <h2>4. Pembayaran dan Pengembalian Dana</h2>
        <p>Pembayaran diproses melalui Xendit menggunakan kanal yang tersedia pada akun merchant. Pembayaran yang berhasil pada prinsipnya tidak dapat dikembalikan, kecuali terdapat transaksi ganda, gangguan sistem yang terverifikasi, kewajiban hukum, atau keputusan resmi penyelenggara.</p>

        <h2>5. Integritas Sistem</h2>
        <p>Penyelenggara berhak meninjau transaksi tidak wajar, aktivitas otomatis, penggunaan data palsu, manipulasi, atau upaya mengganggu sistem. Dukungan yang terbukti tidak sah dapat dibatalkan melalui jurnal koreksi yang terdokumentasi.</p>

        <h2>6. Penutupan Voting</h2>
        <p>Transaksi baru tidak dapat dibuat setelah waktu penutupan. Transaksi yang dibuat sebelum penutupan dapat memperoleh masa penyelesaian pembayaran sesuai konfigurasi sistem.</p>

        <h2>7. Keputusan Penyelenggara</h2>
        <p>Keputusan penyelenggara terkait validitas transaksi, penghitungan persentase dukungan, dan hasil akhir bersifat final dengan tetap memperhatikan transparansi, bukti transaksi, serta ketentuan yang berlaku.</p>

        <h2>8. Perubahan Ketentuan</h2>
        <p>Ketentuan dapat diperbarui untuk menyesuaikan kebutuhan operasional, kebijakan penyedia pembayaran, atau peraturan. Versi terbaru ditampilkan pada halaman ini.</p>

        <p class="small text-secondary mt-5">Terakhir diperbarui: <?= indonesia_date(date('Y-m-d')) ?></p>
    </article>
</section>
<?php require __DIR__ . '/views/partials/footer.php'; ?>
