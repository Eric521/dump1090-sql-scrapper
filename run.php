<?php

$settings = array();
$settings['timezone'] = 'America/Los_Angeles';
$settings['server_ip'] = '';
$settings['server_port'] = '8080';

$settings['mysql_user'] = 'user';
$settings['mysql_pass'] = 'pass';
$settings['mysql_host'] = 'localhost';
$settings['mysql_db'] = 'dump1090';

date_default_timezone_set($settings['timezone']);

function getData($settings) {
    $url = 'http://' . $settings['server_ip'] . ':' . $settings['server_port'] . '/dump1090/data.json';
    return json_decode(file_get_contents($url));
}

function processData($db, $data) {
    $i = 0;
    foreach ($data as $row) {
        echo $i . " - " . saveData($db, $row) . ": " . json_encode($row) . "\n";
        $i++;
    }
}

function saveData($db, $row) {
    $sql = "INSERT INTO dump1090
	(id, hex, squawk, flight, lat, `long`, validposition, altitude, vert_rate, track, validtrack, speed, messages, seen, ts, dts)
	VALUES (NULL, :hex, :squawk, :flight, :lat, :long, :validposition, :altitude, :vert_rate, :track, :validtrack, :speed, :messages, :seen, :ts, :dts);";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':hex', $row->hex, PDO::PARAM_STR);
    $stmt->bindValue(':squawk', $row->squawk, PDO::PARAM_STR);
    $stmt->bindValue(':flight', $row->flight, PDO::PARAM_STR);
    $stmt->bindValue(':lat', (isset($row->lat) ? $row->lat : null), PDO::PARAM_STR);
    $stmt->bindValue(':long', (isset($row->lon) ? $row->lon : null), PDO::PARAM_STR);
    $stmt->bindValue(':validposition', $row->validposition, PDO::PARAM_STR);
    $stmt->bindValue(':altitude', $row->altitude, PDO::PARAM_STR);
    $stmt->bindValue(':vert_rate', $row->vert_rate, PDO::PARAM_STR);
    $stmt->bindValue(':track', $row->track, PDO::PARAM_STR);
    $stmt->bindValue(':validtrack', $row->validtrack, PDO::PARAM_STR);
    $stmt->bindValue(':speed', $row->speed, PDO::PARAM_STR);
    $stmt->bindValue(':messages', $row->messages, PDO::PARAM_STR);
    $stmt->bindValue(':seen', $row->seen, PDO::PARAM_STR);
    $stmt->bindValue(':ts', time(), PDO::PARAM_STR);
    $stmt->bindValue(':dts', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->execute();
    return $db->lastInsertId();
}

$db = new PDO('mysql:host=' . $settings['mysql_host'] . ';dbname=' . $settings['mysql_db'] . '', $settings['mysql_user'], $settings['mysql_pass']);
$x = 0;
while (true) {
    echo "Running: " . $x . "\n";
    $data = getData($settings);
    processData($db, $data);
    unset($data);
    sleep(1);
    $x++;
}
