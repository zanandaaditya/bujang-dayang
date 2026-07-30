<?php
require __DIR__ . '/app/bootstrap.php';

$event = active_event();
$pageTitle = 'E-Voting Finalis';
$pageDescription = 'Pilih dan dukung finalis Bujang Dayang Bangka Belitung melalui pembayaran QRIS dan dompet digital.';
$finalists = [];
$packages = [];
$percentageByFinalist = [];

if ($event) {
    $stmt = db()->prepare(
        'SELECT f.*, r.name AS region_name
         FROM finalists f
         JOIN regions r ON r.id = f.region_id
         WHERE f.event_id = ? AND f.is_active = 1
         ORDER BY f.category, f.contestant_number'
    );
    $stmt->execute([$event['id']]);
    $finalists = $stmt->fetchAll();

    foreach (['BUJANG', 'DAYANG'] as $category) {
        foreach (LeaderboardService::publicRankings((int) $event['id'], $category) as $row) {
            $percentageByFinalist[(int) $row['id']] = (float) $row['support_percentage'];
        }
    }

    $stmt = db()->prepare('SELECT * FROM vote_packages WHERE event_id=? AND is_active=1 ORDER BY sort_order, amount');
    $stmt->execute([$event['id']]);
    $packages = $stmt->fetchAll();
}

require __DIR__ . '/views/partials/header.php';
?>
<section class="py-5 bg-emerald text-white">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="section-kicker text-gold">Dukungan Masyarakat</div>
                <h1 class="display-3 fw-bold mb-3">E-Voting Finalis</h1>
                <p class="text-white-50 fs-5 mb-0">Pilih finalis, tentukan paket dukungan, lalu selesaikan pembayaran melalui kanal yang tersedia di Xendit.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if ($event): ?>
                    <span class="badge rounded-pill text-bg-light px-3 py-2 mb-2"><?= e($event['status']) ?></span>
                    <div class="small text-white-50">Periode: <?= indonesia_date($event['voting_start_at'], true) ?> – <?= indonesia_date($event['voting_end_at'], true) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="filter-bar mb-5">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="search" data-finalist-search class="form-control" placeholder="Cari nama, nomor, atau kabupaten/kota">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                        <button class="btn btn-outline-success rounded-pill active" data-category-filter="ALL">Semua</button>
                        <button class="btn btn-outline-success rounded-pill" data-category-filter="BUJANG">Bujang</button>
                        <button class="btn btn-outline-success rounded-pill" data-category-filter="DAYANG">Dayang</button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$event || $event['status'] !== 'VOTING_ACTIVE'): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-5">
            <h5><i class="bi bi-info-circle me-2"></i>E-Voting tidak sedang aktif</h5>
            <p class="mb-0">Anda tetap dapat melihat profil finalis. Tombol voting akan aktif sesuai jadwal yang ditetapkan penyelenggara.</p>
        </div>
        <?php endif; ?>

        <div class="alert bg-emerald text-white border-0 rounded-4 p-4 mb-5">
            <div class="d-flex gap-3 align-items-start">
                <i class="bi bi-pie-chart-fill fs-3 text-gold"></i>
                <div>
                    <strong>Informasi tampilan publik</strong>
                    <div class="small text-white-50">Website publik hanya menampilkan persentase dukungan per kelompok. Nilai perhitungan internal tidak ditampilkan kepada pengunjung.</div>
                </div>
            </div>
        </div>

        <div class="row g-4" id="finalistGrid">
            <?php foreach ($finalists as $f): ?>
                <?php $percentage = $percentageByFinalist[(int) $f['id']] ?? 0.0; ?>
                <div class="col-sm-6 col-lg-4 col-xl-3 finalist-col">
                    <article class="finalist-card" data-finalist-category="<?= e($f['category']) ?>">
                        <div class="finalist-image">
                            <img src="<?= upload_url($f['photo'], $f['category'] === 'DAYANG' ? 'images/placeholder-dayang.svg' : 'images/placeholder-bujang.svg') ?>" alt="<?= e($f['full_name']) ?>">
                            <span class="finalist-number"><?= str_pad((string) $f['contestant_number'], 2, '0', STR_PAD_LEFT) ?></span>
                            <span class="category-badge"><?= ucfirst(strtolower($f['category'])) ?></span>
                        </div>
                        <div class="p-4">
                            <h4 class="mb-1"><?= e($f['full_name']) ?></h4>
                            <div class="small text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i><?= e($f['region_name']) ?></div>
                            <?php if ((int) $event['points_visible'] === 1): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-secondary">Persentase dukungan</span>
                                    <span class="percentage-pill"><i class="bi bi-pie-chart-fill"></i><?= support_percentage($percentage) ?></span>
                                </div>
                                <div class="progress support-progress" role="progressbar" aria-valuenow="<?= e((string) $percentage) ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width:<?= e((string) min(100, max(0, $percentage))) ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="d-grid gap-2">
                                <a href="<?= url('finalist.php?slug=' . urlencode($f['slug'])) ?>" class="btn btn-outline-success rounded-pill">Lihat Profil</a>
                                <button <?= (!$event || $event['status'] !== 'VOTING_ACTIVE') ? 'disabled' : '' ?> class="btn btn-gold rounded-pill" data-bs-toggle="modal" data-bs-target="#voteModal" data-vote-finalist data-id="<?= (int) $f['id'] ?>" data-name="<?= e($f['full_name']) ?>" data-number="<?= e($f['contestant_number']) ?>" data-region="<?= e($f['region_name']) ?>" data-category="<?= e(ucfirst(strtolower($f['category']))) ?>" data-photo="<?= upload_url($f['photo'], $f['category'] === 'DAYANG' ? 'images/placeholder-dayang.svg' : 'images/placeholder-bujang.svg') ?>">
                                    <i class="bi bi-hand-index-thumb me-2"></i>Vote Finalis
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>

            <?php if (!$finalists): ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-people fs-1 text-emerald"></i>
                    <h4 class="mt-3">Belum ada finalis</h4>
                    <p class="text-secondary">Data finalis akan ditampilkan setelah dipublikasikan oleh Superadmin.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="content-card p-4 p-lg-5">
            <div class="row g-4">
                <div class="col-lg-4"><h3>Cara Melakukan Vote</h3><p class="text-secondary">Empat langkah sederhana untuk memberikan dukungan.</p></div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="d-flex gap-3"><div class="icon-box">1</div><div><strong>Pilih finalis</strong><p class="small text-secondary">Klik tombol Vote Finalis pada kartu peserta.</p></div></div></div>
                        <div class="col-md-6"><div class="d-flex gap-3"><div class="icon-box">2</div><div><strong>Isi data</strong><p class="small text-secondary">Masukkan nama dan nomor WhatsApp aktif.</p></div></div></div>
                        <div class="col-md-6"><div class="d-flex gap-3"><div class="icon-box">3</div><div><strong>Pilih paket</strong><p class="small text-secondary">Tentukan nominal dukungan dan lihat bonus dukungan dalam bentuk persentase.</p></div></div></div>
                        <div class="col-md-6"><div class="d-flex gap-3"><div class="icon-box">4</div><div><strong>Selesaikan pembayaran</strong><p class="small text-secondary">Dukungan dihitung setelah pembayaran dikonfirmasi Xendit.</p></div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/views/partials/vote-modal.php'; ?>
<?php require __DIR__ . '/views/partials/footer.php'; ?>
