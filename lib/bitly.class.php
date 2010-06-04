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

/**
 * Bit.ly API
 */
class Bitly
{

    protected $_api = 'http://api.bit.ly/';
    private $_login;
    private $_apiKey;
    private $_format = 'json';
    private $_version = '2.0.1';
    private $_validActions = array(
        'shorten',
        'stats',
        'info',
        'expand'
        );

    /**
     * Initialize
     * @param string $login
     * @param string $apiKey
     */
    public function __construct($login, $apiKey)
    {
        $this->_login = $login;
        $this->_apiKey = $apiKey;
        $this->statusCode = 'OK';
        $this->errorMessage = '';
        $this->errorCode = '';
    }

    /**
     * Ser error message
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
        if (in_array($action, $this->_validActions)) {
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
        if ($this->statusCode == 'OK') {
            $ret['results'] = array();
        }
        if ($this->_format == 'json') {
            return json_encode($ret);
        } else {
            throw new Exception('Unsupported format');
        }
    }

    /**
     * Shorten message with any number of links
     * @param string $message
     * @return string
     */
    public function shorten($message)
    {

        $postFields = '';
        $pattern = "/http(s?):\/\/[^( |$|\]|,|\\\)]+/i";
        preg_match_all($pattern, $message, $matches);
        
        for ($i=0;$i<sizeof($matches[0]);$i++) {
            $curr = $matches[0][$i];
            // ignore bitly urls
            if (!strstr($curr, 'http://bit.ly')) {
                $postFields .= '&longUrl=' . urlencode($curr);
            }
        }

        // nothing to shorten, return empty result
        if (!strlen($postFields)) {
            return $this->error();
        }
        return $this->process('shorten', $postFields);
    }

    /**
     * Get long URL
     * @param string $message
     * @return string
     */
    public function expand($message)
    {
        $postFields = '&hash=' . $this->getHash($message);
        return $this->process('expand', $postFields);
    }

    /**
     * Get URL info
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
     * Get stats for URL
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
     * @param string $action
     * @param string $postFields
     * @return string
     */
    protected function process($action, $postFields)
    {
        $ch = curl_init($this->_api.$action);
        
        $postFields = 'version=' . $this->_version . $postFields;
        $postFields .= '&format=' . $this->_format;
        $postFields .= '&history=1';

        curl_setopt($ch, CURLOPT_USERPWD, $this->_login . ':' . $this->_apiKey);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch); 
        if ($response === FALSE) {
            throw new Exception('bit.ly: No response');
        }
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
        $this->oldFormat = $this->_format;
        $this->_format = $format;
        return $this->_format;
    }

    /**
     *
     * @return string
     */
    private function restoreFormat()
    {
        if (!empty($this->oldFormat)) {
            $this->_format = $this->oldFormat;
        }
        return $this->_format;
    }

    /**
     * Expect url, shortened url or hash.
     *
     * @param string $message
     * @return string
     */
    public function getHash($message)
    {
        // if url and not bit.ly get shortened first
        if (strstr($message, 'http://') && !strstr($message, 'http://bit.ly')) {
            $message = $this->shortenSingle($message);
        }
        $hash = str_replace('http://bit.ly/', '', $message);
        return $hash;
    }

    /**
     * Shorten URL.
     *
     * @param string $message
     * @return string
     */
    public function shortenSingle($message)
    {
        $this->setReturnFormat('json');
        $data = json_decode($this->shorten($message), true);
        // return to previous state.
        $this->restoreFormat();
        
        // replace every long url with short one
        foreach ($data['results'] as $url => $d) {
            $message = str_replace($url, $d['shortUrl'], $message);
        }
        return $message;
    }

    /**
     * Expand URL.
     *
     * @param string $shortUrl
     * @return string
     */
    public function expandSingle($shortUrl)
    {
        $this->setReturnFormat('json');
        $data = json_decode($this->expand($shortUrl), true);
        $this->restoreFormat();
        return $data['results'][ $this->getHash($shortUrl)]['longUrl'];
    }

    /**
     * Get URL information.
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

        $this->infoArray = array_pop($data['results']);
        return $this->infoArray;
    }

    /**
     * Get URL statistics.
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
     * Get URL clicks.
     *
     * @return int
     */
    public function getClicks()
    {
        return $this->statsArray['clicks'];
    }

    /**
     * Get thumbnail (small, middle, large).
     *
     * @param string $size
     * @return string
     */
    public function getThumbnail($size = 'small')
    {
        if (!in_array($size, array('small', 'medium', 'large'))) {
            throw new Exception('Invalid size value');
        }
        if (empty($this->infoArray)) {
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
