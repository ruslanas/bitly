<?php
/*
Author: Ruslanas Balčiūnas
http://code.ruslanas.com/
*/

include('config.php');
include('lib/bitly.class.php');

$bitly = new Bitly($login, $apiKey);

if( $bitly->validAction( $_POST['action'])) {
    echo $bitly->{$_POST['action']}($_POST['url']);
} else {
    echo $bitly->error();
}
?>
