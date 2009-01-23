<?php
/*
Author: Ruslanas Balčiūnas
http://code.ruslanas.com/
*/

include('config.php');
include('lib/bitly.class.php');

$bitly = new Bitly($login, $apiKey);

$supported = array('shorten', 'stat', 'info');

if( in_array($_POST['action']), $supported) {
    echo $bitly->{$_POST['action']}($_POST['url']);
} else {
    die('Unsupported method!');
}
?>
