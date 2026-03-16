<?php
declare(strict_types=1);

$apiUrl = 'https://api.recruitcrm.io/v1/jobs';
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

header('Content-Type: application/json');

$searchQuery = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$subFunctionFilter = isset($_GET['job_category']) ? trim((string)$_GET['job_category']) : '';
$cityFilter = isset($_GET['city']) ? trim((string)$_GET['city']) : '';
$localityFilter = isset($_GET['locality']) ? trim((string)$_GET['locality']) : '';
$industryFilter = isset($_GET['job_industry']) ? trim((string)$_GET['job_industry']) : '';
$functionFilter = isset($_GET['job_function']) ? trim((string)$_GET['job_function']) : '';
$countryFilter = isset($_GET['country']) ? trim((string)$_GET['country']) : '';
$companyFilter = isset($_GET['company_name']) ? trim((string)$_GET['company_name']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 9;
$fetchAll = isset($_GET['fetch_all']) && (string)$_GET['fetch_all'] === '1';
$subFunctionValues = array_filter(array_map('trim', explode(',', $subFunctionFilter)));
$cityValues = array_filter(array_map('trim', explode(',', $cityFilter)));
$localityValues = array_filter(array_map('trim', explode(',', $localityFilter)));
$industryValues = array_filter(array_map('trim', explode(',', $industryFilter)));
$functionValues = array_filter(array_map('trim', explode(',', $functionFilter)));

if (!empty($industryValues)) {
    $industryValues = array_values(array_filter(array_map('trim', $industryValues), fn(string $value): bool => $value !== ''));
}

if (empty($cityValues) && !empty($localityValues)) {
    $cityValues = $localityValues;
    $localityValues = [];
}

$stringify = function ($value): string {
    if (is_array($value)) {
        $flat = array_filter(array_map('strval', $value), fn($item) => $item !== '');
        return implode(', ', $flat);
    }
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    return $value === null ? '' : (string)$value;
};

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

function has_external_client_hiring_for_field(array $job): bool
{
    return normalize_hiring_for_value(job_hiring_for_value($job)) === 'external';
}

function job_industry_value(array $job): string
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
            if ($fieldId === 3 || $fieldName === 'job - industry') {
                $value = $field['value'] ?? $field['field_value'] ?? $field['selected'] ?? '';
                if (is_array($value)) {
                    $flat = array_filter(array_map('strval', $value), fn($item) => trim($item) !== '');
                    if ($flat) {
                        return trim((string)reset($flat));
                    }
                    $value = $value['value'] ?? $value['label'] ?? $value['name'] ?? '';
                }
                return trim((string)$value);
            }
        }
    }

    $fallback = $job['job_industry'] ?? '';
    if (is_array($fallback)) {
        $fallback = implode(', ', array_filter(array_map('strval', $fallback), fn($item) => trim($item) !== ''));
    }
    return trim((string)$fallback);
}

function job_function_value(array $job): string
{
    $fallback = $job['job_function'] ?? $job['function'] ?? '';
    if (is_array($fallback)) {
        $fallback = implode(', ', array_filter(array_map('strval', $fallback), fn($item) => trim($item) !== ''));
    }
    return trim((string)$fallback);
}

function job_sub_function_value(array $job): string
{
    $fallback = $job['job_category'] ?? $job['category'] ?? '';
    if (is_array($fallback)) {
        $fallback = implode(', ', array_filter(array_map('strval', $fallback), fn($item) => trim($item) !== ''));
    }
    return trim((string)$fallback);
}

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

$jobs = [];
$nextUrl = $apiUrl;
$maxPages = 100;
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
        $jobs = array_merge($jobs, extract_jobs($data));
        $nextUrl = $data['next_page_url'] ?? null;
    } else {
        break;
    }
}

if ($searchQuery !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($searchQuery, $stringify) {
        $title = $stringify($job['name'] ?? $job['title'] ?? '');
        return stripos($title, $searchQuery) !== false;
    }));
}

if ($countryFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($countryFilter, $stringify) {
        $country = $stringify($job['country'] ?? '');
        return $country !== '' && strcasecmp($country, $countryFilter) === 0;
    }));
}

if ($companyFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($companyFilter, $stringify) {
        $company = $stringify($job['company'] ?? $job['company_name'] ?? $job['client'] ?? '');
        return $company !== '' && stripos($company, $companyFilter) !== false;
    }));
}

$jobs = array_values(array_filter($jobs, function ($job) {
    return has_external_client_hiring_for_field($job);
}));

if ($functionFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($functionValues) {
        $jobFunction = job_function_value($job);
        foreach ($functionValues as $value) {
            if (strcasecmp($jobFunction, $value) === 0) {
                return true;
            }
        }
        return false;
    }));
}

if (!empty($cityValues)) {
    $jobs = array_values(array_filter($jobs, function ($job) use ($cityValues, $stringify) {
        $city = $stringify($job['city'] ?? '');
        foreach ($cityValues as $value) {
            if (strcasecmp($city, $value) === 0) {
                return true;
            }
        }
        return false;
    }));
}

// Locality filtering is handled via the city filter to keep URLs consistent.

if (!empty($industryValues)) {
    $jobs = array_values(array_filter($jobs, function ($job) use ($industryValues) {
        $jobIndustry = job_industry_value($job);
        if ($jobIndustry === '') {
            return false;
        }
        foreach ($industryValues as $value) {
            if (strcasecmp($jobIndustry, $value) === 0) {
                return true;
            }
        }
        return false;
    }));
}

if ($subFunctionFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($subFunctionValues) {
        $subFunction = job_sub_function_value($job);
        foreach ($subFunctionValues as $value) {
            if (strcasecmp($subFunction, $value) === 0) {
                return true;
            }
        }
        return false;
    }));
}

$total = count($jobs);
$lastPage = max(1, (int)ceil($total / $perPage));
if (!$fetchAll) {
    if ($page > $lastPage) {
        $page = $lastPage;
    }
    $offset = ($page - 1) * $perPage;
    $jobs = array_slice($jobs, $offset, $perPage);
} else {
    $page = 1;
    $perPage = max(1, $total);
    $lastPage = 1;
}

echo json_encode([
    'error' => false,
    'jobs' => $jobs,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'last_page' => $lastPage,
    ],
]);
