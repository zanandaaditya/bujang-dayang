<?php
require dirname(__DIR__) . '/app/bootstrap.php';
header('Content-Type: application/json');
$raw = file_get_contents('php://input') ?: '';
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['message'=>'Invalid JSON']);
    exit;
}
$event = (string)($payload['event'] ?? 'unknown');
$data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

// Payments API mengirim x-callback-token. Untuk webhook Payment Session yang tidak
// membawa token, endpoint memverifikasi session langsung ke API Xendit menggunakan
// Secret Key server sebelum memproses perubahan status.
$verified = false;
$token = (string)($_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '');
$expectedToken = (string)app_config('xendit.webhook_token');
if ($token !== '' && $expectedToken !== '' && hash_equals($expectedToken, $token)) {
    $verified = true;
} elseif (str_starts_with($event, 'payment_session.') && !empty($data['payment_session_id'])) {
    try {
        $remote = XenditService::getSession((string)$data['payment_session_id']);
        $expectedBusiness = (string)app_config('xendit.business_id', '');
        $businessMatches = $expectedBusiness === '' || hash_equals($expectedBusiness, (string)($remote['business_id'] ?? ''));
        $verified = $businessMatches
            && hash_equals((string)($remote['reference_id'] ?? ''), (string)($data['reference_id'] ?? ''))
            && hash_equals((string)($remote['status'] ?? ''), (string)($data['status'] ?? ''));
    } catch (Throwable) {
        $verified = false;
    }
}
if (!$verified) {
    http_response_code(401);
    echo json_encode(['message'=>'Webhook verification failed']);
    exit;
}

$webhookIdentifier = $data['payment_id'] ?? $data['payment_session_id'] ?? hash('sha256', $raw);
try {
    $stmt = db()->prepare("INSERT INTO payment_webhooks (event_name, webhook_identifier, payment_id, reference_id, payload, verification_status, processing_status, received_at) VALUES (?, ?, ?, ?, ?, 'VERIFIED', 'RECEIVED', NOW())");
    $stmt->execute([$event, $webhookIdentifier, $data['payment_id'] ?? null, $data['reference_id'] ?? null, $raw]);
    $webhookId = (int)db()->lastInsertId();
} catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
        http_response_code(200);
        echo json_encode(['message'=>'Duplicate acknowledged']);
        exit;
    }
    throw $e;
}
try {
    $reference = (string)($data['reference_id'] ?? '');
    if ($reference === '') throw new RuntimeException('Reference ID tidak tersedia.');
    if ($event === 'payment_session.completed' || $event === 'payment.succeeded' || ($event === 'payment.capture' && ($data['status'] ?? '') === 'SUCCEEDED')) {
        VoteService::markPaidByReference($reference, $data);
    } elseif ($event === 'payment_session.expired') {
        VoteService::markStatusByReference($reference, 'EXPIRED');
    } elseif ($event === 'payment.failure') {
        VoteService::markStatusByReference($reference, 'FAILED');
    }
    db()->prepare("UPDATE payment_webhooks SET processing_status='PROCESSED', processed_at=NOW() WHERE id=?")->execute([$webhookId]);
    http_response_code(200);
    echo json_encode(['message'=>'Webhook processed']);
} catch (Throwable $e) {
    db()->prepare("UPDATE payment_webhooks SET processing_status='FAILED', processing_error=?, processed_at=NOW() WHERE id=?")->execute([substr($e->getMessage(),0,1000),$webhookId]);
    file_put_contents(dirname(__DIR__).'/storage/logs/webhook.log','['.date('c').'] '.$e->getMessage().PHP_EOL,FILE_APPEND);
    http_response_code(500);
    echo json_encode(['message'=>'Processing failed']);
}
