<?php
/*
Author: Ruslanas Balčiūnas
http://code.ruslanas.com
*/

class Bitly {
    /**
     * Adress of web service
     *
     * @var string
     */
    protected $api = 'http://api.bit.ly/';
    private $format = 'json';
    private $version = '2.0.1';
    private $validActions = array('shorten', 'stats', 'info', 'expand');

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
        $this->errorMessage = "Undefined method $action";
    	return false;
    }

    function error()
    {
        $ret = array(
            "errorCode" => 202,
            "errorMessage" => $this->errorMessage,
            "statusCode" => "ERROR"
            );
    	return json_encode($ret);
    }
    
    function shorten($message)
    {

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
        return $this->process('shorten', $postFields);
    }

    /**
     * Expand hash or url
     *
     */
    function expand($message)
    {
        if( strstr($message, 'http://bit.ly')) {
            $postFields = '&shortUrl=' . $message;
        } else {
            $postFields = '&hash=' . $message;
        }
    	return $this->proccess('expand', $postFields);
    }

    private function process($action, $postFields) {
        $ch = curl_init($this->api . $action); 
        
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

    /**
     * Accept hash, shortUrl or array as argument
     * 
     * Return array
     */
    function info($bitlyUrl)
    {
        $bitlyUrl = str_replace('http://bit.ly/', '', $bitlyUrl);
        $postFields = '&hash=' . $bitlyUrl;
        return $this->process('info', $postFields);
    }
    
    function stats($shortUrl)
    {
        $postFields = '&shortUrl=' . $shortUrl;
        return $this->process('stats', $postFields);
    }
    
    function setReturnFormat($format = 'json')
    {
    	$this->format = $format;
        return $this->format;
    }
}
?>
