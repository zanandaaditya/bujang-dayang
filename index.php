<?php
require __DIR__ . '/app/bootstrap.php';
$event = active_event();
$pageTitle = 'Beranda';
$pageDescription = 'Portal resmi informasi, finalis, pemenang, e-voting, dan leaderboard Bujang Dayang Bangka Belitung.';

$topBujang = $topDayang = $featuredFinalists = $schedules = $faqs = $sponsors = [];
if ($event) {
    $topBujang = LeaderboardService::publicRankings((int)$event['id'], 'BUJANG', 3);
    $topDayang = LeaderboardService::publicRankings((int)$event['id'], 'DAYANG', 3);
    $stmt = db()->prepare("SELECT f.*, r.name region_name FROM finalists f JOIN regions r ON r.id=f.region_id WHERE f.event_id=? AND f.is_active=1 ORDER BY f.is_featured DESC, f.category, f.contestant_number LIMIT 8");
    $stmt->execute([$event['id']]); $featuredFinalists = $stmt->fetchAll();
    $stmt = db()->prepare('SELECT * FROM event_schedules WHERE event_id=? AND is_active=1 ORDER BY event_date, sort_order'); $stmt->execute([$event['id']]); $schedules=$stmt->fetchAll();
}
$faqs = db()->query('SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order, id LIMIT 8')->fetchAll();
$sponsors = db()->query('SELECT * FROM sponsors WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
$homeVoting = $event && $event['status'] === 'VOTING_ACTIVE';
require __DIR__ . '/views/partials/header.php';
?>
<section class="hero d-flex align-items-center py-5">
  <div class="container py-5">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="hero-badge mb-4"><i class="bi bi-gem"></i><?= $homeVoting ? 'E-Voting Sedang Berlangsung' : 'Generasi Muda, Budaya, dan Pariwisata' ?></span>
        <h1 class="hero-title mb-4"><?= e(setting('hero_title', 'Pesona Muda, Warisan Budaya Bangka Belitung')) ?></h1>
        <p class="hero-copy mb-4"><?= e(setting('hero_subtitle', 'Kenali putra-putri terbaik dari seluruh kabupaten dan kota yang membawa semangat budaya, pariwisata, kreativitas, serta keramahan Negeri Serumpun Sebalai.')) ?></p>
        <?php if ($event && $event['voting_end_at'] && $homeVoting): ?>
        <div class="mb-4"><div class="small text-uppercase fw-bold text-white-50 mb-2">E-Voting ditutup dalam</div><div class="countdown" data-countdown="<?= e((new DateTimeImmutable($event['voting_end_at']))->format(DATE_ATOM)) ?>"><div class="countdown-item"><strong data-days>00</strong><span>Hari</span></div><div class="countdown-item"><strong data-hours>00</strong><span>Jam</span></div><div class="countdown-item"><strong data-minutes>00</strong><span>Menit</span></div><div class="countdown-item"><strong data-seconds>00</strong><span>Detik</span></div></div></div>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-3"><a href="<?= url('evoting.php') ?>" class="btn btn-gold btn-lg rounded-pill px-4"><i class="bi bi-hand-index-thumb me-2"></i>Vote Finalis</a><a href="<?= url('leaderboard.php') ?>" class="btn btn-outline-light btn-lg rounded-pill px-4"><i class="bi bi-trophy me-2"></i>Lihat Leaderboard</a></div>
      </div>
      <div class="col-lg-5">
        <div class="hero-visual">
          <div class="hero-arch"><img src="<?= upload_url(setting('hero_image'), 'images/placeholder-dayang.svg') ?>" alt="Bujang Dayang Bangka Belitung"></div>
          <div class="floating-card one"><div class="small text-secondary">Tampilan Publik</div><strong class="fs-5 text-emerald">Persentase Dukungan</strong></div>
          <div class="floating-card two"><div class="small text-secondary">Pembaruan Leaderboard</div><strong class="fs-5 text-emerald">Otomatis</strong></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($homeVoting): ?>
<section class="section-space podium-section">
  <div class="container">
    <div class="text-center mb-5"><div class="section-kicker">Peringkat Sementara</div><h2 class="section-title">Tiga Besar E-Voting</h2><p class="section-subtitle mx-auto">Peringkat dihitung hanya dari transaksi yang telah berhasil dibayar dan terkonfirmasi sistem.</p></div>
    <div class="row g-5">
      <?php foreach ([['BUJANG',$topBujang],['DAYANG',$topDayang]] as [$label,$rows]): ?>
      <div class="col-lg-6"><div class="content-card p-4 p-xl-5 h-100"><div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0">Kelompok <?= ucfirst(strtolower($label)) ?></h3><a href="<?= url('leaderboard.php?category='.$label) ?>" class="small fw-bold text-emerald text-decoration-none">Semua peringkat <i class="bi bi-arrow-right"></i></a></div>
        <?php if ($rows): ?><div class="list-group list-group-flush"><?php foreach ($rows as $row): ?><div class="list-group-item px-0 py-3 d-flex align-items-center gap-3"><div class="rank-medal position-static translate-middle-0 flex-shrink-0"><?= (int)$row['rank'] ?></div><img class="mini-avatar" src="<?= upload_url($row['photo'], $label==='DAYANG'?'images/placeholder-dayang.svg':'images/placeholder-bujang.svg') ?>" alt="<?= e($row['full_name']) ?>"><div class="flex-grow-1"><strong class="d-block"><?= e($row['full_name']) ?></strong><small class="text-secondary">No. <?= str_pad((string)$row['contestant_number'],2,'0',STR_PAD_LEFT) ?> • <?= e($row['region_name']) ?></small></div><span class="points-pill d-none d-sm-inline-flex"><i class="bi bi-pie-chart-fill"></i><?= support_percentage($row['support_percentage']) ?></span></div><?php endforeach; ?></div><?php else: ?><div class="empty-state py-4">Belum ada data peringkat.</div><?php endif; ?>
      </div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section-space bg-white">
  <div class="container">
    <div class="row align-items-end mb-5 g-3"><div class="col-lg-8"><div class="section-kicker">Tentang Pemilihan</div><h2 class="section-title">Duta Muda untuk Negeri Serumpun Sebalai</h2></div><div class="col-lg-4"><p class="section-subtitle mb-0">Bujang Dayang hadir sebagai figur inspiratif yang memperkenalkan kekayaan daerah dengan pengetahuan, etika, kreativitas, serta kepedulian sosial.</p></div></div>
    <div class="row g-4">
      <div class="col-md-6 col-xl-3"><div class="feature-card p-4 h-100"><div class="icon-box mb-4"><i class="bi bi-map"></i></div><h4>Promosi Pariwisata</h4><p class="text-secondary mb-0">Mengangkat destinasi, kuliner, ekonomi kreatif, dan pengalaman autentik Bangka Belitung.</p></div></div>
      <div class="col-md-6 col-xl-3"><div class="feature-card p-4 h-100"><div class="icon-box mb-4"><i class="bi bi-flower1"></i></div><h4>Pelestarian Budaya</h4><p class="text-secondary mb-0">Memperkenalkan nilai, tradisi, bahasa, busana, musik, dan kearifan lokal kepada generasi baru.</p></div></div>
      <div class="col-md-6 col-xl-3"><div class="feature-card p-4 h-100"><div class="icon-box mb-4"><i class="bi bi-lightbulb"></i></div><h4>Inovasi Anak Muda</h4><p class="text-secondary mb-0">Mendorong gagasan kreatif yang memberi manfaat nyata bagi masyarakat dan kemajuan daerah.</p></div></div>
      <div class="col-md-6 col-xl-3"><div class="feature-card p-4 h-100"><div class="icon-box mb-4"><i class="bi bi-people"></i></div><h4>Kolaborasi Daerah</h4><p class="text-secondary mb-0">Menyatukan finalis dari tujuh kabupaten/kota dalam jejaring kerja dan pengabdian bersama.</p></div></div>
    </div>
  </div>
</section>

<section class="section-space">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-5"><div><div class="section-kicker">Kenali Peserta</div><h2 class="section-title mb-0">Finalis Pilihan</h2></div><a class="btn btn-outline-success rounded-pill px-4" href="<?= url('evoting.php') ?>">Lihat Seluruh Finalis</a></div>
    <div class="row g-4">
      <?php foreach ($featuredFinalists as $f): ?><div class="col-sm-6 col-lg-3"><article class="finalist-card"><div class="finalist-image"><img src="<?= upload_url($f['photo'],$f['category']==='DAYANG'?'images/placeholder-dayang.svg':'images/placeholder-bujang.svg') ?>" alt="<?= e($f['full_name']) ?>"><span class="finalist-number"><?= str_pad((string)$f['contestant_number'],2,'0',STR_PAD_LEFT) ?></span><span class="category-badge"><?= ucfirst(strtolower($f['category'])) ?></span></div><div class="p-4"><h4 class="mb-1"><?= e($f['full_name']) ?></h4><p class="text-secondary small"><i class="bi bi-geo-alt me-1"></i><?= e($f['region_name']) ?></p><div class="d-flex gap-2"><a class="btn btn-outline-success flex-grow-1 rounded-pill" href="<?= url('finalist.php?slug='.urlencode($f['slug'])) ?>">Profil</a><button class="btn btn-gold rounded-circle" data-bs-toggle="modal" data-bs-target="#voteModal" data-vote-finalist data-id="<?= (int)$f['id'] ?>" data-name="<?= e($f['full_name']) ?>" data-number="<?= e($f['contestant_number']) ?>" data-region="<?= e($f['region_name']) ?>" data-category="<?= e(ucfirst(strtolower($f['category']))) ?>" data-photo="<?= upload_url($f['photo'],$f['category']==='DAYANG'?'images/placeholder-dayang.svg':'images/placeholder-bujang.svg') ?>" aria-label="Vote"><i class="bi bi-hand-index-thumb"></i></button></div></div></article></div><?php endforeach; ?>
      <?php if (!$featuredFinalists): ?><div class="col-12"><div class="empty-state"><i class="bi bi-person-badge fs-1 text-emerald"></i><h4 class="mt-3">Finalis belum dipublikasikan</h4><p class="text-secondary">Silakan kembali saat daftar finalis telah diumumkan.</p></div></div><?php endif; ?>
    </div>
  </div>
</section>

<section class="section-space bg-emerald text-white">
  <div class="container"><div class="row g-5 align-items-center"><div class="col-lg-5"><div class="section-kicker text-gold">Rangkaian Kegiatan</div><h2 class="section-title text-white">Perjalanan Menuju Malam Puncak</h2><p class="text-white-50">Setiap tahap dirancang untuk menguji wawasan, karakter, kreativitas, kemampuan komunikasi, serta kepedulian para finalis.</p></div><div class="col-lg-7"><div class="row g-3"><?php foreach ($schedules as $i=>$s): ?><div class="col-md-6"><div class="glass-card p-4 h-100"><span class="text-gold fw-bold">0<?= $i+1 ?></span><h4 class="text-white mt-2 mb-1"><?= e($s['title']) ?></h4><div class="small text-white-50 mb-2"><?= indonesia_date($s['event_date'], true) ?></div><p class="small text-white-50 mb-0"><?= e($s['description']) ?></p></div></div><?php endforeach; ?><?php if(!$schedules):?><div class="col-12"><div class="glass-card p-4">Jadwal kegiatan akan diumumkan oleh penyelenggara.</div></div><?php endif;?></div></div></div></div>
</section>

<section class="section-space bg-white">
  <div class="container"><div class="row g-5"><div class="col-lg-5"><div class="section-kicker">Informasi Penting</div><h2 class="section-title">Pertanyaan yang Sering Diajukan</h2><p class="section-subtitle">Pelajari cara voting, penghitungan persentase dukungan, proses pembayaran, serta ketentuan dukungan kepada finalis.</p><a href="<?= url('terms.php') ?>" class="btn btn-outline-success rounded-pill px-4">Baca Ketentuan Lengkap</a></div><div class="col-lg-7"><div class="accordion faq-accordion" id="faqHome"><?php foreach($faqs as $i=>$faq):?><div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button <?= $i?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= (int)$faq['id'] ?>"><?= e($faq['question']) ?></button></h2><div id="faq<?= (int)$faq['id'] ?>" class="accordion-collapse collapse <?= !$i?'show':'' ?>" data-bs-parent="#faqHome"><div class="accordion-body text-secondary"><?= nl2br(e($faq['answer'])) ?></div></div></div><?php endforeach;?></div></div></div></div>
</section>

<?php if($sponsors):?><section class="py-5"><div class="container"><div class="text-center mb-4"><div class="section-kicker">Didukung Oleh</div><h3 class="font-display fs-1">Sponsor dan Mitra</h3></div><div class="d-flex flex-wrap justify-content-center align-items-center gap-4"><?php foreach($sponsors as $s):?><a href="<?= e($s['website_url'] ?: '#') ?>" class="content-card p-3 d-flex align-items-center justify-content-center" style="width:170px;height:90px" target="_blank" rel="noopener"><img src="<?= upload_url($s['logo'],'images/logo.svg') ?>" alt="<?= e($s['name']) ?>" style="max-width:130px;max-height:58px"></a><?php endforeach;?></div></div></section><?php endif;?>
<?php require __DIR__ . '/views/partials/vote-modal.php'; ?>
<?php require __DIR__ . '/views/partials/footer.php'; ?>
