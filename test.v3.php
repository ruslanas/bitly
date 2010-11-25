<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"> 
    <title>Bitly V3 test</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table td {
            border: 1px solid;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
<?php

require_once('lib/bitly.v3.class.php');
include('config.php');

$urls = array('http://u.opposeto.com/eSLwEP', 'http://u.opposeto.com/hAKSbX');

$bitly = new Bitly($login, $apiKey);

//print_r($bitly->clicksByMinute('http://u.opposeto.com/eSLwEP'));
//print_r($bitly->clicksByDay('http://u.opposeto.com/eSLwEP'));

if($bitly->isProDomain('u.opposeto.com')) {
    echo '<p>This domain is pro!</p>';
}

// this line generates error INVALID_URI
if(!$bitly->shorten('http:/\yahoo.com')) {
    echo '<div class="error">'.$bitly->getError().'</div>';
}

echo $bitly->shortenSingle('http://aerodromes.eu');

$data = $bitly->expand('dT0uqL');
echo '<p>'.$data[0]->long_url.'</p>';

$data = $bitly->lookup(array('http://aerodromes.eu', 'http://google.com'));

if(!$data) {
    echo 'Error: '.$bitly->getErrorCode().' "'.$bitly->getError().'"';
}

foreach($data as $rowObj) {

    // check if found
    if(empty($rowObj->error)) {
        $url = $rowObj->short_url;
        $title = $bitly->getTitle($url)
            or trigger_error('Error: '.$bitly->getErrorCode());
        echo '<h1><a href="'.$url.'">'.$title.'</a></h1>';
        $clicks = $bitly->clicks($url);
        echo '<p>Global clicks: '.$clicks[0]->global_clicks.'</p>';
        echo '<table>';
        $referrers = $bitly->referrers($url);
        foreach($referrers as $refObj) {
            echo '<tr><td>'.$refObj->referrer.'</td><td>'.$refObj->clicks.'</td></tr>';
        }
        echo '</table>';
    } else {
        echo $rowObj->error;
    }
}

?>
</body>
</html>
