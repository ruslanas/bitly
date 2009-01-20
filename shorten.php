<?php
/*
Author: Ruslanas Balčiūnas
http://code.ruslanas.com/
*/

include('config.php');
include('lib/bitly.class.php');

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
