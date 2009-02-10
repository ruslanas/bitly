<?php
/*
Author: Ruslanas Balčiūnas
http://bitly.googlecode.com
*/
class Zzgd extends Bitly {
    function Zzgd()
    {
        $this->api = 'http://zz.gd/';
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
?>
