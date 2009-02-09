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

$url = $bitly->shortenSingle('http://www.ruslanas.com');

//$bitly->setReturnFormat('xml');
//echo $bitly->stats($url);
//exit;
$bitly->getInfoArray($url);
$bitly->getStatsArray($url);

echo '<h1>' . $bitly->getTitle() . '</h1>';
echo $bitly->getClicks() . ' clicks<br/>';
echo '<img src="' . $bitly->getThumbnail('medium') . '"/>';

echo '<h2>Expanded data</h2>';
echo 'Expanded: ' . $bitly->expandSingle($url);

?>
