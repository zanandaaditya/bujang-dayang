<?php

declare(strict_types=1);

final class VoteService
{
    public static function createOrder(array $data): array
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        RateLimiter::hit('vote_create', $ip, 12, 900);

        $event = active_event();
        if (!$event || $event['status'] !== 'VOTING_ACTIVE') {
            throw new RuntimeException('E-voting belum dibuka atau telah ditutup.');
        }
        $now = new DateTimeImmutable('now');
        if ($event['voting_start_at'] && $now < new DateTimeImmutable($event['voting_start_at'])) {
            throw new RuntimeException('E-voting belum dimulai.');
        }
        if ($event['voting_end_at'] && $now > new DateTimeImmutable($event['voting_end_at'])) {
            throw new RuntimeException('E-voting telah ditutup.');
        }

        $stmt = db()->prepare('SELECT * FROM finalists WHERE id = ? AND event_id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([(int) $data['finalist_id'], (int) $event['id']]);
        $finalist = $stmt->fetch();
        if (!$finalist) throw new RuntimeException('Finalis tidak ditemukan atau sedang tidak aktif.');

        $stmt = db()->prepare('SELECT * FROM vote_packages WHERE id = ? AND event_id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([(int) $data['package_id'], (int) $event['id']]);
        $package = $stmt->fetch();
        if (!$package) throw new RuntimeException('Paket voting tidak tersedia.');

        $name = trim((string) ($data['voter_name'] ?? ''));
        $phone = normalize_phone((string) ($data['voter_phone'] ?? ''));
        if (mb_strlen($name) < 3 || mb_strlen($name) > 100) throw new RuntimeException('Nama pemilih harus terdiri dari 3–100 karakter.');
        if (!preg_match('/^\+62\d{8,13}$/', $phone)) throw new RuntimeException('Nomor telepon Indonesia tidak valid.');
        if (empty($data['consent_vote']) || empty($data['consent_refund']) || empty($data['consent_privacy'])) {
            throw new RuntimeException('Seluruh pernyataan persetujuan wajib dicentang.');
        }

        $orderNumber = 'BD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $expiresAt = (new DateTimeImmutable('now'))->modify('+' . (int) app_config('xendit.expiry_minutes', 30) . ' minutes');

        $stmt = db()->prepare("INSERT INTO vote_orders
            (order_number, event_id, finalist_id, voter_name, voter_phone, voter_phone_hash, package_id, package_name_snapshot,
             amount_snapshot, base_points_snapshot, bonus_points_snapshot, total_points_snapshot, payment_method,
             payment_status, expires_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'XENDIT', 'CREATED', ?, NOW(), NOW())");
        $stmt->execute([
            $orderNumber, $event['id'], $finalist['id'], $name, encrypt_value($phone), hash('sha256', $phone), $package['id'], $package['name'],
            $package['amount'], $package['base_points'], $package['bonus_points'], $package['total_points'], $expiresAt->format('Y-m-d H:i:s'),
        ]);
        $id = (int) db()->lastInsertId();
        audit('CREATE_VOTE_ORDER', 'vote_orders', $id, null, ['order_number'=>$orderNumber,'finalist_id'=>$finalist['id'],'amount'=>$package['amount']]);
        return self::findById($id) ?? throw new RuntimeException('Transaksi gagal dibuat.');
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare("SELECT vo.*, f.full_name AS finalist_name, f.contestant_number, f.category, f.photo, r.name AS region_name
                              FROM vote_orders vo JOIN finalists f ON f.id = vo.finalist_id JOIN regions r ON r.id = f.region_id
                              WHERE vo.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch() ?: null;
        if ($row) $row['voter_phone'] = decrypt_value($row['voter_phone']);
        return $row;
    }

    public static function findByOrder(string $orderNumber): ?array
    {
        $stmt = db()->prepare("SELECT vo.*, f.full_name AS finalist_name, f.contestant_number, f.category, f.photo, r.name AS region_name
                              FROM vote_orders vo JOIN finalists f ON f.id = vo.finalist_id JOIN regions r ON r.id = f.region_id
                              WHERE vo.order_number = ? LIMIT 1");
        $stmt->execute([$orderNumber]);
        $row = $stmt->fetch() ?: null;
        if ($row) $row['voter_phone'] = decrypt_value($row['voter_phone']);
        return $row;
    }

    public static function attachSession(int $orderId, array $session): void
    {
        $stmt = db()->prepare("UPDATE vote_orders SET xendit_session_id = ?, payment_link_url = ?, payment_status = 'PENDING', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$session['payment_session_id'] ?? $session['id'] ?? null, $session['payment_link_url'] ?? null, $orderId]);
    }

    public static function markPaidByReference(string $referenceId, array $payload): bool
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM vote_orders WHERE order_number = ? FOR UPDATE');
            $stmt->execute([$referenceId]);
            $order = $stmt->fetch();
            if (!$order) throw new RuntimeException('Order tidak ditemukan.');
            if ($order['payment_status'] === 'PAID') {
                $pdo->commit();
                return false;
            }
            $amount = (int) ($payload['amount'] ?? $payload['request_amount'] ?? $order['amount_snapshot']);
            if ($amount !== (int) $order['amount_snapshot']) throw new RuntimeException('Nominal pembayaran tidak sesuai.');

            $paymentId = $payload['payment_id'] ?? null;
            $paymentRequestId = $payload['payment_request_id'] ?? null;
            $channelCode = $payload['channel_code'] ?? null;
            $stmt = $pdo->prepare("UPDATE vote_orders SET payment_status='PAID', xendit_payment_id=?, xendit_payment_request_id=?, payment_channel=?, paid_at=NOW(), updated_at=NOW() WHERE id=?");
            $stmt->execute([$paymentId, $paymentRequestId, $channelCode, $order['id']]);

            $stmt = $pdo->prepare("INSERT INTO point_ledgers (event_id, finalist_id, vote_order_id, transaction_type, points, description, created_at)
                                   SELECT ?, ?, ?, 'VOTE', ?, ?, NOW() FROM DUAL
                                   WHERE NOT EXISTS (SELECT 1 FROM point_ledgers WHERE vote_order_id = ? AND transaction_type = 'VOTE')");
            $stmt->execute([$order['event_id'], $order['finalist_id'], $order['id'], $order['total_points_snapshot'], 'Poin transaksi ' . $order['order_number'], $order['id']]);
            $pdo->commit();
            audit('PAYMENT_PAID', 'vote_orders', (int) $order['id'], ['status'=>$order['payment_status']], ['status'=>'PAID','payment_id'=>$paymentId]);
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function markStatusByReference(string $referenceId, string $status): void
    {
        $allowed = ['FAILED','EXPIRED','CANCELED'];
        if (!in_array($status, $allowed, true)) return;
        $stmt = db()->prepare("UPDATE vote_orders SET payment_status=?, updated_at=NOW() WHERE order_number=? AND payment_status <> 'PAID'");
        $stmt->execute([$status, $referenceId]);
    }
}
