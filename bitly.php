<?php
/*
Author: Ruslanas Balčiūnas
http://code.ruslanas.com/
*/

function errorHandler($errno, $errstr, $errfile, $errline)
{
    header('Content-Type: text/plain; charset="utf-8"');
    $ret = array(
        "errorCode" => 10001,
        "errorMessage" => $errstr,
        "statusCode" => "ERROR"
        );
    echo json_encode($ret);
    exit;
}

$oldErrorHandler = set_error_handler('errorHandler');

include('config.php');
include('lib/bitly.class.php');

$bitly = new Bitly($login, $apiKey);

if( $bitly->validAction( $_POST['action'])) {
    echo $bitly->{$_POST['action']}($_POST['url']);
} else {
    echo $bitly->error();
}
?>
