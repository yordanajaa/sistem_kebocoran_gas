<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: *');

$vps_url = "http://31.97.220.137:3000/api/status";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch) || $httpCode != 200) {
    // Jika gagal, kirim status OFFLINE
    echo json_encode([
        "gas" => 0, 
        "api" => 0, 
        "status" => "OFFLINE",
        "debug_error" => curl_error($ch),
        "http_code" => $httpCode
    ]);
} else {
    echo $response;
}

curl_close($ch);
?>
