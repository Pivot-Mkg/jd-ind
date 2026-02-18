<?php
declare(strict_types=1);

$apiBase = 'https://api.recruitcrm.io/v1/candidates';
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method Not Allowed']);
    exit;
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? ($_POST['contact_number'] ?? ''));
$salary = trim($_POST['salary_expectation'] ?? '');
$jobSlug = trim($_POST['job_slug'] ?? '');
$formType = trim($_POST['form_type'] ?? ($jobSlug !== '' ? 'job_application' : ''));

$function = trim($_POST['function'] ?? '');
$industry = trim($_POST['industry'] ?? '');
$jobTitle = trim($_POST['job_title'] ?? ($_POST['position'] ?? ''));
$currentOrganization = trim($_POST['current_organization'] ?? '');
$currentSalary = trim($_POST['current_salary'] ?? '');
$currentCtc = trim($_POST['current_ctc'] ?? '');
$linkedin = trim($_POST['linkedin_profile'] ?? ($_POST['linkedin'] ?? ''));

if ($firstName === '' || $lastName === '' || $email === '' || $phone === '') {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Missing required fields']);
    exit;
}

if ($formType === 'job_application' && $jobSlug === '') {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Missing job slug']);
    exit;
}

$isWorkWithUs = $formType === 'work_with_us';
if ($isWorkWithUs) {
    if (
        $function === '' ||
        $industry === '' ||
        $jobTitle === '' ||
        $currentOrganization === '' ||
        $currentSalary === '' ||
        $linkedin === ''
    ) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Missing required Work With Us fields']);
        exit;
    }
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid email']);
    exit;
}

if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid LinkedIn URL']);
    exit;
}

function recruitcrm_request(string $method, string $url, $fields, string $token, array $extraHeaders = []): array
{
    $ch = curl_init();
    $headers = array_merge([
        'Accept: application/json',
        'Authorization: ' . $token,
    ], $extraHeaders);

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
    ];

    if ($fields !== null) {
        $options[CURLOPT_POSTFIELDS] = $fields;
    }

    curl_setopt_array($ch, $options);
    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $status,
        'body' => $body === false ? '' : $body,
        'error' => $error,
    ];
}

$resumeField = null;
if (!empty($_FILES['resume']['tmp_name']) && is_uploaded_file($_FILES['resume']['tmp_name'])) {
    $resumeField = new CURLFile(
        $_FILES['resume']['tmp_name'],
        $_FILES['resume']['type'] ?? 'application/octet-stream',
        $_FILES['resume']['name'] ?? 'resume'
    );
}

if ($isWorkWithUs && !$resumeField) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Resume is required']);
    exit;
}

$candidateSlug = null;
$payload = [
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'contact_number' => $phone,
];

if ($salary !== '') {
    $payload['salary_expectation'] = $salary;
}

if ($function !== '') {
    $payload['specialization'] = $function;
}

if ($jobTitle !== '') {
    $payload['position'] = $jobTitle;
}

if ($currentOrganization !== '') {
    $payload['current_organization'] = $currentOrganization;
}

if ($currentSalary !== '') {
    $payload['current_salary'] = $currentSalary;
}

if ($currentCtc !== '') {
    $payload['current_salary'] = $currentCtc;
}

if ($linkedin !== '') {
    $payload['linkedin'] = $linkedin;
}

if ($isWorkWithUs) {
    $payload['source'] = 'Work With Us';
    $payload['candidate_summary'] = "Function: {$function}\nIndustry: {$industry}\nJob Title: {$jobTitle}\nCurrent Salary: {$currentSalary}";
}

if ($resumeField) {
    $payload['resume'] = $resumeField;
}

$createResponse = recruitcrm_request('POST', $apiBase, $payload, $apiToken);
if ($createResponse['error']) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Candidate create failed']);
    exit;
}

$createData = json_decode($createResponse['body'], true);
$candidateData = $createData['data'] ?? $createData;
if (is_array($candidateData)) {
    $candidateSlug = $candidateData['slug']
        ?? $candidateData['candidate_slug']
        ?? $candidateData['candidate_id']
        ?? $candidateData['id']
        ?? null;
}

if (!$candidateSlug) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Candidate slug not found']);
    exit;
}

if ($formType === 'job_application') {
    $applyUrl = $apiBase . '/' . rawurlencode((string)$candidateSlug) . '/apply';
    $applyPayload = json_encode([
        'job_slug' => $jobSlug,
    ]);

    $applyResponse = recruitcrm_request('POST', $applyUrl, $applyPayload, $apiToken, [
        'Content-Type: application/json',
    ]);

    if ($applyResponse['error']) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Apply request failed']);
        exit;
    }

    $applyStatus = $applyResponse['status'] ?: 500;
    http_response_code($applyStatus);
    echo $applyResponse['body'] ?: json_encode(['error' => false, 'message' => 'Applied successfully']);
    exit;
}

http_response_code(200);
echo json_encode(['error' => false, 'message' => 'Candidate submitted successfully']);
