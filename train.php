<?php
ini_set('max_execution_time', 300);
set_time_limit(300);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/utils/normalize.php';

use Phpml\Regression\LeastSquares;
use Phpml\ModelManager;

$csvFile = __DIR__ . '/data/cleaned_weather.csv';
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Cannot open cleaned_weather.csv\n");
}
fgetcsv($handle);

$siteData = [];

while (($row = fgetcsv($handle)) !== false) {
    $site_id     = intval($row[0]);
    $day         = intval($row[1]);
    $month       = intval($row[2]);
    $minTemp     = floatval($row[4]);
    $maxTemp     = floatval($row[5]);
    $minHumidity = floatval($row[6]);
    $maxHumidity = floatval($row[7]);

    if (!isset($siteData[$site_id])) {
        $siteData[$site_id] = [
            'days'    => [],
            'months'  => [],
            'targets' => [
                'minTemp'     => [],
                'maxTemp'     => [],
                'minHumidity' => [],
                'maxHumidity' => [],
            ],
        ];
    }

    $siteData[$site_id]['days'][]    = $day;
    $siteData[$site_id]['months'][]  = $month;
    $siteData[$site_id]['targets']['minTemp'][]     = $minTemp;
    $siteData[$site_id]['targets']['maxTemp'][]     = $maxTemp;
    $siteData[$site_id]['targets']['minHumidity'][] = $minHumidity;
    $siteData[$site_id]['targets']['maxHumidity'][] = $maxHumidity;
}
fclose($handle);

$modelManager = new ModelManager();

foreach ($siteData as $site_id => $data) {
    $dayNormResult   = minMaxNormalize($data['days']);
    $normalizedDays  = $dayNormResult['normalized'];
    $minDay          = $dayNormResult['min'];
    $maxDay          = $dayNormResult['max'];

    $monthNormResult = minMaxNormalize($data['months']);
    $normalizedMonths = $monthNormResult['normalized'];
    $minMonth         = $monthNormResult['min'];
    $maxMonth         = $monthNormResult['max'];

    file_put_contents(
        __DIR__ . "/models/site_{$site_id}_day_norm.json",
        json_encode(['min' => $minDay, 'max' => $maxDay])
    );
    file_put_contents(
        __DIR__ . "/models/site_{$site_id}_month_norm.json",
        json_encode(['min' => $minMonth, 'max' => $maxMonth])
    );

    $samples = [];
    $count   = count($normalizedDays);
    for ($i = 0; $i < $count; ++$i) {
        $samples[] = [ $normalizedDays[$i], $normalizedMonths[$i] ];
    }

    $tMinTemp     = $data['targets']['minTemp'];
    $tMaxTemp     = $data['targets']['maxTemp'];
    $tMinHumidity = $data['targets']['minHumidity'];
    $tMaxHumidity = $data['targets']['maxHumidity'];

    $regMinTemp = new LeastSquares();
    $regMinTemp->train($samples, $tMinTemp);

    $regMaxTemp = new LeastSquares();
    $regMaxTemp->train($samples, $tMaxTemp);

    $regMinHum = new LeastSquares();
    $regMinHum->train($samples, $tMinHumidity);

    $regMaxHum = new LeastSquares();
    $regMaxHum->train($samples, $tMaxHumidity);

    $modelManager->saveToFile($regMinTemp,  __DIR__ . "/models/site_{$site_id}_min_temp.model");
    $modelManager->saveToFile($regMaxTemp,  __DIR__ . "/models/site_{$site_id}_max_temp.model");
    $modelManager->saveToFile($regMinHum,   __DIR__ . "/models/site_{$site_id}_min_hum.model");
    $modelManager->saveToFile($regMaxHum,   __DIR__ . "/models/site_{$site_id}_max_hum.model");

    echo "Site {$site_id} models trained and saved.\n";
}

echo "All site models and normalization metadata have been created.\n";
