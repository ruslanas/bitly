<?php
require_once('lib/bitly.class.php');
include('config.php');

$bitly = new Bitly($login, $apiKey);

$bitly->setReturnFormat('xml');
try {
    $bitly->error();
} catch (Exception $e) {
    echo $e->getMessage();
}

$url = $bitly->shortenSingle('http://bitly.googlecode.com');

//$bitly->setReturnFormat('xml');
//echo $bitly->stats($url);
//exit;
$info = $bitly->getInfoArray($url);

echo '<h1>' . $url . '</h1>';
echo '<img src="' . $info['thumbnail']['medium'] . '"/>';

$stats = $bitly->getStatsArray($url);

echo '<h2>Expanded data</h2>';
echo 'Expanded: ' . $bitly->expandSingle($url);

echo '<pre>';
print_r($stats);
echo '</pre>';

?>
