<?php

declare(strict_types=1);

final class LeaderboardService
{
    /**
     * Peringkat internal. Nilai total_points hanya untuk proses server dan halaman Superadmin.
     */
    public static function rankings(int $eventId, string $category, ?int $limit = null, bool $respectFreeze = true): array
    {
        if ($respectFreeze) {
            $eventStmt = db()->prepare('SELECT leaderboard_frozen FROM events WHERE id=?');
            $eventStmt->execute([$eventId]);
            if ((int) $eventStmt->fetchColumn() === 1) {
                $snapshotStmt = db()->prepare(
                    'SELECT snapshot_data FROM leaderboard_snapshots WHERE event_id=? AND category=? ORDER BY id DESC LIMIT 1'
                );
                $snapshotStmt->execute([$eventId, $category]);
                $snapshot = $snapshotStmt->fetchColumn();
                if ($snapshot) {
                    $rows = json_decode((string) $snapshot, true);
                    if (is_array($rows)) {
                        return $limit !== null ? array_slice($rows, 0, $limit) : $rows;
                    }
                }
            }
        }

        $sql = "SELECT f.id, f.contestant_number, f.full_name, f.slug, f.photo, f.category,
                       r.name AS region_name, COALESCE(pl.total_points, 0) AS total_points,
                       pl.last_point_at
                FROM finalists f
                JOIN regions r ON r.id = f.region_id
                LEFT JOIN (
                    SELECT event_id, finalist_id, SUM(points) AS total_points,
                           MAX(CASE WHEN points > 0 THEN created_at END) AS last_point_at
                    FROM point_ledgers
                    GROUP BY event_id, finalist_id
                ) pl ON pl.finalist_id = f.id AND pl.event_id = f.event_id
                WHERE f.event_id = ? AND f.category = ? AND f.is_active = 1
                ORDER BY total_points DESC, last_point_at ASC, f.contestant_number ASC";

        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, (int) $limit);
        }

        $stmt = db()->prepare($sql);
        $stmt->execute([$eventId, $category]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $i => &$row) {
            $row['rank'] = $i + 1;
        }
        unset($row);

        return $rows;
    }

    /**
     * Data aman untuk halaman publik.
     * Persentase dihitung per kelompok: nilai finalis / total nilai kelompok × 100.
     * Angka internal total_points dihapus agar tidak terekspos melalui HTML/API publik.
     */
    public static function publicRankings(
        int $eventId,
        string $category,
        ?int $limit = null,
        bool $respectFreeze = true
    ): array {
        $rows = self::rankings($eventId, $category, null, $respectFreeze);
        $categoryTotal = array_sum(array_map(
            static fn(array $row): int => max(0, (int) ($row['total_points'] ?? 0)),
            $rows
        ));

        foreach ($rows as &$row) {
            $internalValue = max(0, (int) ($row['total_points'] ?? 0));
            $row['support_percentage'] = $categoryTotal > 0
                ? round(($internalValue / $categoryTotal) * 100, 2)
                : 0.0;
            unset($row['total_points'], $row['last_point_at']);
        }
        unset($row);

        return $limit !== null ? array_slice($rows, 0, max(1, $limit)) : $rows;
    }

    public static function publicPercentageForFinalist(
        int $eventId,
        string $category,
        int $finalistId,
        bool $respectFreeze = true
    ): float {
        foreach (self::publicRankings($eventId, $category, null, $respectFreeze) as $row) {
            if ((int) $row['id'] === $finalistId) {
                return (float) $row['support_percentage'];
            }
        }

        return 0.0;
    }

    public static function createSnapshot(int $eventId, string $category): void
    {
        $rows = self::rankings($eventId, $category, null, false);
        $stmt = db()->prepare(
            'INSERT INTO leaderboard_snapshots(event_id,category,snapshot_data,created_at) VALUES(?,?,?,NOW())'
        );
        $stmt->execute([
            $eventId,
            $category,
            json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public static function totals(int $eventId): array
    {
        $stmt = db()->prepare("SELECT
            COALESCE(SUM(CASE WHEN vo.payment_status = 'PAID' THEN vo.amount_snapshot ELSE 0 END), 0) AS revenue,
            COALESCE(SUM(CASE WHEN vo.payment_status = 'PAID' THEN vo.total_points_snapshot ELSE 0 END), 0) AS paid_points,
            SUM(vo.payment_status = 'PAID') AS paid_transactions,
            SUM(vo.payment_status IN ('CREATED','PENDING')) AS pending_transactions,
            SUM(vo.payment_status = 'FAILED') AS failed_transactions,
            SUM(vo.payment_status = 'EXPIRED') AS expired_transactions
            FROM vote_orders vo WHERE vo.event_id = ?");
        $stmt->execute([$eventId]);

        return $stmt->fetch() ?: [];
    }
}
