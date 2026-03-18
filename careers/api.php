<?php
declare(strict_types=1);

header('Content-Type: application/json');

$apiUrl   = 'https://api.recruitcrm.io/v1/jobs';
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

/* ─────────────────────────── helpers ─────────────────────────── */

function recruitcrm_get(string $url, string $token): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: ' . $token,
        ],
    ]);
    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    $status    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'error'  => $curlError,
        'status' => $status,
        'body'   => $response ?: '',
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

function is_external_client_job(array $job): bool
{
    $fields = $job['custom_fields'] ?? [];
    if (!is_array($fields)) {
        return false;
    }

    foreach ($fields as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fieldId    = (int) ($field['field_id']   ?? $field['id']   ?? 0);
        $entityType = strtolower(trim((string) ($field['entity_type'] ?? '')));
        $fieldName  = strtolower(trim((string) ($field['field_name'] ?? $field['name'] ?? '')));
        $fieldType  = strtolower(trim((string) ($field['field_type'] ?? $field['type'] ?? '')));

        $value = $field['value'] ?? $field['field_value'] ?? $field['selected'] ?? '';
        if (is_array($value)) {
            $value = $value['value'] ?? $value['label'] ?? $value['name'] ?? '';
        }
        $value = trim((string) $value);

        if (
            $fieldId    === 7                    &&
            $entityType === 'job'                &&
            $fieldName  === 'hiring for'         &&
            $fieldType  === 'dropdown'           &&
            normalize_hiring_for_value($value) === 'external'
        ) {
            return true;
        }
    }

    return false;
}

/**
 * Returns true when the job should not be posted anywhere.
 */
function is_confidential_job(array $job): bool
{
    $fields = $job['custom_fields'] ?? [];
    if (!is_array($fields)) {
        return false;
    }

    foreach ($fields as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fieldId   = (int) ($field['field_id'] ?? $field['id'] ?? 0);
        $fieldName = strtolower(trim((string) ($field['field_name'] ?? $field['name'] ?? '')));

        if ($fieldId !== 7 && $fieldName !== 'hiring for') {
            continue;
        }

        $value = $field['value'] ?? $field['field_value'] ?? $field['selected'] ?? '';
        if (is_array($value)) {
            $value = $value['value'] ?? $value['label'] ?? $value['name'] ?? '';
        }

        if (normalize_hiring_for_value(trim((string) $value)) === 'hidden') {
            return true;
        }
    }

    return false;
}

function is_job_posting_status_active(array $job): bool
{
    $rawStatus = $job['job_posting_status'] ?? 0;
    if (is_array($rawStatus)) {
        $rawStatus = $rawStatus['value'] ?? $rawStatus['id'] ?? $rawStatus['status'] ?? 0;
    }
    return (int) $rawStatus === 1;
}

$stringify = function ($value): string {
    if (is_array($value)) {
        $flat = array_filter(array_map('strval', $value), fn($item) => $item !== '');
        return implode(', ', $flat);
    }
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    return $value === null ? '' : (string) $value;
};

/* ─────────────────────────── fetch all pages ─────────────────── */

$allJobs      = [];
$errorMessage = '';
$nextUrl      = $apiUrl;
$maxPages     = 100;
$pageCount    = 0;

while ($nextUrl && $pageCount < $maxPages) {
    $pageCount++;
    $response = recruitcrm_get($nextUrl, $apiToken);

    if ($response['error']) {
        $errorMessage = 'Could not reach the recruitment API.';
        break;
    }

    $data = json_decode($response['body'], true);

    if ($response['status'] >= 200 && $response['status'] < 300 && is_array($data)) {
        $pageJobs = extract_jobs($data);
        $allJobs  = array_merge($allJobs, $pageJobs);
        $nextUrl  = $data['next_page_url'] ?? null;
    } else {
        $errorMessage = 'Unexpected response from the recruitment API.';
        break;
    }
}

if ($errorMessage) {
    http_response_code(502);
    echo json_encode(['error' => true, 'message' => $errorMessage]);
    exit;
}

/* ─────────────────────── keep only External Client jobs ──────── */

$jobs = array_values(array_filter($allJobs, function (array $job): bool {
    // Must not be confidential
    if (is_confidential_job($job)) {
        return false;
    }
    if (!is_job_posting_status_active($job)) {
        return false;
    }
    // Must be marked for external posting
    return is_external_client_job($job);
}));

/* ─────────────────────────── apply filters ───────────────────── */

$searchQuery    = isset($_GET['q'])              ? trim((string) $_GET['q'])              : '';
$categoryFilter = isset($_GET['job_category'])   ? trim((string) $_GET['job_category'])   : '';
$cityFilter     = isset($_GET['city'])           ? trim((string) $_GET['city'])           : '';
$localityFilter = isset($_GET['locality'])       ? trim((string) $_GET['locality'])       : '';
$industryFilter = isset($_GET['job_industry'])   ? trim((string) $_GET['job_industry'])   : '';
$salaryFilter   = isset($_GET['salary_range'])   ? trim((string) $_GET['salary_range'])   : '';

// Fallback: treat locality as city
if ($cityFilter === '' && $localityFilter !== '') {
    $cityFilter = $localityFilter;
}

// Search by title / keyword
if ($searchQuery !== '') {
    $jobs = array_values(array_filter($jobs, function (array $job) use ($searchQuery, $stringify): bool {
        $title = $stringify($job['name'] ?? $job['title'] ?? '');
        return stripos($title, $searchQuery) !== false;
    }));
}

// Filter by job category
if ($categoryFilter !== '') {
    $jobs = array_values(array_filter($jobs, function (array $job) use ($categoryFilter, $stringify): bool {
        $category = $stringify($job['job_category'] ?? $job['category'] ?? '');
        return strcasecmp($category, $categoryFilter) === 0;
    }));
}

// Filter by city
if ($cityFilter !== '') {
    $jobs = array_values(array_filter($jobs, function (array $job) use ($cityFilter, $stringify): bool {
        $city = $stringify($job['city'] ?? '');
        return $city !== '' && strcasecmp($city, $cityFilter) === 0;
    }));
}

// Filter by industry
if ($industryFilter !== '') {
    $jobs = array_values(array_filter($jobs, function (array $job) use ($industryFilter, $stringify): bool {
        $industry = $stringify($job['job_industry'] ?? '');
        return $industry !== '' && strcasecmp($industry, $industryFilter) === 0;
    }));
}

// Filter by salary range
if ($salaryFilter !== '') {
    $ranges = array_filter(array_map('trim', explode(',', $salaryFilter)));

    $jobs = array_values(array_filter($jobs, function (array $job) use ($ranges): bool {
        $min = is_numeric($job['min_annual_salary'] ?? null) ? (int) $job['min_annual_salary'] : 0;
        $max = is_numeric($job['max_annual_salary'] ?? null) ? (int) $job['max_annual_salary'] : 0;

        foreach ($ranges as $range) {
            if ($range === '20000000+') {
                if ($max >= 20000000 || $min >= 20000000) {
                    return true;
                }
            } else {
                [$rMin, $rMax] = array_pad(explode('-', $range), 2, null);
                $rMin = (int) $rMin;
                $rMax = (int) $rMax;
                if ($min === 0 && $max === 0) {
                    continue;
                }
                if ($min <= $rMax && $max >= $rMin) {
                    return true;
                }
            }
        }

        return false;
    }));
}

/* ─────────────────────────── pagination ─────────────────────── */

$fetchAll = isset($_GET['fetch_all']) && $_GET['fetch_all'] === '1';

if ($fetchAll) {
    // Return all matched jobs; the browser handles client-side pagination
    echo json_encode([
        'error'        => false,
        'jobs'         => array_values($jobs),
        'total'        => count($jobs),
        'current_page' => 1,
        'last_page'    => 1,
        'per_page'     => count($jobs),
    ]);
    exit;
}

$perPage     = max(1, (int) ($_GET['per_page'] ?? 9));
$currentPage = max(1, (int) ($_GET['page']     ?? 1));
$total       = count($jobs);
$lastPage    = max(1, (int) ceil($total / $perPage));
$currentPage = min($currentPage, $lastPage);
$offset      = ($currentPage - 1) * $perPage;
$pageJobs    = array_slice($jobs, $offset, $perPage);

echo json_encode([
    'error'        => false,
    'jobs'         => array_values($pageJobs),
    'total'        => $total,
    'current_page' => $currentPage,
    'last_page'    => $lastPage,
    'per_page'     => $perPage,
]);
