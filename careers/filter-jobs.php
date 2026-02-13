<?php
declare(strict_types=1);

$apiUrl = 'https://api.recruitcrm.io/v1/jobs';
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

header('Content-Type: application/json');

$searchQuery = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$categoryFilter = isset($_GET['job_category']) ? trim((string)$_GET['job_category']) : '';
$cityFilter = isset($_GET['city']) ? trim((string)$_GET['city']) : '';
$localityFilter = isset($_GET['locality']) ? trim((string)$_GET['locality']) : '';
$industryFilter = isset($_GET['job_industry']) ? trim((string)$_GET['job_industry']) : '';
$salaryFilter = isset($_GET['salary_range']) ? trim((string)$_GET['salary_range']) : '';
$countryFilter = isset($_GET['country']) ? trim((string)$_GET['country']) : '';
$companyFilter = isset($_GET['company_name']) ? trim((string)$_GET['company_name']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 9;
$categoryValues = array_filter(array_map('trim', explode(',', $categoryFilter)));
$cityValues = array_filter(array_map('trim', explode(',', $cityFilter)));
$localityValues = array_filter(array_map('trim', explode(',', $localityFilter)));
$industryValues = array_filter(array_map('trim', explode(',', $industryFilter)));
$salaryValues = array_filter(array_map('trim', explode(',', $salaryFilter)));

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

$searchParams = array_filter([
    'name' => $searchQuery !== '' ? $searchQuery : '',
    'city' => count($cityValues) === 1 ? $cityValues[0] : '',
    'country' => $countryFilter,
    'locality' => '',
    'company_name' => $companyFilter,
], fn($value) => $value !== '');

if ($searchParams) {
    $searchUrl = $apiUrl . '/search?' . http_build_query($searchParams);
    $response = recruitcrm_get($searchUrl, $apiToken);
    if ($response['error']) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Request failed']);
        exit;
    }
    $data = json_decode($response['body'], true);
    if ($response['status'] >= 200 && $response['status'] < 300 && is_array($data)) {
        $jobs = extract_jobs($data);
    }
} else {
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
            $jobs = array_merge($jobs, extract_jobs($data));
            $nextUrl = $data['next_page_url'] ?? null;
        } else {
            break;
        }
    }
}

if ($searchQuery !== '' && empty($searchParams)) {
    $jobs = array_values(array_filter($jobs, function ($job) use ($searchQuery, $stringify) {
        $title = $stringify($job['name'] ?? $job['title'] ?? '');
        return stripos($title, $searchQuery) !== false;
    }));
}

$jobs = array_values(array_filter($jobs, function ($job) {
    $hiringFor = strtolower(job_hiring_for_value($job));
    return $hiringFor === 'client (external)';
}));

if (!empty($categoryValues)) {
    $jobs = array_values(array_filter($jobs, function ($job) use ($categoryValues, $stringify) {
        $category = $stringify($job['job_category'] ?? $job['category'] ?? '');
        foreach ($categoryValues as $value) {
            if (strcasecmp($category, $value) === 0) {
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
    $jobs = array_values(array_filter($jobs, function ($job) use ($industryValues, $stringify) {
        $jobIndustry = $stringify($job['job_industry'] ?? '');
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

if (!empty($salaryValues)) {
    $jobs = array_values(array_filter($jobs, function ($job) use ($salaryValues) {
        $min = is_numeric($job['min_annual_salary'] ?? null) ? (int)$job['min_annual_salary'] : 0;
        $max = is_numeric($job['max_annual_salary'] ?? null) ? (int)$job['max_annual_salary'] : 0;
        foreach ($salaryValues as $range) {
            if ($range === '20000000+') {
                if ($max >= 20000000 || $min >= 20000000) {
                    return true;
                }
                continue;
            }
            [$rMin, $rMax] = array_pad(explode('-', $range), 2, null);
            $rMin = (int)$rMin;
            $rMax = (int)$rMax;
            if ($min <= $rMax && $max >= $rMin) {
                return true;
            }
        }
        return false;
    }));
}

$total = count($jobs);
$lastPage = max(1, (int)ceil($total / $perPage));
if ($page > $lastPage) {
    $page = $lastPage;
}
$offset = ($page - 1) * $perPage;
$jobs = array_slice($jobs, $offset, $perPage);

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
