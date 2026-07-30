<?php
require dirname(__DIR__).'/app/bootstrap.php';Auth::logout();flash('success','Anda telah keluar dari dashboard.');redirect('admin/login.php');
