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
ini_set("soap.wsdl_cache_enabled", "0");
include('lib/bitly.class.php');

class BitlyService { 
    
    function getShortened($url) {
        include('config.php');
        $bitly = new Bitly($login, $apiKey);
        return $bitly->shortenSingle($url);
    }
}

$server = new SoapServer("http://ruslanas.com/bitly/bitly.wsdl");
$server->setClass("BitlyService");
$server->handle();
?>
