<?php
declare(strict_types=1);

$jobId = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
if ($jobId === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Missing job id']);
    exit;
}

$apiUrl = 'https://api.recruitcrm.io/v1/jobs/' . rawurlencode($jobId);
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Authorization: ' . $apiToken,
    ],
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');
if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Request failed']);
    exit;
}

http_response_code($statusCode ?: 500);
echo $response ?: json_encode(['error' => true, 'message' => 'Unexpected response']);
