<?php
require dirname(__DIR__).'/app/bootstrap.php';
$secret=(string)($_GET['secret']??($_SERVER['HTTP_X_CRON_SECRET']??''));
$expected=(string)app_config('app.cron_secret','');
if($expected===''||!hash_equals($expected,$secret)){http_response_code(403);exit('Forbidden');}
$stmt=db()->query("SELECT * FROM vote_orders WHERE payment_status IN ('CREATED','PENDING') AND xendit_session_id IS NOT NULL ORDER BY id ASC LIMIT 100");
$result=['checked'=>0,'paid'=>0,'expired'=>0,'errors'=>[]];
foreach($stmt->fetchAll() as $order){$result['checked']++;try{$session=XenditService::getSession($order['xendit_session_id']);$status=$session['status']??'ACTIVE';if($status==='COMPLETED'){if(VoteService::markPaidByReference($order['order_number'],$session))$result['paid']++;}elseif($status==='EXPIRED'){VoteService::markStatusByReference($order['order_number'],'EXPIRED');$result['expired']++;}}catch(Throwable $e){$result['errors'][]=$order['order_number'].': '.$e->getMessage();}}
header('Content-Type: application/json');echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
