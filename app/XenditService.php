<?php

declare(strict_types=1);

final class XenditService
{
    public static function createPaymentSession(array $order): array
    {
        $secret = (string) app_config('xendit.secret_key');
        if ($secret === '') throw new RuntimeException('Xendit Secret Key belum dikonfigurasi.');

        $names = preg_split('/\s+/', trim((string) $order['voter_name'])) ?: [];
        $given = array_shift($names) ?: 'Pemilih';
        $surname = implode(' ', $names);
        $referenceCustomer = preg_replace('/[^A-Za-z0-9]/', '', 'cust' . $order['id'] . substr($order['voter_phone_hash'], 0, 10));
        $payload = [
            'reference_id' => $order['order_number'],
            'session_type' => 'PAY',
            'mode' => 'PAYMENT_LINK',
            'amount' => (int) $order['amount_snapshot'],
            'currency' => 'IDR',
            'country' => 'ID',
            'capture_method' => 'AUTOMATIC',
            'customer' => [
                'reference_id' => $referenceCustomer,
                'type' => 'INDIVIDUAL',
                'mobile_number' => $order['voter_phone'],
                'individual_detail' => [
                    'given_names' => preg_replace('/[^A-Za-z0-9 ]/', '', $given) ?: 'Pemilih',
                    'surname' => preg_replace('/[^A-Za-z0-9 ]/', '', $surname) ?: null,
                ],
            ],
            'items' => [[
                'reference_id' => 'vote-' . $order['finalist_id'],
                'type' => 'DIGITAL_SERVICE',
                'name' => 'E-Voting Finalis ' . $order['finalist_name'],
                'net_unit_amount' => (int) $order['amount_snapshot'],
                'quantity' => 1,
                'category' => 'E-VOTING',
                'description' => $order['package_name_snapshot'] . ' untuk Finalis ' . $order['finalist_name'],
            ]],
            'allowed_payment_channels' => app_config('xendit.allowed_channels', []),
            'expires_at' => (new DateTimeImmutable($order['expires_at']))->setTimezone(new DateTimeZone('UTC'))->format(DATE_ATOM),
            'locale' => 'id',
            'description' => 'Dukungan e-voting untuk Finalis ' . $order['finalist_name'] . ' (' . $order['order_number'] . ')',
            'success_return_url' => url('payment-status.php?order=' . urlencode($order['order_number'])),
            'cancel_return_url' => url('payment-cancel.php?order=' . urlencode($order['order_number'])),
            'metadata' => [
                'order_id' => (string) $order['id'],
                'event_id' => (string) $order['event_id'],
                'finalist_id' => (string) $order['finalist_id'],
            ],
        ];
        if (!$surname) unset($payload['customer']['individual_detail']['surname']);
        if (empty($payload['allowed_payment_channels'])) unset($payload['allowed_payment_channels']);

        return self::request('POST', '/sessions', $payload);
    }

    public static function getSession(string $sessionId): array
    {
        return self::request('GET', '/sessions/' . rawurlencode($sessionId));
    }

    private static function request(string $method, string $endpoint, array $payload = []): array
    {
        $url = app_config('xendit.api_url') . $endpoint;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_USERPWD => app_config('xendit.secret_key') . ':',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_POSTFIELDS => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false) throw new RuntimeException('Tidak dapat menghubungi Xendit: ' . $error);
        $data = json_decode($response, true);
        if ($status < 200 || $status >= 300) {
            $message = $data['message'] ?? $data['error_code'] ?? 'Permintaan pembayaran ditolak.';
            throw new RuntimeException('Xendit: ' . $message);
        }
        return is_array($data) ? $data : [];
    }
}
