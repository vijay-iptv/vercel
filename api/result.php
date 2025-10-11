<?php
$keyid = $_GET['keyid'] != '' ? $_GET['keyid'] : 'No';
$key = $_GET['key'] != '' ? $_GET['key'] : 'No';

echo json_encode([
    'keyid' => $keyid,
    'key' => $key
]);