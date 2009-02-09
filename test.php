<?php

require_once('lib/bitly.class.php');
include('config.php');

$bitly = new Bitly($login, $apiKey);

$bitly->setReturnFormat('xml');
try {
    $bitly->error();
} catch (Exception $e) {
    echo '<div>' . $e->getMessage() . '</div>';
}

$url = $bitly->shortenSingle('http://www.ruslanas.com');

//$bitly->setReturnFormat('xml');
//echo $bitly->stats($url);
//exit;

// line below generates error
try {
    $bitly->getThumbnail();
} catch(Exception $e) {
    echo '<div>' . $e->getMessage() . '</div>';
}

$bitly->getInfoArray($url);
$bitly->getStatsArray($url);

echo '<h1>' . $bitly->getTitle() . '</h1>';
echo $bitly->getClicks() . ' clicks<br/>';
echo '<img src="' . $bitly->getThumbnail('medium') . '"/>';

echo '<h2>Expanded data</h2>';
echo 'Expanded: ' . $bitly->expandSingle($url);

?>
