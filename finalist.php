<?php
require __DIR__ . '/app/bootstrap.php';

$slug = (string) ($_GET['slug'] ?? '');
$stmt = db()->prepare(
    'SELECT f.*, r.name AS region_name
     FROM finalists f
     JOIN regions r ON r.id = f.region_id
     WHERE f.slug = ?
     LIMIT 1'
);
$stmt->execute([$slug]);
$finalist = $stmt->fetch();
if (!$finalist) {
    http_response_code(404);
    exit('Finalis tidak ditemukan.');
}

$event = active_event();
$pageTitle = $finalist['full_name'];
$pageDescription = 'Profil Finalis ' . $finalist['full_name'] . ' asal ' . $finalist['region_name'] . '.';
$supportPercentage = LeaderboardService::publicPercentageForFinalist(
    (int) $finalist['event_id'],
    (string) $finalist['category'],
    (int) $finalist['id']
);

$stmt = db()->prepare('SELECT * FROM vote_packages WHERE event_id=? AND is_active=1 ORDER BY sort_order, amount');
$stmt->execute([$finalist['event_id']]);
$packages = $stmt->fetchAll();

require __DIR__ . '/views/partials/header.php';
?>
<section class="py-5 bg-emerald text-white">
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="hero-arch position-relative inset-auto" style="height:620px;inset:auto">
                    <img src="<?= upload_url($finalist['photo'], $finalist['category'] === 'DAYANG' ? 'images/placeholder-dayang.svg' : 'images/placeholder-bujang.svg') ?>" alt="<?= e($finalist['full_name']) ?>">
                </div>
            </div>
            <div class="col-lg-7">
                <span class="hero-badge mb-4">Finalis <?= ucfirst(strtolower($finalist['category'])) ?> • Nomor <?= str_pad((string) $finalist['contestant_number'], 2, '0', STR_PAD_LEFT) ?></span>
                <h1 class="hero-title mb-3"><?= e($finalist['full_name']) ?></h1>
                <p class="fs-5 text-white-50"><i class="bi bi-geo-alt me-2 text-gold"></i><?= e($finalist['region_name']) ?></p>

                <?php if ($event && (int) $event['points_visible'] === 1): ?>
                <div class="glass-card d-inline-flex align-items-center gap-3 px-4 py-3 my-3">
                    <i class="bi bi-pie-chart-fill fs-3 text-gold"></i>
                    <div>
                        <small class="d-block text-white-50">Persentase Dukungan</small>
                        <strong class="fs-3 text-gold"><?= support_percentage($supportPercentage) ?></strong>
                    </div>
                </div>
                <?php endif; ?>

                <blockquote class="fs-4 font-display border-start border-warning border-3 ps-4 my-4">“<?= e($finalist['motto'] ?: 'Menjadi generasi muda yang berakar pada budaya dan bergerak untuk masa depan.') ?>”</blockquote>
                <div class="d-flex flex-wrap gap-3">
                    <button <?= (!$event || $event['status'] !== 'VOTING_ACTIVE') ? 'disabled' : '' ?> class="btn btn-gold btn-lg rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#voteModal" data-vote-finalist data-id="<?= (int) $finalist['id'] ?>" data-name="<?= e($finalist['full_name']) ?>" data-number="<?= e($finalist['contestant_number']) ?>" data-region="<?= e($finalist['region_name']) ?>" data-category="<?= e(ucfirst(strtolower($finalist['category']))) ?>" data-photo="<?= upload_url($finalist['photo'], $finalist['category'] === 'DAYANG' ? 'images/placeholder-dayang.svg' : 'images/placeholder-bujang.svg') ?>">
                        <i class="bi bi-hand-index-thumb me-2"></i>Vote Finalis Ini
                    </button>
                    <?php if ($finalist['instagram']): ?>
                    <a class="btn btn-outline-light btn-lg rounded-pill" target="_blank" rel="noopener" href="<?= e($finalist['instagram']) ?>"><i class="bi bi-instagram me-2"></i>Instagram</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space bg-white">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="section-kicker">Tentang Finalis</div>
                <h2 class="section-title">Profil dan Perjalanan</h2>
                <div class="fs-5 text-secondary lh-lg"><?= nl2br(e($finalist['biography'] ?: 'Profil lengkap finalis dapat diperbarui oleh Superadmin melalui dashboard pengelolaan finalis.')) ?></div>
            </div>
            <div class="col-lg-5">
                <div class="content-card p-4 p-lg-5">
                    <h3 class="mb-4">Informasi Singkat</h3>
                    <div class="d-flex justify-content-between py-3 border-bottom"><span class="text-secondary">Nomor Finalis</span><strong><?= str_pad((string) $finalist['contestant_number'], 2, '0', STR_PAD_LEFT) ?></strong></div>
                    <div class="d-flex justify-content-between py-3 border-bottom"><span class="text-secondary">Kelompok</span><strong><?= ucfirst(strtolower($finalist['category'])) ?></strong></div>
                    <div class="d-flex justify-content-between py-3 border-bottom"><span class="text-secondary">Asal</span><strong class="text-end"><?= e($finalist['region_name']) ?></strong></div>
                    <div class="d-flex justify-content-between py-3"><span class="text-secondary">Pendidikan</span><strong class="text-end"><?= e($finalist['education'] ?: '-') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="content-card p-4 p-lg-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-4">
                    <div class="icon-box mb-4"><i class="bi bi-lightbulb"></i></div>
                    <div class="section-kicker">Program dan Gagasan</div>
                    <h2 class="section-title fs-1">Kontribusi untuk Bangka Belitung</h2>
                </div>
                <div class="col-lg-8"><p class="fs-5 text-secondary lh-lg mb-0"><?= nl2br(e($finalist['program_description'] ?: 'Gagasan dan program unggulan finalis akan ditampilkan pada bagian ini.')) ?></p></div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/views/partials/vote-modal.php'; ?>
<?php require __DIR__ . '/views/partials/footer.php'; ?>
