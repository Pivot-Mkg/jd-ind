<?php
declare(strict_types=1);

$apiUrl = 'https://api.recruitcrm.io/v1/jobs';
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

header('Content-Type: application/json');

function recruitcrm_get(string $url, string $token): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: ' . $token,
        ],
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'error' => $curlError,
        'status' => $statusCode,
        'body' => $response ?: '',
    ];
}

function extract_jobs(array $data): array
{
    if (isset($data['data']) && is_array($data['data'])) {
        return $data['data'];
    }
    if (isset($data['jobs']) && is_array($data['jobs'])) {
        return $data['jobs'];
    }
    if (isset($data[0])) {
        return $data;
    }
    return [];
}

function job_hiring_for_value(array $job): string
{
    $candidates = [
        $job['custom_fields'] ?? null,
        $job['custom_field'] ?? null,
        $job['custom_fields_values'] ?? null,
        $job['custom_field_values'] ?? null,
        $job['fields'] ?? null,
    ];

    foreach ($candidates as $fields) {
        if (!is_array($fields)) {
            continue;
        }
        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
            $fieldName = strtolower((string)($field['field_name'] ?? $field['name'] ?? ''));
            $fieldId = (int)($field['field_id'] ?? $field['id'] ?? 0);
            if ($fieldId === 7 || $fieldName === 'hiring for') {
                $value = $field['value'] ?? $field['field_value'] ?? $field['selected'] ?? '';
                if (is_array($value)) {
                    $value = $value['value'] ?? $value['label'] ?? $value['name'] ?? '';
                }
                return trim((string)$value);
            }
        }
    }

    return '';
}

function is_confidential_job(array $job): bool
{
    return strcasecmp(job_hiring_for_value($job), 'Do Not Post (Confidential)') === 0;
}

$allJobs = [];
$nextUrl = $apiUrl;
$maxPages = 20;
$pageCount = 0;

while ($nextUrl && $pageCount < $maxPages) {
    $pageCount++;
    $response = recruitcrm_get($nextUrl, $apiToken);
    if ($response['error']) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Request failed']);
        exit;
    }

    $data = json_decode($response['body'], true);
    if ($response['status'] >= 200 && $response['status'] < 300 && is_array($data)) {
        $allJobs = array_merge($allJobs, extract_jobs($data));
        $nextUrl = $data['next_page_url'] ?? null;
    } else {
        break;
    }
}

$internalJobs = array_values(array_filter($allJobs, function (array $job): bool {
    if (is_confidential_job($job)) {
        return false;
    }
    $hiringFor = strtolower(job_hiring_for_value($job));
    return $hiringFor !== 'client (external)';
}));

$jobs = array_map(function (array $job): array {
    return [
        'slug' => (string)($job['slug'] ?? $job['job_slug'] ?? ''),
        'title' => (string)($job['name'] ?? $job['title'] ?? 'Open Position'),
        'company' => (string)($job['company'] ?? $job['client'] ?? 'James Douglas'),
        'city' => (string)($job['city'] ?? ''),
        'location' => (string)($job['location'] ?? ''),
        'job_type' => (string)($job['job_type'] ?? $job['type'] ?? ''),
        'min_experience' => (string)($job['minimum_experience'] ?? $job['min_experience'] ?? ''),
        'max_experience' => (string)($job['maximum_experience'] ?? $job['max_experience'] ?? ''),
    ];
}, $internalJobs);

echo json_encode([
    'error' => false,
    'count' => count($jobs),
    'jobs' => $jobs,
]);
