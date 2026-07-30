<?php
require __DIR__.'/app/bootstrap.php';$order=(string)($_GET['order']??'');if($order){VoteService::markStatusByReference($order,'CANCELED');flash('warning','Proses pembayaran dibatalkan. Anda dapat membuat transaksi baru atau melanjutkan transaksi jika tautannya masih aktif.');}redirect('payment-status.php?order='.urlencode($order));
