<?php
/*
Copyright © 2008, 2009 Ruslanas Balčiūnas
Email: ruslanas.com@gmail.com
Blog: http://bitly.blogspot.com/
http://bitly.googlecode.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

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
$action = $_POST['action'];
$url = $_POST['url'];

if( $bitly->validAction($action)) {
    echo $bitly->{$action}($url);
} else {
    echo $bitly->error();
}
?>
