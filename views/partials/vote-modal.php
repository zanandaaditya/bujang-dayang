<?php
$event = $event ?? active_event();
$packages = $packages ?? [];
if ($event && !$packages) {
    $stmt = db()->prepare('SELECT * FROM vote_packages WHERE event_id = ? AND is_active = 1 ORDER BY sort_order, amount');
    $stmt->execute([$event['id']]);
    $packages = $stmt->fetchAll();
}
?>
<div class="modal fade vote-modal" id="voteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header px-4 py-3">
                <div>
                    <span class="small text-uppercase text-white-50">Konfirmasi Dukungan</span>
                    <h4 class="mb-0 text-white">Vote Finalis Pilihan Anda</h4>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="<?= url('vote-process.php') ?>" method="post">
                <div class="modal-body p-4 p-lg-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="finalist_id" value="">

                    <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-light mb-4">
                        <img data-modal-photo src="<?= asset('images/placeholder-bujang.svg') ?>" width="82" height="96" class="rounded-3 object-fit-cover" alt="Finalis">
                        <div>
                            <span class="badge bg-success-subtle text-success-emphasis mb-2"><span data-modal-category>Finalis</span> • No. <span data-modal-number>-</span></span>
                            <h5 data-modal-name class="mb-1">Pilih finalis</h5>
                            <div class="text-secondary small"><i class="bi bi-geo-alt me-1"></i><span data-modal-region>-</span></div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">1. Data Pemilih</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control form-control-lg" name="voter_name" maxlength="100" required placeholder="Nama pemilih">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon/WhatsApp *</label>
                            <input type="tel" class="form-control form-control-lg" name="voter_phone" required placeholder="08xxxxxxxxxx">
                            <div class="form-text">Digunakan untuk identifikasi transaksi dan tidak ditampilkan kepada publik.</div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">2. Pilih Paket Dukungan</h6>
                    <div class="row g-3 mb-4">
                        <?php foreach ($packages as $package): ?>
                            <?php $bonusPercent = bonus_percentage($package['base_points'], $package['bonus_points']); ?>
                            <div class="col-md-6">
                                <label class="package-option position-relative w-100">
                                    <input type="radio" name="package_id" value="<?= (int) $package['id'] ?>" required>
                                    <span class="package-box d-block h-100">
                                        <span class="d-flex justify-content-between align-items-start gap-2">
                                            <strong><?= e($package['name']) ?></strong>
                                            <?php if ($package['badge']): ?><span class="badge text-bg-warning"><?= e($package['badge']) ?></span><?php endif; ?>
                                        </span>
                                        <span class="d-block fs-4 fw-bold text-emerald my-2"><?= rupiah($package['amount']) ?></span>
                                        <?php if ($bonusPercent > 0): ?>
                                            <span class="percentage-pill"><i class="bi bi-stars"></i>Bonus dukungan +<?= support_percentage($bonusPercent, $bonusPercent === floor($bonusPercent) ? 0 : 2) ?></span>
                                        <?php else: ?>
                                            <span class="small text-secondary"><i class="bi bi-check-circle me-1"></i>Paket dukungan reguler</span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="alert alert-success-subtle border-0 rounded-4 small mb-4">
                        <i class="bi bi-pie-chart-fill me-2"></i>Leaderboard publik menampilkan persentase dukungan, bukan nilai perhitungan internal.
                    </div>

                    <h6 class="fw-bold mb-3">3. Metode Pembayaran</h6>
                    <div class="p-3 rounded-4 border mb-4 d-flex align-items-center gap-3">
                        <div class="icon-box flex-shrink-0"><i class="bi bi-qr-code"></i></div>
                        <div><strong>QRIS dan Dompet Digital</strong><div class="small text-secondary">Pilihan kanal aktif akan ditampilkan di halaman pembayaran Xendit.</div></div>
                    </div>

                    <h6 class="fw-bold mb-3">4. Pernyataan Persetujuan</h6>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="consent_vote" value="1" id="consentVote" required>
                        <label class="form-check-label small" for="consentVote">Saya menyatakan bahwa vote ini dilakukan secara sadar dan sukarela, serta memahami bahwa jumlah vote tidak menjamin kandidat menjadi juara 1.</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="consent_refund" value="1" id="consentRefund" required>
                        <label class="form-check-label small" for="consentRefund">Saya menyetujui bahwa pembayaran yang telah dilakukan tidak dapat dikembalikan dengan alasan apa pun, kecuali ditentukan lain oleh penyelenggara atau penyedia pembayaran.</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="consent_privacy" value="1" id="consentPrivacy" required>
                        <label class="form-check-label small" for="consentPrivacy">Saya menyetujui pemrosesan nama dan nomor telepon untuk pencatatan transaksi, verifikasi pembayaran, serta pencegahan penyalahgunaan sistem.</label>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gold rounded-pill px-4"><i class="bi bi-shield-lock me-2"></i>Lanjutkan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
