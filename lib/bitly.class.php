<?php
/*
Author: Ruslanas Balčiūnas
http://code.ruslanas.com
*/

class Bitly {

    function Bitly($login, $apiKey, $version = '2.0.1')
    {
        $this->login = $login;
        $this->apiKey = $apiKey;
        $this->version = $version;
        $this->format = 'json';
    	return true;
    }

    function shorten($message)
    {
        $url = 'http://api.bit.ly/shorten';

        $postFields = '';
        
        preg_match_all("/http(s?):\/\/[^( |$|\]|,|\"|')]+/", $message, $matches);
        
        for($i=0;$i<sizeof($matches[0]);$i++) {
            $curr = $matches[0][$i];
            // do not shorten bitly urls
            if( !strstr($curr, 'http://bit.ly')) {
                $postFields .= '&longUrl=' . urlencode( $curr);
            }
        }
        if( !strlen($postFields)) {
            return false;
        }
        return $this->process($url, $postFields);
    }

    function process($url, $postFields) {
        $ch = curl_init($url); 
        
        $postFields = 'version=' . $this->version . $postFields;
        $postFields .= '&format=' . $this->format;

        curl_setopt($ch, CURLOPT_USERPWD, $this->login . ':' . $this->apiKey);
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postFields);
        
        $response = curl_exec($ch); 
         
        curl_close($ch); 
        
        return $response[0];
    }

    function info($bitlyUrl)
    {
        $url = 'http://api.bit.ly/info';

        // validate url
        if( !strstr($bitlyUrl, 'http://bit.ly')) {
            return false;
        }
        $a = split('\/', $bitlyUrl);
        $hash = $a[ sizeof($a) - 1];
        $postFields = '&hash=' . $hash;
        return $this->process($url, $postFields);
    }
    
    function stats($shortUrl)
    {
    	$url = 'http://api.bit.ly/stats';
        $postFields = '&shortUrl=' . $shortUrl;
        return $this->process($url, $postFields);
    }
    
    function setReturnFormat($format = 'json')
    {
    	$this->format = $format;
        return $this->format;
    }
}
?>
