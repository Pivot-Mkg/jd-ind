<?php
declare(strict_types=1);

// Same reason as careers/api.php: keep PHP notices/warnings out of the
// response body so it always stays valid JSON for the frontend to parse.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$apiBase = 'https://api.recruitcrm.io/v1/candidates';
$apiToken = 'Bearer BfNc8D74TMfvbPibQihWlO488RvXs6R5GCHpoYHJYbnRuPIj68jNXzq0KLLv7WcCET_EFf7FKshssOtNWcSFZV8xNzg3MTM4NzkyOnw6cHJvZHVjdGlvbg==';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method Not Allowed']);
    exit;
}

$jobSlug = trim((string)($_POST['job_slug'] ?? ''));
$requiredFields = [
    'first_name',
    'last_name',
    'email',
    'contact_number',
    'gender_id',
    'work_ex_year',
    'current_salary',
    'current_organization',
    'notice_period',
    'city',
    'position',
    'specialization',
    'source',
    'custom_fields',
];

foreach ($requiredFields as $field) {
    if (trim((string)($_POST[$field] ?? '')) === '') {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => "Missing required field: {$field}"]);
        exit;
    }
}

if ($jobSlug === '') {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Missing job slug']);
    exit;
}

if (!filter_var((string)$_POST['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid email']);
    exit;
}

if (empty($_FILES['resume']['tmp_name']) || !is_uploaded_file($_FILES['resume']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Resume is required']);
    exit;
}

function recruitcrm_request(string $method, string $url, $fields, string $token, array $extraHeaders = []): array
{
    $ch = curl_init();
    $headers = array_merge([
        'Accept: application/json',
        'Authorization: ' . $token,
    ], $extraHeaders);

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $fields,
    ]);

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch) removed: no-op since PHP 8.0, was only producing a
    // deprecation notice that could leak into this JSON response body.

    return [
        'status' => $status,
        'body' => $body === false ? '' : $body,
        'error' => $error,
    ];
}

function extract_api_message($payload): string
{
    if (!is_array($payload)) {
        return '';
    }

    foreach (['message', 'error'] as $key) {
        $value = $payload[$key] ?? null;
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
    }

    if (isset($payload['errors']) && is_array($payload['errors'])) {
        $messages = [];
        array_walk_recursive($payload['errors'], static function ($value) use (&$messages): void {
            if (is_scalar($value) && trim((string)$value) !== '') {
                $messages[] = trim((string)$value);
            }
        });
        if ($messages) {
            return implode(', ', $messages);
        }
    }

    return '';
}

function extract_candidate_identifier($payload)
{
    if (!is_array($payload)) {
        return null;
    }

    foreach (['slug', 'candidate_slug', 'candidate_id', 'id'] as $key) {
        if (isset($payload[$key]) && $payload[$key] !== '' && $payload[$key] !== null) {
            return $payload[$key];
        }
    }

    foreach ($payload as $value) {
        if (is_array($value)) {
            $identifier = extract_candidate_identifier($value);
            if ($identifier !== null && $identifier !== '') {
                return $identifier;
            }
        }
    }

    return null;
}

$payload = [
    'first_name' => trim((string)$_POST['first_name']),
    'last_name' => trim((string)$_POST['last_name']),
    'email' => trim((string)$_POST['email']),
    'contact_number' => trim((string)$_POST['contact_number']),
    'gender_id' => trim((string)$_POST['gender_id']),
    'work_ex_year' => trim((string)$_POST['work_ex_year']),
    'current_salary' => trim((string)$_POST['current_salary']),
    'salary_expectation' => trim((string)($_POST['salary_expectation'] ?? '')),
    'current_organization' => trim((string)$_POST['current_organization']),
    'notice_period' => trim((string)$_POST['notice_period']),
    'city' => trim((string)$_POST['city']),
    'relevant_experience' => trim((string)($_POST['relevant_experience'] ?? '')),
    'position' => trim((string)$_POST['position']),
    'source' => trim((string)$_POST['source']),
    'specialization' => trim((string)$_POST['specialization']),
    'custom_fields' => trim((string)$_POST['custom_fields']),
    'resume' => new CURLFile(
        $_FILES['resume']['tmp_name'],
        $_FILES['resume']['type'] ?? 'application/octet-stream',
        $_FILES['resume']['name'] ?? 'resume'
    ),
];

$createResponse = recruitcrm_request('POST', $apiBase, $payload, $apiToken);
if ($createResponse['error']) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Candidate create failed']);
    exit;
}

$createData = json_decode($createResponse['body'], true);
if ($createResponse['status'] < 200 || $createResponse['status'] >= 300) {
    http_response_code($createResponse['status'] ?: 500);
    echo json_encode([
        'error' => true,
        'message' => extract_api_message($createData) ?: 'Candidate create failed',
        'details' => $createData ?: $createResponse['body'],
    ]);
    exit;
}

$candidateIdentifier = extract_candidate_identifier($createData);
if (!$candidateIdentifier) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Candidate identifier not found in create response',
        'details' => $createData ?: $createResponse['body'],
    ]);
    exit;
}

$assignResponse = recruitcrm_request(
    'POST',
    $apiBase . '/' . rawurlencode((string)$candidateIdentifier) . '/assign',
    json_encode(['job_slug' => $jobSlug]),
    $apiToken,
    ['Content-Type: application/json']
);

if ($assignResponse['error']) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Candidate assignment failed']);
    exit;
}

$assignData = json_decode($assignResponse['body'], true);
if ($assignResponse['status'] < 200 || $assignResponse['status'] >= 300) {
    http_response_code($assignResponse['status'] ?: 500);
    echo json_encode([
        'error' => true,
        'message' => extract_api_message($assignData) ?: 'Candidate assignment failed',
        'details' => $assignData ?: $assignResponse['body'],
    ]);
    exit;
}

http_response_code($assignResponse['status'] ?: 200);
echo $assignResponse['body'] ?: json_encode(['error' => false, 'message' => 'Candidate created and assigned successfully']);