<?php
/**
 * @author Ruslanas Balčiūnas <ruslanas.com@gmail.com>
 * @link http://bitly.googlecode.com
 */
require_once('bitly.class.php');

class Zzgd extends Bitly
{
    function __construct()
    {
        $this->_api = 'http://zz.gd/';
    }
    public function shortenSingle($url)
    {
        return $this->process('api-create.php', '&url=' . $url);
    }
    public function expandSingle($url)
    {
        return $this->process('api-decrypt.php', '&url=' . $url);
    }
}
