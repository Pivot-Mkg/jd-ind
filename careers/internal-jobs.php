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
    $fields = $job['custom_fields'] ?? [];
    if (!is_array($fields)) {
        return '';
    }

    foreach ($fields as $field) {
        if (!is_array($field)) {
            continue;
        }
        $fieldId = (int)($field['field_id'] ?? 0);
        $entityType = trim((string)($field['entity_type'] ?? ''));
        $fieldName = trim((string)($field['field_name'] ?? ''));
        $fieldType = trim((string)($field['field_type'] ?? ''));
        if (
            $fieldId === 7 &&
            $entityType === 'job' &&
            $fieldName === 'Hiring For' &&
            $fieldType === 'dropdown'
        ) {
            $value = $field['value'] ?? '';
            if (is_array($value)) {
                return '';
            }
            return trim((string)$value);
        }
    }

    return '';
}

function normalize_hiring_for_value(string $value): string
{
    $normalized = strtolower(trim(str_replace(["\xE2\x80\x93", "\xE2\x80\x94"], '-', $value)));
    $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

    return match ($normalized) {
        'internal - to be posted on join us',
        'client (internal)' => 'internal',
        'external - to be posted to "find opportunities"',
        "external - to be posted to 'find opportunities'",
        'external - to be posted to find opportunities',
        'client (external)' => 'external',
        'do not post',
        'do not post (confidential)' => 'hidden',
        default => '',
    };
}

function is_confidential_job(array $job): bool
{
    return normalize_hiring_for_value(job_hiring_for_value($job)) === 'hidden';
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
    return normalize_hiring_for_value(job_hiring_for_value($job)) === 'internal';
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
        'hiring_for' => job_hiring_for_value($job),
    ];
}, $internalJobs);

echo json_encode([
    'error' => false,
    'count' => count($jobs),
    'jobs' => $jobs,
]);
