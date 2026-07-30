<?php
require dirname(__DIR__).'/app/bootstrap.php';header('Content-Type: application/json');$order=(string)($_GET['order']??'');$row=VoteService::findByOrder($order);if(!$row){http_response_code(404);echo json_encode(['message'=>'Not found']);exit;}echo json_encode(['order_number'=>$row['order_number'],'status'=>$row['payment_status'],'paid_at'=>$row['paid_at']]);
