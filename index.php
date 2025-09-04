<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Predictions World</title>
    <script src="canvasjs3.6/canvasjs.min.js"></script>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        form { margin-bottom: 20px; }
        label { margin-right: 8px; }
        select, button { margin-right: 16px; }
    </style>
</head>
<body>

<form method="post" action="index.php">
    <label for="day">Day:</label>
    <select id="day" name="day">
        <?php
        $selDay = isset($_POST['day']) ? intval($_POST['day']) : 0;
        for ($d = 1; $d <= 31; $d++) {
            $selected = ($d === $selDay) ? ' selected' : '';
            echo "<option value=\"$d\"$selected>$d</option>";
        }
        ?>
    </select>

    <label for="month">Month:</label>
    <select id="month" name="month">
        <?php
        $selMonth = isset($_POST['month']) ? intval($_POST['month']) : 0;
        for ($m = 1; $m <= 12; $m++) {
            $selected = ($m === $selMonth) ? ' selected' : '';
            echo "<option value=\"$m\"$selected>$m</option>";
        }
        ?>
    </select>

    <label for="site_id">Site:</label>
    <select id="site_id" name="site_id">
        <?php
        $sites = [
            1 => 'Hobart Airport',
            2 => 'Launceston Airport',
            3 => 'Devonport Airport',
            4 => 'Burnie Airport',
            5 => 'Queenstown Airport'
        ];
        $selSite = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;
        foreach ($sites as $id => $label) {
            $selected = ($id === $selSite) ? ' selected' : '';
            echo "<option value=\"$id\"$selected>$label</option>";
        }
        ?>
    </select>

    <button type="submit">Predict</button>
</form>

<?php
if (
    !isset($_POST['day']) ||
    !isset($_POST['month']) ||
    !isset($_POST['site_id'])
) {
    echo "<h2>No prediction has been requested yet.</h2>";
    echo "<h4>Please select Day, Month and Site from above Drop-Boxs.</h4>";
    exit;
}

$day   = intval($_POST['day']);
$month = intval($_POST['month']);
$site  = intval($_POST['site_id']);

if ($day < 1 || $day > 31 || $month < 1 || $month > 12 || $site < 1 || $site > 5) {
    echo "<p style='color:red;'>Invalid day, month, or site selection.</p>";
    exit;
}

$host = $_SERVER['HTTP_HOST'];
$url  = "http://$host/predict.php?day={$day}&month={$month}&site_id={$site}";
$json = @file_get_contents($url);
if ($json === false) {
    echo "<p style='color:red;'>Failed to contact predict.php on the server.</p>";
    exit;
}

$data = json_decode($json, true);
if (isset($data['error'])) {
    echo "<p style='color:red;'>Prediction error: " . htmlspecialchars($data['error']) . "</p>";
    exit;
}

$locationName = $sites[$site];
echo "<h2>{$locationName} &ndash; {$day}/{$month}/2022</h2>";

$rawCsv = __DIR__ . "/WeatherData/Site{$site}_raw.csv";
$handle = @fopen($rawCsv, 'r');
if (!$handle) {
    echo "<p style='color:red;'>Could not open raw CSV file: Site{$site}_raw.csv</p>";
    exit;
}

fgetcsv($handle);

$bins = array_fill(0, 48, ['sumTemp' => 0.0, 'sumHum' => 0.0, 'count' => 0]);

while (($row = fgetcsv($handle)) !== false) {
    $timestamp = $row[1];
    $hum       = floatval($row[2]);
    $temp      = floatval($row[3]);

    $dtObj = new DateTime($timestamp);
    $rDay   = intval($dtObj->format('j'));
    $rMonth = intval($dtObj->format('n'));

    if ($rDay !== $day || $rMonth !== $month) {
        continue;
    }

    $hour     = intval($dtObj->format('G'));
    $minute   = intval($dtObj->format('i'));
    $binIndex = $hour * 2 + ($minute >= 30 ? 1 : 0);

    $bins[$binIndex]['sumTemp'] += $temp;
    $bins[$binIndex]['sumHum']  += $hum;
    $bins[$binIndex]['count']   += 1;
}

fclose($handle);

$labels   = [];
$avgTemps = [];
$avgHums  = [];

for ($i = 0; $i < 48; $i++) {
    $cnt = $bins[$i]['count'];
    if ($cnt > 0) {
        $avgTemps[$i] = $bins[$i]['sumTemp'] / $cnt;
        $avgHums[$i]  = $bins[$i]['sumHum']  / $cnt;
    } else {
        $avgTemps[$i] = null;
        $avgHums[$i]  = null;
    }
    $hour      = floor($i / 2);
    $min       = ($i % 2) * 30;
    $labels[$i] = sprintf('%02d:%02d', $hour, $min);
}

echo '<div id="tempChartContainer" style="height: 300px; width: 100%; margin-bottom: 20px;"></div>';
echo '<div id="humChartContainer"  style="height: 300px; width: 100%;"></div>';
?>

<script>
window.onload = function() {
    var labels   = <?php echo json_encode($labels, JSON_NUMERIC_CHECK); ?>;
    var avgTemps = <?php echo json_encode($avgTemps, JSON_NUMERIC_CHECK); ?>;
    var avgHums  = <?php echo json_encode($avgHums, JSON_NUMERIC_CHECK); ?>;

    var tempDataPoints = [];
    for (var i = 0; i < labels.length; i++) {
        tempDataPoints.push({
            label: labels[i],
            y: avgTemps[i] === null ? null : avgTemps[i]
        });
    }
    new CanvasJS.Chart("tempChartContainer", {
        animationEnabled: true,
        title: { text: "Average Temperature vs. Time" },
        axisX: { title: "Time of Day", labelAngle: -45, interval: 4 },
        axisY: { title: "Temperature (°C)" },
        data: [{ type: "line", connectNullData: false, dataPoints: tempDataPoints }]
    }).render();

    var humDataPoints = [];
    for (var i = 0; i < labels.length; i++) {
        humDataPoints.push({
            label: labels[i],
            y: avgHums[i] === null ? null : avgHums[i]
        });
    }
    new CanvasJS.Chart("humChartContainer", {
        animationEnabled: true,
        title: { text: "Average Humidity vs. Time" },
        axisX: { title: "Time of Day", labelAngle: -45, interval: 4 },
        axisY: { title: "Humidity (%)" },
        data: [{ type: "line", connectNullData: false, dataPoints: humDataPoints }]
    }).render();
};
</script>

<?php
$minT = round($data['min_temp'], 1);
$maxT = round($data['max_temp'], 1);
$minH = round($data['min_hum'], 0);
$maxH = round($data['max_hum'], 0);

echo "<p>Predicted MinTemp: {$minT} °C    Predicted MaxTemp: {$maxT} °C</p>";
echo "<p>Predicted MinHum: {$minH} %    Predicted MaxHum: {$maxH} %</p>";
?>

</body>
</html>
