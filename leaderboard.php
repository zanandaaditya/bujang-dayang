<?php
require __DIR__ . '/app/bootstrap.php';

$event = active_event();
$pageTitle = 'Leaderboard E-Voting';
$pageDescription = 'Leaderboard publik Bujang Dayang Bangka Belitung dalam bentuk persentase dukungan.';
$category = strtoupper((string) ($_GET['category'] ?? 'BUJANG'));
if (!in_array($category, ['BUJANG', 'DAYANG'], true)) {
    $category = 'BUJANG';
}

$bujang = $dayang = [];
if ($event) {
    $bujang = LeaderboardService::publicRankings((int) $event['id'], 'BUJANG');
    $dayang = LeaderboardService::publicRankings((int) $event['id'], 'DAYANG');
}

function podium_order(array $rows): array
{
    return [$rows[1] ?? null, $rows[0] ?? null, $rows[2] ?? null];
}

require __DIR__ . '/views/partials/header.php';
?>
<section class="py-5 bg-emerald text-white">
    <div class="container py-4 text-center">
        <div class="section-kicker text-gold">Peringkat Sementara</div>
        <h1 class="display-3 fw-bold">Leaderboard E-Voting</h1>
        <p class="text-white-50 mx-auto" style="max-width:760px">
            Halaman publik hanya menampilkan persentase dukungan. Persentase setiap finalis dihitung terhadap total dukungan sah dalam kelompok Bujang atau Dayang.
        </p>
        <?php if ($event): ?>
        <div class="d-flex justify-content-center flex-wrap gap-3 mt-4">
            <span class="glass-card px-4 py-3">
                <small class="d-block text-white-50">Format Publik</small>
                <strong class="text-gold fs-4">Persentase</strong>
            </span>
            <span class="glass-card px-4 py-3">
                <small class="d-block text-white-50">Dasar Perhitungan</small>
                <strong class="text-gold fs-5">Pembayaran Terkonfirmasi</strong>
            </span>
            <span class="glass-card px-4 py-3">
                <small class="d-block text-white-50">Terakhir diperbarui</small>
                <strong class="text-gold"><?= date('H:i') ?> WIB</strong>
            </span>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php foreach ([['BUJANG', $bujang], ['DAYANG', $dayang]] as [$label, $rows]): ?>
<section class="section-space <?= $label === 'DAYANG' ? 'bg-white' : 'podium-section' ?>">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-kicker">Kelompok <?= ucfirst(strtolower($label)) ?></div>
            <h2 class="section-title">Tiga Peringkat Tertinggi</h2>
            <p class="section-subtitle mx-auto">Total persentase dalam setiap kelompok adalah 100% ketika sudah terdapat dukungan sah.</p>
        </div>

        <?php if ($rows): ?>
        <div class="podium mb-5">
            <?php foreach (podium_order($rows) as $i => $r): ?>
                <?php if ($r): ?>
                    <?php $class = $i === 1 ? 'first' : ($i === 0 ? 'second' : 'third'); ?>
                    <div class="podium-item <?= $class ?>">
                        <div class="podium-avatar">
                            <img src="<?= upload_url($r['photo'], $label === 'DAYANG' ? 'images/placeholder-dayang.svg' : 'images/placeholder-bujang.svg') ?>" alt="<?= e($r['full_name']) ?>">
                        </div>
                        <div class="podium-base">
                            <span class="rank-medal"><?= (int) $r['rank'] ?></span>
                            <h5 class="mt-3 mb-1"><?= e($r['full_name']) ?></h5>
                            <div class="small opacity-75">No. <?= str_pad((string) $r['contestant_number'], 2, '0', STR_PAD_LEFT) ?> • <?= e($r['region_name']) ?></div>
                            <strong class="d-block mt-3 fs-4"><?= support_percentage($r['support_percentage']) ?></strong>
                            <small>dukungan</small>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-leaderboard align-middle">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Finalis</th>
                        <th>Asal</th>
                        <th style="min-width:210px">Persentase Dukungan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><span class="rank-medal position-static translate-middle-0"><?= (int) $r['rank'] ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img class="mini-avatar" src="<?= upload_url($r['photo'], $label === 'DAYANG' ? 'images/placeholder-dayang.svg' : 'images/placeholder-bujang.svg') ?>" alt="<?= e($r['full_name']) ?>">
                                <div>
                                    <strong><?= e($r['full_name']) ?></strong>
                                    <small class="d-block text-secondary">Nomor <?= str_pad((string) $r['contestant_number'], 2, '0', STR_PAD_LEFT) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= e($r['region_name']) ?></td>
                        <td>
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <span class="small text-secondary">Dukungan sah</span>
                                <strong class="text-emerald"><?= support_percentage($r['support_percentage']) ?></strong>
                            </div>
                            <div class="progress support-progress" role="progressbar" aria-label="Persentase dukungan <?= e($r['full_name']) ?>" aria-valuenow="<?= e((string) $r['support_percentage']) ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width:<?= e((string) min(100, max(0, (float) $r['support_percentage']))) ?>%"></div>
                            </div>
                        </td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-success rounded-pill" href="<?= url('finalist.php?slug=' . urlencode($r['slug'])) ?>">Profil</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">Belum ada data leaderboard untuk kelompok ini.</div>
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>

<section class="py-5 bg-gold-soft">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h3 class="mb-2">Dukung Finalis Pilihan Anda</h3>
                <p class="text-secondary mb-0">Peringkat e-voting merupakan bentuk dukungan masyarakat dan tidak secara otomatis menentukan juara utama.</p>
            </div>
            <div class="col-lg-4 text-lg-end"><a href="<?= url('evoting.php') ?>" class="btn btn-emerald btn-lg rounded-pill px-4">Vote Sekarang</a></div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/views/partials/footer.php'; ?>
