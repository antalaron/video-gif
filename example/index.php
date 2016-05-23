<?php

use Antalaron\VideoGif\VideoGif;

require __DIR__.'/../vendor/autoload.php';

/**
 * @var VideoGif
 */
$videoGif = new VideoGif();

try {
    $videoGif->create('example/video.mp4', 'example/video.gif', null, null, 500, 500);
} catch (Exception $e) {
    echo $e->getMessage()."\n";
}
