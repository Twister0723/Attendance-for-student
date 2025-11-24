<?php
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

// Allow cross-origin requests if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

echo json_encode([
    'serverTime' => date('Y-m-d H:i:s'),
    'timezone' => 'Asia/Manila',
    'timestamp' => time()
]);
?>