SET NAMES utf8mb4;
SET time_zone = '+07:00';

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','superadmin') NOT NULL DEFAULT 'superadmin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  year SMALLINT UNSIGNED NOT NULL,
  theme VARCHAR(255) NULL,
  voting_start_at DATETIME NULL,
  voting_end_at DATETIME NULL,
  status ENUM('DRAFT','PUBLISHED','VOTING_ACTIVE','VOTING_CLOSED','ARCHIVED') NOT NULL DEFAULT 'DRAFT',
  homepage_mode ENUM('AUTO','GENERAL','LEADERBOARD') NOT NULL DEFAULT 'AUTO',
  leaderboard_visible TINYINT(1) NOT NULL DEFAULT 1,
  leaderboard_frozen TINYINT(1) NOT NULL DEFAULT 0,
  points_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_events_status_year(status,year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS regions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  type ENUM('KABUPATEN','KOTA','PROVINSI') NOT NULL DEFAULT 'KABUPATEN',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS finalists (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  category ENUM('BUJANG','DAYANG') NOT NULL,
  contestant_number INT UNSIGNED NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  region_id BIGINT UNSIGNED NOT NULL,
  photo VARCHAR(255) NULL,
  biography TEXT NULL,
  motto VARCHAR(500) NULL,
  education VARCHAR(255) NULL,
  program_description TEXT NULL,
  instagram VARCHAR(255) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_finalist_number(event_id,category,contestant_number),
  INDEX idx_finalists_event_category(event_id,category,is_active),
  CONSTRAINT fk_finalists_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE RESTRICT,
  CONSTRAINT fk_finalists_region FOREIGN KEY(region_id) REFERENCES regions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS winners (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year SMALLINT UNSIGNED NOT NULL UNIQUE,
  theme VARCHAR(255) NULL,
  bujang_name VARCHAR(150) NOT NULL,
  bujang_region_id BIGINT UNSIGNED NULL,
  bujang_photo VARCHAR(255) NULL,
  dayang_name VARCHAR(150) NOT NULL,
  dayang_region_id BIGINT UNSIGNED NULL,
  dayang_photo VARCHAR(255) NULL,
  description TEXT NULL,
  documentation_url VARCHAR(255) NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_winner_bujang_region FOREIGN KEY(bujang_region_id) REFERENCES regions(id) ON DELETE SET NULL,
  CONSTRAINT fk_winner_dayang_region FOREIGN KEY(dayang_region_id) REFERENCES regions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vote_packages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  amount BIGINT UNSIGNED NOT NULL,
  base_points BIGINT UNSIGNED NOT NULL,
  bonus_points BIGINT UNSIGNED NOT NULL DEFAULT 0,
  total_points BIGINT UNSIGNED NOT NULL,
  badge VARCHAR(80) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_packages_event(event_id,is_active,sort_order),
  CONSTRAINT fk_packages_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vote_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(64) NOT NULL UNIQUE,
  event_id BIGINT UNSIGNED NOT NULL,
  finalist_id BIGINT UNSIGNED NOT NULL,
  voter_name VARCHAR(100) NOT NULL,
  voter_phone VARCHAR(30) NOT NULL,
  voter_phone_hash CHAR(64) NOT NULL,
  package_id BIGINT UNSIGNED NULL,
  package_name_snapshot VARCHAR(100) NOT NULL,
  amount_snapshot BIGINT UNSIGNED NOT NULL,
  base_points_snapshot BIGINT UNSIGNED NOT NULL,
  bonus_points_snapshot BIGINT UNSIGNED NOT NULL,
  total_points_snapshot BIGINT UNSIGNED NOT NULL,
  payment_method VARCHAR(40) NOT NULL DEFAULT 'XENDIT',
  payment_channel VARCHAR(80) NULL,
  payment_status ENUM('CREATED','PENDING','PAID','FAILED','EXPIRED','CANCELED','REFUNDED','REVERSED') NOT NULL DEFAULT 'CREATED',
  xendit_session_id VARCHAR(100) NULL,
  xendit_payment_request_id VARCHAR(100) NULL,
  xendit_payment_id VARCHAR(100) NULL,
  payment_link_url TEXT NULL,
  expires_at DATETIME NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_orders_event_status(event_id,payment_status,created_at),
  INDEX idx_orders_finalist(finalist_id,payment_status),
  INDEX idx_orders_phone_hash(voter_phone_hash),
  UNIQUE KEY uq_xendit_payment_id(xendit_payment_id),
  CONSTRAINT fk_orders_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE RESTRICT,
  CONSTRAINT fk_orders_finalist FOREIGN KEY(finalist_id) REFERENCES finalists(id) ON DELETE RESTRICT,
  CONSTRAINT fk_orders_package FOREIGN KEY(package_id) REFERENCES vote_packages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leaderboard_snapshots (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  category ENUM('BUJANG','DAYANG') NOT NULL,
  snapshot_data LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_snapshot_event(event_id,category,created_at),
  CONSTRAINT fk_snapshot_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS point_ledgers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  finalist_id BIGINT UNSIGNED NOT NULL,
  vote_order_id BIGINT UNSIGNED NULL,
  transaction_type ENUM('VOTE','REVERSAL','ADJUSTMENT','BONUS') NOT NULL,
  points BIGINT NOT NULL,
  description VARCHAR(500) NOT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_ledger_event_finalist(event_id,finalist_id),
  UNIQUE KEY uq_vote_ledger(vote_order_id,transaction_type),
  CONSTRAINT fk_ledger_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ledger_finalist FOREIGN KEY(finalist_id) REFERENCES finalists(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ledger_order FOREIGN KEY(vote_order_id) REFERENCES vote_orders(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ledger_user FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_webhooks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_name VARCHAR(120) NOT NULL,
  webhook_identifier VARCHAR(190) NOT NULL UNIQUE,
  payment_id VARCHAR(120) NULL,
  reference_id VARCHAR(64) NULL,
  payload LONGTEXT NOT NULL,
  verification_status ENUM('VERIFIED','REJECTED') NOT NULL,
  processing_status ENUM('RECEIVED','PROCESSED','FAILED') NOT NULL,
  processing_error VARCHAR(1000) NULL,
  received_at DATETIME NOT NULL,
  processed_at DATETIME NULL,
  INDEX idx_webhooks_reference(reference_id),
  INDEX idx_webhooks_status(processing_status,received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_schedules (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  event_date DATETIME NOT NULL,
  location VARCHAR(255) NULL,
  description TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_schedule_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faqs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sponsors (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  logo VARCHAR(255) NULL,
  website_url VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(120) PRIMARY KEY,
  setting_value TEXT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  action_key VARCHAR(80) NOT NULL,
  identity_hash CHAR(64) NOT NULL,
  window_started_at DATETIME NOT NULL,
  hit_count INT UNSIGNED NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_rate_limit(action_key,identity_hash),
  INDEX idx_rate_window(window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(100) NULL,
  entity_id BIGINT UNSIGNED NULL,
  old_values LONGTEXT NULL,
  new_values LONGTEXT NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(500) NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_audit_action(action,created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO regions(id,name,type,sort_order,is_active) VALUES
(1,'Kota Pangkal Pinang','KOTA',1,1),(2,'Kabupaten Bangka','KABUPATEN',2,1),(3,'Kabupaten Bangka Barat','KABUPATEN',3,1),(4,'Kabupaten Bangka Tengah','KABUPATEN',4,1),(5,'Kabupaten Bangka Selatan','KABUPATEN',5,1),(6,'Kabupaten Belitung','KABUPATEN',6,1),(7,'Kabupaten Belitung Timur','KABUPATEN',7,1);

INSERT IGNORE INTO events(id,name,year,theme,voting_start_at,voting_end_at,status,homepage_mode,leaderboard_visible,leaderboard_frozen,points_visible,created_at,updated_at) VALUES
(1,'Pemilihan Bujang Dayang Bangka Belitung 2026',2026,'Pesona Muda, Warisan Budaya','2026-07-01 00:00:00','2026-08-31 23:59:59','VOTING_ACTIVE','AUTO',1,0,1,NOW(),NOW());

INSERT IGNORE INTO finalists(id,event_id,category,contestant_number,full_name,slug,region_id,biography,motto,education,program_description,is_featured,is_active,created_at,updated_at) VALUES
(1,1,'BUJANG',1,'Finalis Bujang Pangkal Pinang','finalis-bujang-pangkal-pinang-bujang-1',1,'Putra daerah yang memiliki minat pada promosi kota kreatif, sejarah, dan pengembangan komunitas muda.','Berakar pada budaya, bergerak untuk masa depan.','Mahasiswa', 'Program promosi wisata kota berbasis konten digital dan kolaborasi komunitas.',1,1,NOW(),NOW()),
(2,1,'BUJANG',2,'Finalis Bujang Bangka','finalis-bujang-bangka-bujang-2',2,'Finalis dari Kabupaten Bangka dengan perhatian pada budaya pesisir dan ekonomi kreatif.','Karya kecil yang konsisten melahirkan perubahan besar.','Mahasiswa', 'Kampanye wisata ramah lingkungan dan penguatan produk kreatif lokal.',1,1,NOW(),NOW()),
(3,1,'BUJANG',3,'Finalis Bujang Bangka Barat','finalis-bujang-bangka-barat-bujang-3',3,'Pemuda yang aktif dalam kegiatan sosial dan edukasi sejarah daerah.','Mengenal sejarah untuk menata masa depan.','Mahasiswa', 'Tur edukasi sejarah dan peningkatan literasi budaya generasi muda.',0,1,NOW(),NOW()),
(4,1,'BUJANG',4,'Finalis Bujang Bangka Tengah','finalis-bujang-bangka-tengah-bujang-4',4,'Finalis dengan ketertarikan pada pengembangan desa wisata dan komunikasi publik.','Tumbuh bersama, berdampak untuk semua.','Mahasiswa', 'Pendampingan konten digital bagi desa wisata dan pelaku UMKM.',0,1,NOW(),NOW()),
(5,1,'BUJANG',5,'Finalis Bujang Bangka Selatan','finalis-bujang-bangka-selatan-bujang-5',5,'Pemuda yang membawa semangat promosi bahari dan pelestarian lingkungan.','Laut adalah identitas yang harus dijaga.','Mahasiswa', 'Gerakan wisata bahari bertanggung jawab dan edukasi sampah pesisir.',0,1,NOW(),NOW()),
(6,1,'BUJANG',6,'Finalis Bujang Belitung','finalis-bujang-belitung-bujang-6',6,'Finalis yang aktif memperkenalkan destinasi, kuliner, dan kisah masyarakat Belitung.','Cerita terbaik lahir dari pengalaman yang tulus.','Mahasiswa', 'Storytelling destinasi untuk memperpanjang lama tinggal wisatawan.',1,1,NOW(),NOW()),
(7,1,'BUJANG',7,'Finalis Bujang Belitung Timur','finalis-bujang-belitung-timur-bujang-7',7,'Pemuda yang fokus pada literasi budaya dan potensi geopark.','Belajar dari alam, berkarya untuk daerah.','Mahasiswa', 'Edukasi geopark dan pengembangan relawan pemandu muda.',0,1,NOW(),NOW()),
(8,1,'DAYANG',1,'Finalis Dayang Pangkal Pinang','finalis-dayang-pangkal-pinang-dayang-1',1,'Putri daerah yang aktif pada bidang komunikasi, budaya, dan kegiatan sosial.','Elegan dalam sikap, nyata dalam karya.','Mahasiswi', 'Kampanye ruang publik ramah wisata dan konten budaya perkotaan.',1,1,NOW(),NOW()),
(9,1,'DAYANG',2,'Finalis Dayang Bangka','finalis-dayang-bangka-dayang-2',2,'Finalis dengan perhatian pada kriya, kuliner, dan pemberdayaan perempuan muda.','Budaya hidup ketika terus diceritakan.','Mahasiswi', 'Kelas promosi digital bagi pengrajin dan pelaku kuliner lokal.',1,1,NOW(),NOW()),
(10,1,'DAYANG',3,'Finalis Dayang Bangka Barat','finalis-dayang-bangka-barat-dayang-3',3,'Putri daerah yang mengembangkan edukasi sejarah dan literasi generasi muda.','Pengetahuan adalah bentuk cinta kepada daerah.','Mahasiswi', 'Konten sejarah singkat dan kunjungan edukatif untuk pelajar.',0,1,NOW(),NOW()),
(11,1,'DAYANG',4,'Finalis Dayang Bangka Tengah','finalis-dayang-bangka-tengah-dayang-4',4,'Finalis yang aktif dalam kegiatan lingkungan dan pengembangan desa.','Merawat alam berarti merawat masa depan.','Mahasiswi', 'Gerakan wisata hijau dan edukasi pengurangan sampah.',0,1,NOW(),NOW()),
(12,1,'DAYANG',5,'Finalis Dayang Bangka Selatan','finalis-dayang-bangka-selatan-dayang-5',5,'Putri daerah yang membawa gagasan penguatan wisata bahari dan UMKM.','Berani bermimpi, tekun mewujudkan.','Mahasiswi', 'Paket wisata kolaboratif antara pelaku bahari, kuliner, dan kriya.',0,1,NOW(),NOW()),
(13,1,'DAYANG',6,'Finalis Dayang Belitung','finalis-dayang-belitung-dayang-6',6,'Finalis yang aktif pada promosi destinasi dan pelestarian seni pertunjukan.','Pesona sejati tumbuh dari karakter.','Mahasiswi', 'Panggung budaya mini sebagai pengalaman tambahan bagi wisatawan.',1,1,NOW(),NOW()),
(14,1,'DAYANG',7,'Finalis Dayang Belitung Timur','finalis-dayang-belitung-timur-dayang-7',7,'Putri daerah dengan perhatian pada geopark, literasi, dan komunitas kreatif.','Bergerak bersama untuk dampak yang lebih luas.','Mahasiswi', 'Program duta muda geopark dan konten edukasi berbasis sekolah.',0,1,NOW(),NOW());

INSERT IGNORE INTO vote_packages(id,event_id,name,amount,base_points,bonus_points,total_points,badge,sort_order,is_active,created_at,updated_at) VALUES
(1,1,'Dukungan 10K',10000,10000,0,10000,NULL,1,1,NOW(),NOW()),
(2,1,'Dukungan 20K',20000,20000,0,20000,NULL,2,1,NOW(),NOW()),
(3,1,'Dukungan 50K',50000,50000,10000,60000,'Mulai Bonus',3,1,NOW(),NOW()),
(4,1,'Dukungan 100K',100000,100000,25000,125000,'Populer',4,1,NOW(),NOW()),
(5,1,'Dukungan 250K',250000,250000,75000,325000,NULL,5,1,NOW(),NOW()),
(6,1,'Dukungan 500K',500000,500000,175000,675000,'Best Value',6,1,NOW(),NOW()),
(7,1,'Dukungan 1 Juta',1000000,1000000,400000,1400000,'Premium',7,1,NOW(),NOW()),
(8,1,'Dukungan 2,5 Juta',2500000,2500000,1250000,3750000,'VIP',8,1,NOW(),NOW()),
(9,1,'Dukungan 5 Juta',5000000,5000000,3000000,8000000,'Ultimate',9,1,NOW(),NOW());

INSERT IGNORE INTO point_ledgers(id,event_id,finalist_id,vote_order_id,transaction_type,points,description,created_at) VALUES
(1,1,1,NULL,'ADJUSTMENT',940000,'Data demonstrasi awal',NOW()),(2,1,2,NULL,'ADJUSTMENT',1250000,'Data demonstrasi awal',NOW()),(3,1,3,NULL,'ADJUSTMENT',615000,'Data demonstrasi awal',NOW()),(4,1,4,NULL,'ADJUSTMENT',780000,'Data demonstrasi awal',NOW()),(5,1,5,NULL,'ADJUSTMENT',520000,'Data demonstrasi awal',NOW()),(6,1,6,NULL,'ADJUSTMENT',1680000,'Data demonstrasi awal',NOW()),(7,1,7,NULL,'ADJUSTMENT',430000,'Data demonstrasi awal',NOW()),(8,1,8,NULL,'ADJUSTMENT',1100000,'Data demonstrasi awal',NOW()),(9,1,9,NULL,'ADJUSTMENT',890000,'Data demonstrasi awal',NOW()),(10,1,10,NULL,'ADJUSTMENT',645000,'Data demonstrasi awal',NOW()),(11,1,11,NULL,'ADJUSTMENT',760000,'Data demonstrasi awal',NOW()),(12,1,12,NULL,'ADJUSTMENT',480000,'Data demonstrasi awal',NOW()),(13,1,13,NULL,'ADJUSTMENT',1450000,'Data demonstrasi awal',NOW()),(14,1,14,NULL,'ADJUSTMENT',590000,'Data demonstrasi awal',NOW());

INSERT IGNORE INTO winners(id,year,theme,bujang_name,bujang_region_id,dayang_name,dayang_region_id,description,is_published,created_at,updated_at) VALUES
(1,2025,'Generasi Muda untuk Pariwisata Berkelanjutan','Nama Bujang 2025',2,'Nama Dayang 2025',6,'Data contoh yang dapat diganti melalui dashboard Superadmin.',1,NOW(),NOW()),
(2,2024,'Pesona Budaya Negeri Serumpun Sebalai','Nama Bujang 2024',1,'Nama Dayang 2024',4,'Data contoh yang dapat diganti melalui dashboard Superadmin.',1,NOW(),NOW()),
(3,2023,'Muda, Kreatif, dan Berbudaya','Nama Bujang 2023',7,'Nama Dayang 2023',3,'Data contoh yang dapat diganti melalui dashboard Superadmin.',1,NOW(),NOW());

INSERT IGNORE INTO event_schedules(id,event_id,title,event_date,location,description,sort_order,is_active,created_at,updated_at) VALUES
(1,1,'Pengumuman Finalis','2026-07-01 10:00:00','Pangkal Pinang','Pengenalan finalis dari tujuh kabupaten/kota.',1,1,NOW(),NOW()),
(2,1,'Pembukaan E-Voting','2026-07-01 12:00:00','Online','Masyarakat mulai dapat memberikan dukungan melalui website resmi.',2,1,NOW(),NOW()),
(3,1,'Masa Karantina','2026-08-25 08:00:00','Pangkal Pinang','Pembekalan budaya, pariwisata, komunikasi, dan pengembangan diri.',3,1,NOW(),NOW()),
(4,1,'Malam Grand Final','2026-08-31 19:00:00','Pangkal Pinang','Penampilan akhir dan penetapan Bujang Dayang Bangka Belitung.',4,1,NOW(),NOW());

INSERT IGNORE INTO faqs(id,question,answer,sort_order,is_active,created_at,updated_at) VALUES
(1,'Bagaimana cara melakukan voting?','Pilih finalis, isi nama dan nomor telepon, pilih paket dukungan, setujui ketentuan, lalu selesaikan pembayaran melalui Xendit.',1,1,NOW(),NOW()),
(2,'Apakah pemilih harus membuat akun?','Tidak. Pemilih tidak wajib memiliki akun. Identitas pemilih dicatat pada setiap transaksi menggunakan nama dan nomor telepon.',2,1,NOW(),NOW()),
(3,'Kapan dukungan masuk ke leaderboard?','Persentase dukungan diperbarui setelah pembayaran berhasil dan webhook Xendit dikonfirmasi oleh server. Transaksi pending tidak dihitung.',3,1,NOW(),NOW()),
(4,'Apakah voting menentukan juara pertama?','Tidak secara otomatis. E-voting merupakan bentuk dukungan masyarakat dan hasil akhir mengikuti mekanisme penilaian penyelenggara.',4,1,NOW(),NOW()),
(5,'Apakah pembayaran dapat dikembalikan?','Pada prinsipnya pembayaran berhasil tidak dapat dikembalikan, kecuali terdapat kondisi khusus yang telah diverifikasi oleh penyelenggara atau penyedia pembayaran.',5,1,NOW(),NOW()),
(6,'Metode pembayaran apa yang tersedia?','Kanal pembayaran yang aktif, seperti QRIS dan dompet digital, akan ditampilkan pada halaman Hosted Checkout Xendit.',6,1,NOW(),NOW());

INSERT INTO settings(setting_key,setting_value,updated_at) VALUES
('site_name','Bujang Dayang Bangka Belitung',NOW()),
('site_description','Portal resmi informasi, finalis, pemenang, e-voting, dan leaderboard Bujang Dayang Bangka Belitung.',NOW()),
('hero_title','Pesona Muda, Warisan Budaya Bangka Belitung',NOW()),
('hero_subtitle','Kenali putra-putri terbaik dari seluruh kabupaten dan kota yang membawa semangat budaya, pariwisata, kreativitas, serta keramahan Negeri Serumpun Sebalai.',NOW()),
('footer_description','Ruang apresiasi generasi muda terbaik yang membawa budaya, pariwisata, kreativitas, dan keramahan Kepulauan Bangka Belitung.',NOW()),
('contact_email','panitia@bujangdayangbabel.id',NOW()),
('contact_phone','+62 812-0000-0000',NOW()),
('contact_address','Pangkal Pinang, Kepulauan Bangka Belitung',NOW()),
('instagram_url','#',NOW()),('youtube_url','#',NOW()),('tiktok_url','#',NOW())
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),updated_at=NOW();
