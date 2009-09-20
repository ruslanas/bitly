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

Usage:
$bitly = new Bitly($login, $apiKey);
$short = $bitly->shortenSingle('http://bitly.googlecode.com');
$long = $bitly->expandSingle($short);
print_r( $bitly->getStatsArray($short));
print_r( $bitly->getInfoArray($long));

*/

class Bitly {

    protected $api = 'http://api.bit.ly/';
    private $login;
    private $apiKey;
    private $format = 'json';
    private $version = '2.0.1';
    private $validActions = array(
        'shorten',
        'stats',
        'info',
        'expand'
        );

    /**
     *
     * @param string $login
     * @param string $apiKey
     */
    public function __construct($login, $apiKey)
    {
        $this->login = $login;
        $this->apiKey = $apiKey;
        $this->statusCode = 'OK';
        $this->errorMessage = '';
        $this->errorCode = '';
    }

    /**
     *
     * @param string $message
     * @param int $code
     */
    private function setError($message, $code = 101)
    {
    	$this->errorCode = $code;
        $this->errorMessage = $message;
        $this->statusCode = 'ERROR';
    }

    /**
     * Check if action supported
     * @param string $action
     * @return bool
     */
    public function validAction($action)
    {
        if( in_array($action, $this->validActions)) {
            return true;
        }
        $this->setError("Undefined method $action", 202);
    	return false;
    }

    /**
     * Return JSON encoded error message
     * @return string
     */
    public function error()
    {
        $ret = array(
            "errorCode" => $this->errorCode,
            "errorMessage" => $this->errorMessage,
            "statusCode" => $this->statusCode
            );

        // Function used for passing empty result sometimes.
        if( $this->statusCode == 'OK') {
            $ret['results'] = array();
        }
        if( $this->format == 'json') {
            return json_encode($ret);
        } else {
            throw new Exception('Unsupported format');
        }
    }

    /**
     *
     * @param string $message
     * @return string
     */
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

    /**
     *
     * @param string $message
     * @return string
     */
    public function expand($message)
    {
        $postFields = '&hash=' . $this->getHash($message);
    	return $this->process('expand', $postFields);
    }

    /**
     *
     * @param string $bitlyUrl
     * @return string
     */
    public function info($bitlyUrl)
    {
        $hash = $this->getHash($bitlyUrl);
        $postFields = '&hash=' . $hash;
        return $this->process('info', $postFields);
    }

    /**
     *
     * @param string $bitlyUrl
     * @return string
     */
    public function stats($bitlyUrl)
    {
        // Take only first hash or url. Ignore others.
        $a = split(',', $bitlyUrl);
        $postFields = '&hash=' . $this->getHash($a[0]);
        return $this->process('stats', $postFields);
    }

    /**
     *
     * @param string $action
     * @param string $postFields
     * @return string
     */
    protected function process($action, $postFields) {
        $ch = curl_init( $this->api . $action); 
        
        $postFields = 'version=' . $this->version . $postFields;
        $postFields .= '&format=' . $this->format;
        $postFields .= '&history=1';

        curl_setopt($ch, CURLOPT_USERPWD, $this->login . ':' . $this->apiKey);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch); 
         
        curl_close($ch); 
        
        return $response;
    }

    /**
     * Set return formal XML or JSON
     * @param string $format
     * @return string
     */
    public function setReturnFormat($format)
    {
        // needed for restoration
        $this->oldFormat = $this->format;
    	$this->format = $format;
        return $this->format;
    }

    /**
     *
     * @return string
     */
    private function restoreFormat()
    {
        if( !empty( $this->oldFormat)) {
            $this->format = $this->oldFormat;
        }
        return $this->format;
    }

    /**
     * Expect url, shortened url or hash
     *
     * @param string $message
     * @return string
     */
    public function getHash($message)
    {
        // if url and not bit.ly get shortened first
        if( strstr($message, 'http://') && !strstr($message, 'http://bit.ly')) {
            $message = $this->shortenSingle($message);
        }
        $hash = str_replace('http://bit.ly/', '', $message);
        return $hash;
    }

    /**
     *
     * @param string $message
     * @return string
     */
    public function shortenSingle($message)
    {
        $this->setReturnFormat('json');
    	$data = json_decode( $this->shorten($message), true);
        // return to previous state.
        $this->restoreFormat();
        
        // replace every long url with short one
        foreach($data['results'] as $url => $d) {
            $message = str_replace($url, $d['shortUrl'], $message);
        }
        return $message;
    }

    /**
     *
     * @param string $shortUrl
     * @return string
     */
    public function expandSingle($shortUrl)
    {
        $this->setReturnFormat('json');
    	$data = json_decode( $this->expand($shortUrl), true);
        $this->restoreFormat();
        return $data['results'][ $this->getHash($shortUrl)]['longUrl'];
    }

    /**
     *
     * @param string $url
     * @return array
     */
    public function getInfoArray($url)
    {
        $this->setReturnFormat('json');
    	$json = $this->info($url);
        $this->restoreFormat();
        $data = json_decode($json, true);

        $this->infoArray = array_pop( $data['results']);
        return $this->infoArray;
    }

    /**
     *
     * @param string $url
     * @return array
     */
    public function getStatsArray($url)
    {
        $this->setReturnFormat('json');
    	$json = $this->stats($url);
        $this->restoreFormat();
        $data = json_decode($json, true);
        $this->statsArray = $data['results'];
        return $this->statsArray;
    }

    /**
     *
     * @return int
     */
    public function getClicks()
    {
    	return $this->statsArray['clicks'];
    }

    /**
     * Get thumbnail (small, middle, large)
     * @param string $size
     * @return string
     */
    public function getThumbnail($size = 'small')
    {
        if( !in_array($size, array('small', 'medium', 'large'))) {
            throw new Exception('Invalid size value');
        }
        if( empty( $this->infoArray)) {
            throw new Exception('Info not loaded');
        }
    	return $this->infoArray['thumbnail'][$size];
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
    	return $this->infoArray['htmlTitle'];
    }
}
?>
