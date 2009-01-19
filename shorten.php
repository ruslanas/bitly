<?php
/*
Author: Ruslanas Balčiūnas
http://www.ruslanas.com/
*/

// configuration
$apiKey = 'R_d4d0b8149ca5d9e8f69dce40e0628305';
$login = 'ruslanas';
$version = '2.0.1';

include('../lib/bitly.class.php');

$bitly = new Bitly($login, $apiKey);

if( !empty( $_POST['longUrl'])) {
    echo $bitly->shorten( $_POST['longUrl']);
    exit;
}

if( !empty( $_POST['bitlyUrl'])) {
    echo $bitly->info( $_POST['bitlyUrl']);
    exit;
}
?>
