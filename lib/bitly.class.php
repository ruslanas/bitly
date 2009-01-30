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

    public function Bitly($login, $apiKey)
    {
        $this->login = $login;
        $this->apiKey = $apiKey;
        $this->statusCode = 'OK';
        $this->errorMessage = '';
        $this->errorCode = '';
    	return true;
    }

    private function setError($message, $code = 101)
    {
    	$this->errorCode = $code;
        $this->errorMessage = $message;
        $this->statusCode = 'ERROR';
    }
    
    public function validAction($action)
    {
        if( in_array($action, $this->validActions)) {
            return true;
        }
        $this->setError("Undefined method $action", 202);
    	return false;
    }

    public function error()
    {
        $ret = array(
            "errorCode" => $this->errorCode,
            "errorMessage" => $this->errorMessage,
            "statusCode" => $this->statusCode
            );
        if( $this->statusCode == 'OK') {
            $ret['results'] = array();
        }
        return json_encode($ret);
    }

    public function shorten($message)
    {

        $postFields = '';
        preg_match_all("/http(s?):\/\/[^( |$|\]|,|\\\)]+/i", $message, $matches);
        
        for($i=0;$i<sizeof( $matches[0]);$i++) {
            $curr = $matches[0][$i];
            // ignore bitly urls
            if( !strstr($curr, 'http://bit.ly')) {
                $postFields .= '&longUrl=' . urlencode( $curr);
            }
        }

        // nothing to shorten, return empty result
        if( !strlen($postFields)) {
            return $this->error();
        }
        return $this->process('shorten', $postFields);
    }

    public function expand($message)
    {
        $postFields = '&hash=' . $this->getHash($message);
    	return $this->process('expand', $postFields);
    }

    public function info($bitlyUrl)
    {
        $hash = $this->getHash($bitlyUrl);
        $postFields = '&hash=' . $hash;
        return $this->process('info', $postFields);
    }

    public function stats($bitlyUrl)
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

    public function setReturnFormat($format)
    {
    	$this->format = $format;
        return $this->format;
    }

    // expect url, shortened url or hash
    public function getHash($message)
    {
        // if url and not bit.ly get shortened first
        if( strstr($message, 'http://') && !strstr($message, 'http://bit.ly')) {
            $message = $this->shortenSingle($message);
        }
        $hash = str_replace('http://bit.ly/', '', $message);
        return $hash;
    }
    
    public function shortenSingle($url)
    {
        $postFields = '&longUrl=' . $url;
    	$data = json_decode( $this->process('shorten', $postFields), true);
        return $data['results'][$url]['shortUrl'];
    }
}
?>
