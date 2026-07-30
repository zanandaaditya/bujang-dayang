<?php

declare(strict_types=1);

final class RateLimiter
{
    public static function hit(string $action, string $identity, int $maxHits, int $windowSeconds): void
    {
        $hash = hash('sha256', $identity);
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM rate_limits WHERE action_key=? AND identity_hash=? FOR UPDATE');
            $stmt->execute([$action,$hash]);
            $row = $stmt->fetch();
            $now = new DateTimeImmutable('now');
            if (!$row) {
                $pdo->prepare('INSERT INTO rate_limits(action_key,identity_hash,window_started_at,hit_count,updated_at) VALUES(?,?,NOW(),1,NOW())')->execute([$action,$hash]);
            } else {
                $started = new DateTimeImmutable($row['window_started_at']);
                if ($now->getTimestamp() - $started->getTimestamp() >= $windowSeconds) {
                    $pdo->prepare('UPDATE rate_limits SET window_started_at=NOW(),hit_count=1,updated_at=NOW() WHERE id=?')->execute([$row['id']]);
                } elseif ((int)$row['hit_count'] >= $maxHits) {
                    throw new RuntimeException('Terlalu banyak transaksi dibuat dari perangkat ini. Silakan coba kembali beberapa saat lagi.');
                } else {
                    $pdo->prepare('UPDATE rate_limits SET hit_count=hit_count+1,updated_at=NOW() WHERE id=?')->execute([$row['id']]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
