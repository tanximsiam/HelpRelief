<?php
$url = 'https://api.gdeltproject.org/api/v2/doc/doc?query=bangladesh&format=json&mode=artlist&maxrecords=1';

// Disable SSL verification for testing
$context = stream_context_create([
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ]
]);

$data = file_get_contents($url, false, $context);

if ($data === false) {
    echo "Request failed\n";
} else {
    echo $data;
}
