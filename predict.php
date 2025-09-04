<?php
header('Content-Type: application/json');
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/utils/normalize.php';

use Phpml\ModelManager;
use Phpml\Regression\LeastSquares;

$day_input   = isset($_REQUEST['day'])   ? intval($_REQUEST['day'])   : 0;
$month_input = isset($_REQUEST['month']) ? intval($_REQUEST['month']) : 0;
$site_id     = isset($_REQUEST['site_id']) ? intval($_REQUEST['site_id']) : 0;

if ($day_input < 1 || $day_input > 31 ||
    $month_input < 1 || $month_input > 12 ||
    $site_id < 1 || $site_id > 5) {
    echo json_encode(['error' => 'Invalid day, month, or site_id']);
    exit;
}

$xml  = new DOMDocument('1.0', 'UTF-8');
$root = $xml->createElement('last_request');
$xml->appendChild($root);
$root->appendChild($xml->createElement('day',    $day_input));
$root->appendChild($xml->createElement('month',  $month_input));
$root->appendChild($xml->createElement('year',   2022));
$root->appendChild($xml->createElement('site_id', $site_id));
$xml->save(__DIR__ . '/saved_date.xml');

$dayNormMeta   = json_decode(file_get_contents(__DIR__ . "/models/site_{$site_id}_day_norm.json"),   true);
$monthNormMeta = json_decode(file_get_contents(__DIR__ . "/models/site_{$site_id}_month_norm.json"), true);

$minDay   = $dayNormMeta['min'];
$maxDay   = $dayNormMeta['max'];
$minMonth = $monthNormMeta['min'];
$maxMonth = $monthNormMeta['max'];

$day_norm   = ($day_input   - $minDay)   / ($maxDay   - $minDay);
$month_norm = ($month_input - $minMonth) / ($maxMonth - $minMonth);

$modelManager = new ModelManager();
$regMinTemp = $modelManager->restoreFromFile(__DIR__ . "/models/site_{$site_id}_min_temp.model");
$regMaxTemp = $modelManager->restoreFromFile(__DIR__ . "/models/site_{$site_id}_max_temp.model");
$regMinHum  = $modelManager->restoreFromFile(__DIR__ . "/models/site_{$site_id}_min_hum.model");
$regMaxHum  = $modelManager->restoreFromFile(__DIR__ . "/models/site_{$site_id}_max_hum.model");

$inputSample    = [ $day_norm, $month_norm ];
$pred_min_temp  = $regMinTemp->predict($inputSample);
$pred_max_temp  = $regMaxTemp->predict($inputSample);
$pred_min_hum   = $regMinHum->predict($inputSample);
$pred_max_hum   = $regMaxHum->predict($inputSample);

// Map site - location name
$locationNames = [
    1 => 'Hobart Airport',
    2 => 'Launceston Airport',
    3 => 'Devonport Airport',
    4 => 'Burnie Airport',
    5 => 'Queenstown Airport',
];
$location_name = $locationNames[$site_id];

// Return JSON
echo json_encode([
    'location_name' => $location_name,
    'min_temp'      => $pred_min_temp,
    'max_temp'      => $pred_max_temp,
    'min_hum'       => $pred_min_hum,
    'max_hum'       => $pred_max_hum,
]);
