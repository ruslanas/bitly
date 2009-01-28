<?php
/*
Author: Ruslanas Balčiūnas
http://bitly.googlecode.com
*/

class Bitly {

    protected $api = 'http://api.bit.ly/';
    private $format = 'json';
    private $version = '2.0.1';
    private $validActions = array(
        'shorten',
        'stats',
        'info',
        'expand'
        );

    function Bitly($login, $apiKey)
    {
        $this->login = $login;
        $this->apiKey = $apiKey;
    	return true;
    }

    function validAction($action)
    {
        if( in_array($action, $this->validActions)) {
            return true;
        }
        $this->errorCode = 202;
        $this->errorMessage = "Undefined method $action";
    	return false;
    }

    function error()
    {
        if( empty( $this->errorMessage) && empty( $this->errorCode)) {
            return false;
        }
        $ret = array(
            "errorCode" => $this->errorCode,
            "errorMessage" => $this->errorMessage,
            "statusCode" => "ERROR"
            );
    	return json_encode($ret);
    }

    function shorten($message)
    {

        $postFields = '';
        
        preg_match_all("/http(s?):\/\/[^( |$|\]|,|\"|')]+/i", $message, $matches);
        
        for($i=0;$i<sizeof( $matches[0]);$i++) {
            $curr = $matches[0][$i];
            // ignore bitly urls
            if( !strstr($curr, 'http://bit.ly')) {
                $postFields .= '&longUrl=' . urlencode( $curr);
            }
        }

        if( !strlen($postFields)) {
            return false;
        }
        return $this->process('shorten', $postFields);
    }

    function expand($message)
    {
        $postFields = '&hash=' . $this->getHash($message);
    	return $this->process('expand', $postFields);
    }

    function info($bitlyUrl)
    {
        $hash = $this->getHash($bitlyUrl);
        $postFields = '&hash=' . $hash;
        return $this->process('info', $postFields);
    }

    function stats($bitlyUrl)
    {
        // Take only first hash or url. Ignore others.
        $a = split(',', $bitlyUrl);
        $postFields = '&hash=' . $this->getHash($a[0]);
        return $this->process('stats', $postFields);
    }
    
    private function process($action, $postFields) {
        $ch = curl_init( $this->api . $action); 
        
        $postFields = 'version=' . $this->version . $postFields;
        $postFields .= '&format=' . $this->format;

        curl_setopt($ch, CURLOPT_USERPWD, $this->login . ':' . $this->apiKey);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch); 
         
        curl_close($ch); 
        
        return $response;
    }

    function setReturnFormat($format)
    {
    	$this->format = $format;
        return $this->format;
    }

    // expect url, shortened url or hash
    function getHash($message)
    {
        // if url and not bit.ly get shortened first
        if( strstr($message, 'http://') && !strstr($message, 'http://bit.ly')) {
            $message = $this->shortenSingle($message);
        }
        $hash = str_replace('http://bit.ly/', '', $message);
        return $hash;
    }
    
    function shortenSingle($url)
    {
        $postFields = '&longUrl=' . $url;
    	$data = json_decode( $this->process('shorten', $postFields), true);
        return $data['results'][$url]['shortUrl'];
    }
}
?>
