<?php 
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Lhama.php';

ini_set('session.gc_maxlifetime', 3600 * 24);
session_set_cookie_params(3600 * 24);
session_start();

$lhama = new Lhama(); 

$lhama->handle(); 

