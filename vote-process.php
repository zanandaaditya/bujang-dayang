<?php
require __DIR__.'/app/bootstrap.php';
if(!is_post())redirect('evoting.php');
try{verify_csrf();$order=VoteService::createOrder($_POST);$session=XenditService::createPaymentSession($order);VoteService::attachSession((int)$order['id'],$session);$link=$session['payment_link_url']??null;if(!$link)throw new RuntimeException('Tautan pembayaran tidak diterima dari Xendit.');redirect($link);}catch(Throwable $e){remember_old_input();flash('danger',$e->getMessage());redirect('evoting.php');}
