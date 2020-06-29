<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->logout();
?>