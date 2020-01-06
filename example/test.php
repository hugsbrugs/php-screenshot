<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Screenshot\Screenshot as Screenshot;


$data = realpath(__DIR__ . '/../');

define('HUG_SCREENSHOT_SAVE_PATH', $data.'/data/');
define('HUG_SCREENSHOT_PROVIDERS', $data.'/conf/providers.json');
define('HUG_SCREENSHOT_CACHE', 'P1W');

$Screenshot = new Screenshot();

$url = 'https://hugo.maugey.fr';
$url = 'https://tantra-kundalini-yoga.com';
$url = 'https://tantra-kundalini-yoga.com/fr/ressources/bandhas';

$widths = ['1024', '768', '480'];

$screenshot = $Screenshot->shot($url, $widths);

error_log(print_r($screenshot, true));
