<?php
/**
 * @author Ruslanas Balčiūnas <ruslanas.com@gmail.com>
 * @link http://bitly.googlecode.com
 */
require_once('bitly.class.php');
class Tinyurl extends Bitly
{
    function __construct()
    {
        $this->_api = 'http://tinyurl.com/';
    }

    public function shortenSingle($url)
    {
        return $this->process('api-create.php', '&url=' . $url);
    }
}
