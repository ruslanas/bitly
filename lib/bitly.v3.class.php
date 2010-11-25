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

/**
 * Bit.ly API
 *
 * <code>
 * $bitly = new Bitly($login, $apiKey);
 * if($bitly->shortenSingle('http://www.opposedto.com/')) {
 *     $bitly->error();
 * }
 * </code>
 */
class Bitly
{

    protected $_api = 'http://api.bit.ly/v3/';
    private $_login;
    private $_apiKey;
    private $_format = 'json';

    private $errorCode = false;
    private $errorMessage = false;

    /**
     * Initialize
     * @param string $login
     * @param string $apiKey
     */
    public function __construct($login, $apiKey)
    {
        $this->_login = $login;
        $this->_apiKey = $apiKey;
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
    }

    /**
     * Return JSON encoded error message
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }
    public function getErrorCode() {
        return $this->errorCode;
    }

    /**
     * Shorten message with any number of links
     * @param string $message
     * @return string
     */
    public function shorten($url)
    {
        if(gettype($url) === 'array') {
            throw new Exception('Array not supported.');
        }
        $data = $this->process('shorten', $this->prepareParams($url, 'longUrl'));
        return $data;
    }
    /**
     * Get long URL
     * @param string $message
     * @return string
     */
    public function expand($bitlyUrl)
    {
        $params = $this->prepareParams($bitlyUrl);
        $data = $this->process('expand', $params);
        return $data->expand;
    }

    /**
     *
     * @param mixed $url
     * @return array
     */
    public function clicks($url)
    {
        $data = $this->process('clicks', $this->prepareParams($url));
        return $data->clicks;
    }
    public function referrers($url) {
        $data = $this->process('referrers', $this->prepareParams($url));
        return $data->referrers;
    }
    public function countries($url) {
        return $this->process('countries', 'shortUrl='.urlencode($url));
    }
    public function clicksByMinute($url) {
        $data = $this->process('clicks_by_minute', $this->prepareParams($url));
        return $data->clicks_by_minute;
    }
    public function clicksByDay($url) {
        $data = $this->process('clicks_by_day', $this->prepareParams($url));
        return $data->clicks_by_day;
    }

    /**
     *
     * @param string $domain
     * @return int
     */
    public function isProDomain($domain) {
        $data = $this->process('bitly_pro_domain', 'domain='.urlencode($domain));
        return $data->bitly_pro_domain;
    }
    public function lookup($url) {
        $data = $this->process('lookup', $this->prepareParams($url, 'url'));
        return $data->lookup;
    }

    private function prepareParams($bitlyArray, $name = 'shortUrl') {
        $params = '';
        if(gettype($bitlyArray) === 'array') {
            foreach($bitlyArray as $url) {
                if(strstr($url, '.')) {
                    $params .= $name.'='.urldecode($url).'&';
                } else {
                    $params .= 'hash='.urlencode($url);
                }
            }
            rtrim($url, '&');
        } else {
            if(strstr($bitlyArray, '.')) {
                $params .= $name.'='.urlencode($bitlyArray);
            } else {
                $params .= 'hash='.urlencode($bitlyArray);
            }
        }
        return $params;
    }
    /**
     * Get URL info
     * @param string $bitlyUrl
     * @return string
     */
    public function info($bitlyUrl)
    {
        $params = $this->prepareParams($bitlyUrl);
        return $this->process('info', $params);
    }

    /**
     * @param string $action
     * @param string $postFields
     * @return string
     * @throws Exception If cURL session fails.
     */
    protected function process($action, $postFields)
    {
        $postFields .= '&apiKey='.$this->_apiKey.'&login='.$this->_login;
        $postFields .= '&format=' . $this->_format;

        $url = $this->_api.$action.'?'.$postFields;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === FALSE) {
            throw new Exception('bit.ly: No response');
        }
        curl_close($ch);
        $data = json_decode($response);
        if($data->status_code != 200) {
            $this->setError($data->status_txt, $data->status_code);
            return false;
        }
        return $data->data;
    }

    /**
     * Shorten URL.
     *
     * @param string $message
     * @return string
     */
    public function shortenSingle($message)
    {
        $data = $this->shorten($message);
        return $data->url;
    }

    /**
     *
     * @return string
     */
    public function getTitle($url)
    {
        $data = $this->info($url);
        return $data->info[0]->title;
    }
}
