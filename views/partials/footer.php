</main>
<footer class="site-footer pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row g-4 pb-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="<?= asset('images/logo.svg') ?>" alt="Logo" width="62" height="62">
                    <div><h4 class="mb-0 text-white">Bujang Dayang</h4><span class="text-gold">Bangka Belitung</span></div>
                </div>
                <p class="text-white-50 mb-3"><?= e(setting('footer_description', 'Ruang apresiasi generasi muda terbaik yang membawa budaya, pariwisata, kreativitas, dan keramahan Kepulauan Bangka Belitung.')) ?></p>
                <div class="d-flex gap-2 social-links">
                    <a href="<?= e(setting('instagram_url', '#')) ?>" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="<?= e(setting('youtube_url', '#')) ?>" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="<?= e(setting('tiktok_url', '#')) ?>" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Navigasi</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= url('index.php') ?>">Beranda</a></li>
                    <li><a href="<?= url('winners.php') ?>">Nama Pemenang</a></li>
                    <li><a href="<?= url('evoting.php') ?>">E-Voting</a></li>
                    <li><a href="<?= url('leaderboard.php') ?>">Leaderboard</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Informasi</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= url('terms.php') ?>">Syarat & Ketentuan</a></li>
                    <li><a href="<?= url('privacy.php') ?>">Kebijakan Privasi</a></li>
                    <li><a href="<?= url('payment-check.php') ?>">Cek Pembayaran</a></li>
                    <li><a href="<?= url('admin/login.php') ?>">Superadmin</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="footer-title">Kontak Penyelenggara</h6>
                <p class="text-white-50 small mb-2"><i class="bi bi-envelope me-2 text-gold"></i><?= e(setting('contact_email', 'panitia@bujangdayangbabel.id')) ?></p>
                <p class="text-white-50 small mb-2"><i class="bi bi-whatsapp me-2 text-gold"></i><?= e(setting('contact_phone', '+62 812-0000-0000')) ?></p>
                <p class="text-white-50 small"><i class="bi bi-geo-alt me-2 text-gold"></i><?= e(setting('contact_address', 'Pangkal Pinang, Kepulauan Bangka Belitung')) ?></p>
            </div>
        </div>
        <div class="border-top border-light border-opacity-10 pt-3 d-flex flex-wrap justify-content-between gap-2 small text-white-50">
            <span>© <?= date('Y') ?> Bujang Dayang Bangka Belitung.</span>
            <span>Pembayaran diproses melalui Xendit.</span>
        </div>
    </div>
</footer>
<a class="mobile-vote-bar d-lg-none" href="<?= url('evoting.php') ?>"><i class="bi bi-hand-index-thumb"></i> Vote Finalis</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
