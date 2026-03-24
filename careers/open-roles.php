<?php
declare(strict_types=1);

$apiUrl = 'https://api.recruitcrm.io/v1/jobs';
$qualificationApiUrl = 'https://api.recruitcrm.io/v1/qualifications';
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

$jobs = [];
$allJobs = [];
$errorMessage = '';
$searchQuery = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$subFunctionFilter = isset($_GET['job_category']) ? trim((string)$_GET['job_category']) : '';
$cityFilter = isset($_GET['city']) ? trim((string)$_GET['city']) : '';
$localityFilter = isset($_GET['locality']) ? trim((string)$_GET['locality']) : '';
$industryFilter = isset($_GET['job_industry']) ? trim((string)$_GET['job_industry']) : '';
$functionFilter = isset($_GET['job_function']) ? trim((string)$_GET['job_function']) : '';

if ($cityFilter === '' && $localityFilter !== '') {
    $cityFilter = $localityFilter;
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

function format_experience_range(string $minExp, string $maxExp): string
{
    $minExp = trim($minExp);
    $maxExp = trim($maxExp);

    if ($minExp !== '' && $maxExp !== '') {
        return $minExp === $maxExp ? $minExp . ' Years' : $minExp . ' to ' . $maxExp . ' Years';
    }

    if ($minExp !== '') {
        return $minExp . ' Years';
    }

    if ($maxExp !== '') {
        return $maxExp . ' Years';
    }

    return 'Not specified';
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

function has_external_client_hiring_for_field(array $job): bool
{
    return normalize_hiring_for_value(job_hiring_for_value($job)) === 'external';
}

function has_internal_hiring_for_field(array $job): bool
{
    return normalize_hiring_for_value(job_hiring_for_value($job)) === 'internal';
}

function is_job_open(array $job): bool
{
    $status = $job['job_status'] ?? null;
    if (is_array($status)) {
        $label = trim((string)($status['label'] ?? $status['name'] ?? ''));
        if ($label !== '') {
            return strcasecmp($label, 'Open') === 0;
        }

        return (int)($status['id'] ?? 0) === 1;
    }

    return strcasecmp(trim((string)$status), 'Open') === 0;
}

function should_post_on_website(array $job): bool
{
    $fields = $job['custom_fields'] ?? [];
    if (!is_array($fields)) {
        return false;
    }

    foreach ($fields as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fieldId = (int)($field['field_id'] ?? $field['id'] ?? 0);
        $entityType = strtolower(trim((string)($field['entity_type'] ?? '')));
        $fieldName = strtolower(trim((string)($field['field_name'] ?? $field['name'] ?? '')));
        $fieldType = strtolower(trim((string)($field['field_type'] ?? $field['type'] ?? '')));
        $value = $field['value'] ?? $field['field_value'] ?? $field['selected'] ?? '';

        if (is_array($value)) {
            $value = $value['value'] ?? $value['label'] ?? $value['name'] ?? '';
        }

        if (
            $fieldId === 10 &&
            $entityType === 'job' &&
            $fieldName === 'post on website' &&
            $fieldType === 'dropdown'
        ) {
            return strcasecmp(trim((string)$value), 'Yes') === 0;
        }
    }

    return false;
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

function extract_qualification_id(array $job): ?int
{
    $qualificationId = $job['qualification_id'] ?? null;
    if (is_numeric($qualificationId)) {
        return (int)$qualificationId;
    }

    $qualification = $job['qualification'] ?? null;
    if (is_array($qualification)) {
        $nestedId = $qualification['qualification_id'] ?? $qualification['id'] ?? null;
        if (is_numeric($nestedId)) {
            return (int)$nestedId;
        }
    }

    return null;
}

function extract_qualifications_map(array $data): array
{
    $items = [];

    if (isset($data['data']) && is_array($data['data'])) {
        $items = $data['data'];
    } elseif (isset($data['qualifications']) && is_array($data['qualifications'])) {
        $items = $data['qualifications'];
    } elseif (isset($data[0])) {
        $items = $data;
    }

    $map = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $qualificationId = $item['qualification_id'] ?? $item['id'] ?? null;
        $label = trim((string)($item['label'] ?? $item['name'] ?? ''));

        if (is_numeric($qualificationId) && $label !== '') {
            $map[(int)$qualificationId] = $label;
        }
    }

    return $map;
}

function job_qualification_value(array $job, array $qualificationsMap = []): string
{
    $qualification = $job['qualification'] ?? null;
    if (is_array($qualification)) {
        $value = $qualification['label'] ?? $qualification['name'] ?? '';
        $value = trim((string)$value);
        if ($value !== '') {
            return $value;
        }
    }

    $fallback = $job['qualification_name'] ?? $job['qualification_label'] ?? '';
    $fallback = trim((string)$fallback);
    if ($fallback !== '') {
        return $fallback;
    }

    $qualificationId = extract_qualification_id($job);
    if ($qualificationId !== null && array_key_exists($qualificationId, $qualificationsMap)) {
        return $qualificationsMap[$qualificationId];
    }

    return '';
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

$errorMessage = '';
$qualificationsMap = [];

$qualificationResponse = recruitcrm_get($qualificationApiUrl, $apiToken);
if (!$qualificationResponse['error'] && $qualificationResponse['status'] >= 200 && $qualificationResponse['status'] < 300) {
    $qualificationData = json_decode($qualificationResponse['body'], true);
    if (is_array($qualificationData)) {
        $qualificationsMap = extract_qualifications_map($qualificationData);
    }
}

$nextUrl = $apiUrl;
$maxPages = 100;
$pageCount = 0;

while ($nextUrl && $pageCount < $maxPages) {
    $pageCount++;
    $response = recruitcrm_get($nextUrl, $apiToken);
    if ($response['error']) {
        $errorMessage = 'We could not load open roles right now. Please try again later.';
        break;
    }
    $data = json_decode($response['body'], true);
    if ($response['status'] >= 200 && $response['status'] < 300 && is_array($data)) {
        $pageJobs = extract_jobs($data);
        $allJobs = array_merge($allJobs, $pageJobs);
        $nextUrl = $data['next_page_url'] ?? null;
    } else {
        $errorMessage = 'We could not load open roles right now. Please try again later.';
        break;
    }
}

$internalJobs = [];
$externalJobs = array_values(array_filter($allJobs, function ($job) {
    return has_external_client_hiring_for_field($job)
        && is_job_open($job)
        && should_post_on_website($job);
}));

$industryOptions = [
    'Biotech',
    'Clinical Research',
    'Pharmaceutical',
    'Medical Devices',
    'Medical Equipments',
    'Medical Diagnostics',
    'CDMO',
    'API',
    'Consumer Healthcare',
    'Clinics & Labs',
    'Hospitals',
    'Building Materials',
    'Chemicals',
    'Automobile',
    'Auto Components',
    'Defence & Aerospace',
    'Electrical Equipment',
    'Agrochemicals',
    'Industrial Automation',
    'Industrial Equipment/Machinery',
    'Iron & Steel',
    'Metals & Mining',
    'Packaging',
    'Petrochemicals',
    'Plastics & Rubber',
    'Aviation',
    'Logistics',
    'EPC',
    'Oil & Gas',
    'Ports & Shipping',
    'Power',
    'Real Estate',
    'Construction',
    'FMCG',
    'FMCD',
    'Beauty & Personal Care',
    'Beverage',
    'Fitness & Wellness',
    'Furniture & Furnishing',
    'Gems & Jewellery',
    'Hospitality',
    'Retail',
    'Textile & Apparel',
    'Travel & Tourism',
    'Financial Services',
    'Banking',
    'Credit Rating',
    'Life Insurance',
    'General Insurance',
    'Health Insurance',
    'NBFC',
    'Capital Markets/Securities/Broking',
    'Mutual Funds & AMC',
    'MII',
    'Investment Banking/Management',
    'PE/VC',
    'Payment-Tech',
    'Lending-Tech',
    'Wealth-Tech',
    'Insur-Tech',
    'Reg-Tech',
    'Crypto',
    'Consumer-Tech',
    'D2C Tech',
    'Retail-Tech',
    'Health-Tech',
    'Ed-Tech',
    'Prop-Tech',
    'Travel-Tech',
    'Logi-Tech',
    'HR-Tech',
    'Analytics Platform',
    'B2B SaaS',
    'ITeS',
    'Gaming',
    'Telecom',
    'Advertising',
    'PR',
    'Media',
    'Accounting',
    'Business Consulting & Services',
    'Law Firms',
    'NGOs',
    'Market Research',
    'Sports/Leisure/Recreation',
    'Education',
    'Miscellaneous',
];
$industryOptionLookup = array_fill_keys(array_map('strtolower', $industryOptions), true);
$functionOptions = [
    'Healthcare & Lifesciences',
    'B2B',
    'Property & Construction',
    'B2C',
    'Banking & Financial Services',
    'FinTech',
    'Technology',
    'Media, Entertainment & Telecom',
    'Professional Services',
    'Miscellaneous',
];
$functionOptionLookup = array_fill_keys(array_map('strtolower', $functionOptions), true);

$jobs = $externalJobs;

if ($cityFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($cityFilter, $stringify) {
        $city = $stringify($job['city'] ?? '');
        return $city !== '' && strcasecmp($city, $cityFilter) === 0;
    }));
}

if ($searchQuery !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($searchQuery, $stringify) {
        $title = $stringify($job['name'] ?? $job['title'] ?? '');
        return stripos($title, $searchQuery) !== false;
    }));
}

if ($functionFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($functionFilter) {
        $jobFunction = job_function_value($job);
        return $jobFunction !== '' && strcasecmp($jobFunction, $functionFilter) === 0;
    }));
}

if ($subFunctionFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($subFunctionFilter) {
        $subFunction = job_sub_function_value($job);
        return $subFunction !== '' && strcasecmp($subFunction, $subFunctionFilter) === 0;
    }));
}

$cities = [];
$localities = [];
$industries = [];
$subFunctionOptions = [];
$subFunctionOptionLookup = [];
foreach ($externalJobs as $job) {
    $subFunction = job_sub_function_value($job);
    if ($subFunction !== '') {
        $key = strtolower($subFunction);
        if (!isset($subFunctionOptionLookup[$key])) {
            $subFunctionOptionLookup[$key] = true;
            $subFunctionOptions[] = $subFunction;
        }
    }
}
if ($subFunctionOptions) {
    natcasesort($subFunctionOptions);
    $subFunctionOptions = array_values($subFunctionOptions);
    $subFunctionOptionLookup = array_fill_keys(array_map('strtolower', $subFunctionOptions), true);
}

if ($industryFilter !== '') {
    if (isset($industryOptionLookup[strtolower($industryFilter)])) {
        $jobs = array_values(array_filter($jobs, function ($job) use ($industryFilter) {
            $jobIndustry = job_industry_value($job);
            return $jobIndustry !== '' && strcasecmp($jobIndustry, $industryFilter) === 0;
        }));
    }
}

$cityOptions = [
    'Agra',
    'Ahmedabad',
    'Ajmer',
    'Aligarh',
    'Amritsar',
    'Aurangabad',
    'Bengaluru',
    'Bhopal',
    'Bhubaneswar',
    'Chandigarh',
    'Chennai',
    'Coimbatore',
    'Dehradun',
    'Delhi',
    'Dhanbad',
    'Faridabad',
    'Ghaziabad',
    'Goa',
    'Gurgaon',
    'Guwahati',
    'Gwalior',
    'Hyderabad',
    'Indore',
    'Jaipur',
    'Jamshedpur',
    'Jodhpur',
    'Kanpur',
    'Kochi',
    'Kolkata',
    'Lucknow',
    'Ludhiana',
    'Madurai',
    'Mangalore',
    'Meerut',
    'Mumbai',
    'Mysuru',
    'Nagpur',
    'Nashik',
    'Noida',
    'Patna',
    'Pimpri-Chinchwad',
    'Pune',
    'Raipur',
    'Rajkot',
    'Ranchi',
    'Surat',
    'Thane',
    'Tiruchirappalli',
    'Udaipur',
    'Vadodara',
    'Varanasi',
    'Vijayawada',
    'Visakhapatnam',
];
foreach ($allJobs as $job) {
    $city = $stringify($job['city'] ?? '');
    $location = $stringify($job['location'] ?? '');
    $locality = $stringify($job['locality'] ?? '');
    $state = $stringify($job['state'] ?? '');
    $industry = job_industry_value($job);
    $cityKey = $city !== '' ? $city : $location;
    if ($cityKey !== '') {
        $cities[$cityKey] = ($cities[$cityKey] ?? 0) + 1;
    }
    if ($locality !== '') {
        $localities[$locality] = ($localities[$locality] ?? 0) + 1;
    }
    if ($industry !== '') {
        $industries[$industry] = ($industries[$industry] ?? 0) + 1;
    }
}
foreach ($cityOptions as $city) {
    $localities[$city] = $localities[$city] ?? 0;
}
foreach ($industryOptions as $industry) {
    $industries[$industry] = $industries[$industry] ?? 0;
}

function build_filter_url(array $params): string
{
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $query = array_filter($params, fn($value) => $value !== '');
    return $query ? $base . '?' . http_build_query($query) : $base;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Open Roles | James Douglas</title>
    <link rel="icon" type="image/x-icon" href="../images/icons/favicon-jdg.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/open-roles.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <!-- Google Tag Manager -->
    <?php include '../inc/gtm-head-code.php'; ?>
    <!-- End Google Tag Manager -->
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <?php include '../inc/gtm-body-code.php'; ?>
    <!-- End Google Tag Manager (noscript) -->

    <!-- navbar start  -->
    <?php include '../inc/navbar2.php'; ?>
    <!-- navbar end  -->

    <!-- Banner -->
    <section class="banner" id="career-banner">
        <div class="banner-overlay">
            <div class="banner-content">
                <h1>
                    <span class="hero-corner">Advisors. Partners.</span>
                    <br>
                    Redefining Futures.
                </h1>
            </div>
        </div>
    </section>
    <!-- Banner end -->

    <!-- Why Join Us start here  -->
    <section class="sections mt-0">
        <div class="container content-above-decorator">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-heading">
                    Careers Opportunities
                </h2>
                <div class="section-divider"></div>
               
                <!-- <p class="mt-1" style="color: #444; font-size: 1.1rem">
                    Your career deserves more than routine mandates. At James Douglas, you’ll work on high-stakes
                    leadership searches, learn from industry experts, and grow in an environment that rewards ambition
                    and sharp thinking.
                </p>
                <p class="mt-4" style="color: #444; font-size: 1.1rem">
                    We are creating a firm for those who believe advisory is more than a career — it is a calling. At
                    James Douglas, your work shapes leaders, organizations, and industries.
                </p> -->

                <p class="mt-4" style="color: #444; font-size: 1.1rem">
                    Browse exclusive job opportunities across industries and functions. Each role offers genuine growth potential and aligns with our commitment to creating meaningful connections.
                </p>

            </div>
            <div class="row g-4 justify-content-center d-none">

                <div class="col-12 col-md-6 col-lg-4" data-aos="flip-left" data-aos-delay="100">
                    <div class="card h-100  border-0">
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center justify-content-center" style="
                        width: 56px;
                        height: 56px;
                        background: #3F597A;
                        border-radius: 50%;
                    ">
                                <img src="../assets/images/icons/Teamwork.svg" alt="" height="42px;" width="42px;"
                                    srcset="">
                            </div>
                            <h5 class="card-title fw-bold" style="color: #23235b">
                                Purposeful Work
                            </h5>
                            <p class="card-text" style="color: #444">
                                impact that matters to businesses and lives.
                            </p>
                        </div>
                        <div class="pseudo-element">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4" data-aos="flip-left" data-aos-delay="200">
                    <div class="card h-100  border-0">
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center justify-content-center" style="
                        width: 56px;
                        height: 56px;
                        background: #3F597A;
                        border-radius: 50%;
                    ">
                                <img src="../assets/images/icons/Excellence-1.svg" alt="" height="42px;" width="42px;"
                                    srcset="">
                            </div>
                            <h5 class="card-title fw-bold" style="color: #23235b">
                                Ownership with Support
                            </h5>
                            <p class="card-text" style="color: #444">
                                freedom to lead, backed by mentorship.
                            </p>
                        </div>
                        <div class="pseudo-element">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4" data-aos="flip-left" data-aos-delay="300">
                    <div class="card h-100  border-0">
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center justify-content-center" style="
                        width: 56px;
                        height: 56px;
                        background: #3F597A;
                        border-radius: 50%;
                    ">
                                <img src="../assets/images/icons/handshake.svg" alt="" height="42px;" width="42px;"
                                    srcset="">
                            </div>
                            <h5 class="card-title fw-bold" style="color: #23235b">
                                Culture of Meaning
                            </h5>
                            <p class="card-text" style="color: #444">
                                built on trust, shared responsibility, and excellence.
                            </p>
                        </div>
                        <div class="pseudo-element">
                        </div>

                    </div>
                </div>
            </div>
            <p class="mt-4 text-center" style="color: #444; font-size: 1.1rem">
                Here, your career creates value through people. And that legacy lasts. 
            </p>
        </div>
    </section>
    <!-- Why Join Us end here  -->

   

    <!-- internal job section start -->
    <section class="open-roles-section" id="internal-jobs-section" style="display: none;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-heading">
                    Build Your Career With James Douglas
                    <!-- Internal Opportunities -->
                </h2>
                <div class="section-divider"></div>
                <p class="mt-3 text-muted">
                    Join Our Growing Team
                   <!-- Careers at James Douglas -->
                </p>
            </div>

            <?php if ($errorMessage): ?>
                <div class="alert alert-light shadow-sm" role="alert">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php elseif (!$internalJobs): ?>
                <div class="alert alert-light shadow-sm" role="alert">
                    No internal roles are available right now. Please check back soon.
                </div>
            <?php else: ?>
                <div class="open-roles-grid">
                    <div id="internal-job-list">
                        <?php foreach ($internalJobs as $index => $job): ?>
                            <?php
                            $title = $stringify($job['name'] ?? $job['title'] ?? 'Open Position');
                            $company = $stringify($job['company'] ?? $job['client'] ?? 'James Douglas Global');
                            $location = $stringify($job['location'] ?? ($job['city'] ?? ''));
                            $type = $stringify($job['job_type'] ?? ($job['type'] ?? 'Full time'));
                            $minExp = $stringify($job['minimum_experience'] ?? $job['min_experience'] ?? $job['min_exp'] ?? '');
                            $maxExp = $stringify($job['maximum_experience'] ?? $job['max_experience'] ?? $job['max_exp'] ?? '');
                            $expRange = format_experience_range($minExp, $maxExp);
                            $city = $stringify($job['city'] ?? '');
                            $industry = job_industry_value($job) ?: 'Not specified';
                            $jobFunction = job_function_value($job) ?: 'Not specified';
                            $subFunction = job_sub_function_value($job) ?: 'Not specified';
                            $qualification = job_qualification_value($job, $qualificationsMap) ?: 'Not specified';
                            $noteForCandidates = $stringify($job['note_for_candidates'] ?? '') ?: 'Not specified';
                            $hiringForLabel = job_hiring_for_value($job);
                            $jobId = $job['id'] ?? $job['job_id'] ?? '';
                            $jobSlug = $job['slug'] ?? $job['job_slug'] ?? '';
                            $jobUrl = $job['apply_link'] ?? $job['url'] ?? '../career.php';
                            ?>
                            <a class="open-role-list-card <?php echo $index === 0 ? 'active' : ''; ?> mb-3"
                                href="job-details.php?slug=<?php echo htmlspecialchars((string)$jobSlug, ENT_QUOTES, 'UTF-8'); ?>"
                                data-job-id="<?php echo htmlspecialchars((string)$jobId, ENT_QUOTES, 'UTF-8'); ?>"
                                data-job-slug="<?php echo htmlspecialchars((string)$jobSlug, ENT_QUOTES, 'UTF-8'); ?>"
                                data-title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
                                data-company="<?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?>"
                                data-location="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>"
                                data-type="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                                data-description="<?php echo htmlspecialchars(strip_tags((string)($job['short_description'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                data-apply-url="<?php echo htmlspecialchars($jobUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="open-role-header">
                                    <div class="open-role-brand">
                                        <div class="open-role-logo rounded-circle m-0"><?php echo htmlspecialchars(substr($company ?: 'JD', 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div>
                                            <div class="open-role-title"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="open-role-subtitle"><?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php if (has_external_client_hiring_for_field($job)): ?>
                                                <div class="open-role-subtitle">
                                                    Hiring For: <?php echo htmlspecialchars($hiringForLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="open-role-info">
                                    <div>
                                        <div class="open-role-info-label">Location</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($city ?: 'Not specified', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Industry</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($industry, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Function</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($jobFunction, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Sub Function</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($subFunction, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Educational Qualification</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($qualification, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">YOE</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($expRange, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="open-role-info-note">
                                        <div class="open-role-info-label">Note for Candidates</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($noteForCandidates, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div id="internal-jobs-pagination" class="jobs-pagination"></div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- internal job section end -->

    <section class="open-roles-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-heading">
                    <!-- External Opportunities -->
                    Leadership Roles With us
                </h2>
                <div class="section-divider"></div>
                <!-- <p class="mt-3 text-muted">
                 Global Talent Acquisition Opportunities
                </p> -->
            </div>
            <div class="jobs-filter-form-wrapper">
                <form id="job-search-form">
                    <div class="jobs-filter-top">
                        <div class="jobs-filter-input">
                            <i class="bi bi-search"></i>
                            <input type="search" id="job-search-input" placeholder="Search by Job Title" name="q" value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <button type="button" class="jobs-filter-btn jobs-filter-btn-clear" id="job-search-clear">Clear</button>
                        <button type="button" class="jobs-filter-btn jobs-filter-btn-search" id="job-search-submit">Search</button>
                    </div>
                    <div class="jobs-filter-dropdowns">
                        <div class="jobs-filter-group">
                            <span class="jobs-filter-label">Location</span>
                            <div class="jobs-filter-select">
                            <select id="job-location-select" name="city">
                                <option value="">All</option>
                                <?php foreach ($cityOptions as $city): ?>
                                    <option value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $cityFilter === $city ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                                <path d="M10.5 1.25L6 5.75L1.5 1.25" stroke="#0B3041" stroke-width="1.4" stroke-linecap="round" />
                            </svg>
                            </div>
                        </div>
                        <div class="jobs-filter-group">
                            <span class="jobs-filter-label">Industry</span>
                            <div class="jobs-filter-select">
                            <select id="job-industry-select" name="job_industry">
                                <option value="">All</option>
                                <?php foreach ($industryOptions as $industry): ?>
                                    <option value="<?php echo htmlspecialchars($industry, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $industryFilter === $industry ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($industry, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                                <path d="M10.5 1.25L6 5.75L1.5 1.25" stroke="#0B3041" stroke-width="1.4" stroke-linecap="round" />
                            </svg>
                            </div>
                        </div>
                        <div class="jobs-filter-group">
                            <span class="jobs-filter-label">Function</span>
                            <div class="jobs-filter-select">
                            <select id="job-function-select" name="job_function">
                                <option value="">All</option>
                                <?php foreach ($functionOptions as $function): ?>
                                    <option value="<?php echo htmlspecialchars($function, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $functionFilter === $function ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($function, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                                <path d="M10.5 1.25L6 5.75L1.5 1.25" stroke="#0B3041" stroke-width="1.4" stroke-linecap="round" />
                            </svg>
                            </div>
                        </div>
                        <div class="jobs-filter-group">
                            <span class="jobs-filter-label">Sub Function</span>
                            <div class="jobs-filter-select">
                            <select id="job-sub-function-select" name="job_category">
                                <option value="">All</option>
                                <?php foreach ($subFunctionOptions as $subFunction): ?>
                                    <option value="<?php echo htmlspecialchars($subFunction, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $subFunctionFilter === $subFunction ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($subFunction, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                                <path d="M10.5 1.25L6 5.75L1.5 1.25" stroke="#0B3041" stroke-width="1.4" stroke-linecap="round" />
                            </svg>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($errorMessage): ?>
                <div class="alert alert-light shadow-sm" role="alert">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php elseif (!$jobs): ?>
                <div class="alert alert-light shadow-sm" role="alert">
                    No open roles are available right now. Please check back soon.
                </div>
            <?php else: ?>
                <div class="open-roles-grid">
                    <div id="job-list">
                        <?php
                        $externalRenderIndex = 0;
                        foreach ($jobs as $job):
                            if (!has_external_client_hiring_for_field($job)) {
                                continue;
                            }
                            $index = $externalRenderIndex++;
                        ?>
                            <?php
                            $title = $stringify($job['name'] ?? $job['title'] ?? 'Open Position');
                            $company = $stringify($job['company'] ?? $job['client'] ?? 'James Douglas Global');
                            $location = $stringify($job['location'] ?? ($job['city'] ?? ''));
                            $type = $stringify($job['job_type'] ?? ($job['type'] ?? 'Full time'));
                            $minExp = $stringify($job['minimum_experience'] ?? $job['min_experience'] ?? $job['min_exp'] ?? '');
                            $maxExp = $stringify($job['maximum_experience'] ?? $job['max_experience'] ?? $job['max_exp'] ?? '');
                            $expRange = format_experience_range($minExp, $maxExp);
                            $city = $stringify($job['city'] ?? '');
                            $industry = job_industry_value($job) ?: 'Not specified';
                            $jobFunction = job_function_value($job) ?: 'Not specified';
                            $subFunction = job_sub_function_value($job) ?: 'Not specified';
                            $qualification = job_qualification_value($job, $qualificationsMap) ?: 'Not specified';
                            $noteForCandidates = $stringify($job['note_for_candidates'] ?? '') ?: 'Not specified';
                            $jobId = $job['id'] ?? $job['job_id'] ?? '';
                            $jobSlug = $job['slug'] ?? $job['job_slug'] ?? '';
                            $jobUrl = $job['apply_link'] ?? $job['url'] ?? '../career.php';
                            ?>
                            <a class="open-role-list-card <?php echo $index === 0 ? 'active' : ''; ?> mb-3"
                                href="job-details.php?slug=<?php echo htmlspecialchars((string)$jobSlug, ENT_QUOTES, 'UTF-8'); ?>"
                                data-job-id="<?php echo htmlspecialchars((string)$jobId, ENT_QUOTES, 'UTF-8'); ?>"
                                data-job-slug="<?php echo htmlspecialchars((string)$jobSlug, ENT_QUOTES, 'UTF-8'); ?>"
                                data-title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
                                data-company="<?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?>"
                                data-location="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>"
                                data-type="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>"
                                data-description="<?php echo htmlspecialchars(strip_tags((string)($job['short_description'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                data-apply-url="<?php echo htmlspecialchars($jobUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="open-role-header">
                                    <div class="open-role-brand">
                                            <div class="open-role-logo rounded-circle m-0"><?php echo htmlspecialchars(substr($company ?: 'JD', 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div>
                                            <div class="open-role-title"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="open-role-subtitle"><?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="open-role-info">
                                    <div>
                                        <div class="open-role-info-label">Location</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($city ?: 'Not specified', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Industry</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($industry, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Function</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($jobFunction, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Sub Function</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($subFunction, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Educational Qualification</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($qualification, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">YOE</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($expRange, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="open-role-info-note">
                                        <div class="open-role-info-label">Note for Candidates</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($noteForCandidates, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div id="jobs-pagination" class="jobs-pagination"></div>
                </div>
            <?php endif; ?>
        </div>
    </section>
   
 
    <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 p-3ad">
                <div class="modal-header border-0 pb-0">
                    <h4 class="modal-title fw-bold">Apply for a Position</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <form id="apply-job-form" enctype="multipart/form-data">
                        <input type="hidden" name="job_slug" id="apply-job-slug">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Salary Expectation</label>
                                <input type="number" class="form-control" name="salary_expectation" min="0" step="1" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Current CTC</label>
                                <input type="text" class="form-control" name="current_ctc" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Upload Resume</label>
                                <input type="file" class="form-control" name="resume" required>
                            </div>
                        </div>
                        <div class="mt-4 d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary px-4">Submit Application</button>
                            <div id="apply-job-status" class="small text-muted"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <!-- Work With Us start here -->
     <section class="pb-5 bg-white mt-5" id="apply">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-11 col-xl-11">
                    <div class="contact-form-container">
                        <div class="text-center mb-5">
                            <h2 class="section-heading">Considering your next move?</h2>
                            <div class="section-divider"></div>
                            <p class="mt-4 text-muted" style="font-size: 1.1rem;">
                               We work with professionals across management and leadership roles, often before opportunities are publicly visible. Share your details and we’ll reach out when there’s a relevant conversation to have.
                            </p>
                        </div>

                        <form class="contact-form" id="applyForm" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="work_with_us">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workFirstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control contact-input" id="workFirstName"
                                            name="first_name" placeholder="Enter first name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workLastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control contact-input" id="workLastName"
                                            name="last_name" placeholder="Enter last name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workPhone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control contact-input" id="workPhone"
                                            name="phone" placeholder="+91 98XXXXXX" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control contact-input" id="workEmail"
                                            name="email" placeholder="you@example.com" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workFunction" class="form-label">Function / Department</label>
                                        <input type="text" class="form-control contact-input" id="workFunction"
                                            name="function" placeholder="e.g. Sales, Finance, HR" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workIndustry" class="form-label">Industry</label>
                                        <input type="text" class="form-control contact-input" id="workIndustry"
                                            name="industry" placeholder="Enter your industry" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workJobTitle" class="form-label">Job Title</label>
                                        <input type="text" class="form-control contact-input" id="workJobTitle"
                                            name="job_title" placeholder="Enter your current job title" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workOrganization" class="form-label">Current Organization</label>
                                        <input type="text" class="form-control contact-input" id="workOrganization"
                                            name="current_organization" placeholder="Enter current organization" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workCurrentSalary" class="form-label">Current CTC</label>
                                        <input type="text" class="form-control contact-input" id="workCurrentSalary"
                                            name="current_salary" placeholder="Enter current CTC" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workLinkedIn" class="form-label">LinkedIn</label>
                                        <input type="url" class="form-control contact-input" id="workLinkedIn"
                                            name="linkedin_profile" placeholder="https://www.linkedin.com/in/your-profile" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="workResume" class="form-label">Resume</label>
                                        <input type="file" class="form-control contact-input" id="workResume"
                                            name="resume" accept=".pdf,.doc,.docx" required>
                                    </div>
                                </div>

                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-submit">Register your profile</button>
                                    <div id="workWithUsStatus" class="small text-muted mt-3"></div>
                                </div>
                            </div>
                        </form>
                        <!-- /form -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Work With Us end here -->

    <!-- job cards container (client-rendered fallback) -->
    <section class="py-5">
        <div class="container">
            <div id="job-container" class="row g-4"></div>
        </div>
    </section>

    <script>
        // reuse helper from api-test.html in JS context
        function renderJobCard(job) {
            const title = job.name ?? job.title ?? 'Untitled';
            const city = job.city ?? job.location ?? 'N/A';
            const minSal = job.min_annual_salary ?? 0;
            const maxSal = job.max_annual_salary ?? 0;
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';
            const card = document.createElement('div');
            card.className = 'p-3 border rounded bg-white h-100';
            card.innerHTML = `
                <h5 class="mb-2">${title}</h5>
                <p class="mb-1"><strong>Location:</strong> ${city}</p>
                <p class="mb-0"><strong>Salary:</strong> ${minSal} - ${maxSal}</p>
            `;
            col.appendChild(card);
            return col;
        }

        const jobsData = <?php echo json_encode($jobs, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?> || [];
        const jobContainer = document.getElementById('job-container');
        jobsData.forEach(job => {
            jobContainer.appendChild(renderJobCard(job));
        });
    </script>

    <!-- footer starts -->
    <?php include '../inc/footer2.php'; ?>
    <!-- footer ends -->

    <script src="../assets/js/main.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="../assets/js/main.js"></script>


    <!-- Contact Form JavaScript -->
    <script>
        const workWithUsForm = document.getElementById('applyForm');
        const workWithUsStatus = document.getElementById('workWithUsStatus');
        if (workWithUsForm && workWithUsStatus) {
            workWithUsForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                workWithUsStatus.textContent = 'Submitting...';
                workWithUsStatus.classList.remove('text-danger', 'text-success');
                workWithUsStatus.classList.add('text-muted');

                const formData = new FormData(workWithUsForm);
                try {
                    const response = await fetch('apply-job.php', {
                        method: 'POST',
                        body: formData,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && !data.error) {
                        workWithUsStatus.textContent = data.message || 'Submitted successfully.';
                        workWithUsStatus.classList.remove('text-muted', 'text-danger');
                        workWithUsStatus.classList.add('text-success');
                        workWithUsForm.reset();
                    } else {
                        workWithUsStatus.textContent = data.message || 'Failed to submit.';
                        workWithUsStatus.classList.remove('text-muted', 'text-success');
                        workWithUsStatus.classList.add('text-danger');
                    }
                } catch (error) {
                    workWithUsStatus.textContent = 'Failed to submit.';
                    workWithUsStatus.classList.remove('text-muted', 'text-success');
                    workWithUsStatus.classList.add('text-danger');
                }
            });
        }
    </script>

    <script>
        // Handle carousel indicators active state (guard for non-home pages)
        const carousel = document.getElementById('teamCarousel');
        const indicators = document.querySelectorAll('.carousel-indicators button');
        if (carousel && indicators.length) {
            carousel.addEventListener('slide.bs.carousel', function (event) {
                indicators.forEach(indicator => {
                    indicator.classList.remove('active');
                    indicator.removeAttribute('aria-current');
                });

                const activeIndicator = indicators[event.to];
                if (activeIndicator) {
                    activeIndicator.classList.add('active');
                    activeIndicator.setAttribute('aria-current', 'true');
                }
            });
        }
    </script>
    <script>
        // Initialize Swiper (guard for non-home pages)
        if (typeof Swiper !== 'undefined' && document.querySelector('.bannerSwiper')) {
            var bannerSwiper = new Swiper(".bannerSwiper", {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        }
    </script>
    <script>
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });
        }
    </script>
    <script>
        function paginateInternalJobs(pageSize = 9) {
            const list = document.getElementById('internal-job-list');
            const pagination = document.getElementById('internal-jobs-pagination');
            if (!list || !pagination) {
                return;
            }

            const cards = Array.from(list.querySelectorAll('.open-role-list-card'));
            if (!cards.length) {
                pagination.innerHTML = '';
                return;
            }

            const totalPages = Math.ceil(cards.length / pageSize);
            let currentPage = 1;

            const renderPage = (page) => {
                currentPage = Math.max(1, Math.min(totalPages, page));
                const start = (currentPage - 1) * pageSize;
                const end = start + pageSize;
                cards.forEach((card, index) => {
                    card.style.display = index >= start && index < end ? '' : 'none';
                });
                renderPagination();
            };

            const renderPagination = () => {
                if (totalPages <= 1) {
                    pagination.innerHTML = '';
                    return;
                }
                let html = '';
                html += `<button class="pagination-btn" data-page="${Math.max(1, currentPage - 1)}" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>`;
                const start = Math.max(1, currentPage - 2);
                const end = Math.min(totalPages, currentPage + 2);
                if (start > 1) {
                    html += `<button class="pagination-btn" data-page="1">1</button>`;
                    if (start > 2) {
                        html += `<span class="pagination-ellipsis">…</span>`;
                    }
                }
                for (let i = start; i <= end; i += 1) {
                    html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
                }
                if (end < totalPages) {
                    if (end < totalPages - 1) {
                        html += `<span class="pagination-ellipsis">…</span>`;
                    }
                    html += `<button class="pagination-btn" data-page="${totalPages}">${totalPages}</button>`;
                }
                html += `<button class="pagination-btn" data-page="${Math.min(totalPages, currentPage + 1)}" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
                pagination.innerHTML = html;
                pagination.querySelectorAll('.pagination-btn').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const nextPage = parseInt(btn.dataset.page || '1', 10);
                        if (Number.isNaN(nextPage) || nextPage === currentPage) {
                            return;
                        }
                        renderPage(nextPage);
                        list.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });
            };

            renderPage(1);
        }

        paginateInternalJobs();

        const jobCards = document.querySelectorAll('.open-role-list-card');
        const applyModalEl = document.getElementById('applyModal');
        const applyModal = applyModalEl ? new bootstrap.Modal(applyModalEl) : null;
        const applyForm = document.getElementById('apply-job-form');
        const applyJobSlug = document.getElementById('apply-job-slug');
        const applyStatus = document.getElementById('apply-job-status');

        function setActiveCard(card) {
            jobCards.forEach((item) => item.classList.remove('active'));
            card.classList.add('active');
        }

        function bindJobCardEvents() {
            const cards = document.querySelectorAll('.open-role-list-card');
            cards.forEach((card) => {
                card.addEventListener('click', () => {
                    setActiveCard(card);
                });
            });
        }

        bindJobCardEvents();

        if (applyForm) {
            applyForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                applyStatus.textContent = 'Submitting...';
                const formData = new FormData(applyForm);
                try {
                    const response = await fetch('apply-job.php', {
                        method: 'POST',
                        body: formData,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && !data.error) {
                        applyStatus.textContent = 'Application submitted successfully.';
                        applyStatus.classList.remove('text-danger');
                        applyStatus.classList.add('text-success');
                        applyForm.reset();
                    } else {
                        applyStatus.textContent = data.message || 'Failed to submit application.';
                        applyStatus.classList.remove('text-success');
                        applyStatus.classList.add('text-danger');
                    }
                } catch (error) {
                    applyStatus.textContent = 'Failed to submit application.';
                    applyStatus.classList.remove('text-success');
                    applyStatus.classList.add('text-danger');
                }
            });
        }

        const searchForm = document.getElementById('job-search-form');
        const searchInput = document.getElementById('job-search-input');
        const clearButton = document.getElementById('job-search-clear');
        const submitButton = document.getElementById('job-search-submit');
        const locationSelect = document.getElementById('job-location-select');
        const industrySelect = document.getElementById('job-industry-select');
        const functionSelect = document.getElementById('job-function-select');
        const subFunctionSelect = document.getElementById('job-sub-function-select');

        if (searchForm && searchInput) {
            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
            });

            let searchTimer = null;
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    applyFilters(1);
                }, 250);
            });
        }

        if (submitButton) {
            submitButton.addEventListener('click', () => {
                applyFilters(1);
            });
        }

        if (clearButton) {
            clearButton.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                }
                if (locationSelect) locationSelect.value = '';
                if (industrySelect) industrySelect.value = '';
                if (functionSelect) functionSelect.value = '';
                if (subFunctionSelect) subFunctionSelect.value = '';
                applyFilters(1);
            });
        }

        [locationSelect, industrySelect, functionSelect, subFunctionSelect].forEach((select) => {
            if (!select) return;
            select.addEventListener('change', () => {
                applyFilters(1);
            });
        });

        const API_URL = 'https://api.recruitcrm.io/v1/jobs';
        const API_TOKEN = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';
        const LIMIT = 6;
        const CLIENT_PAGE_SIZE = LIMIT;
        const INDUSTRY_OPTIONS = [
            'Biotech',
            'Clinical Research',
            'Pharmaceutical',
            'Medical Devices',
            'Medical Equipments',
            'Medical Diagnostics',
            'CDMO',
            'API',
            'Consumer Healthcare',
            'Clinics & Labs',
            'Hospitals',
            'Building Materials',
            'Chemicals',
            'Automobile',
            'Auto Components',
            'Defence & Aerospace',
            'Electrical Equipment',
            'Agrochemicals',
            'Industrial Automation',
            'Industrial Equipment/Machinery',
            'Iron & Steel',
            'Metals & Mining',
            'Packaging',
            'Petrochemicals',
            'Plastics & Rubber',
            'Aviation',
            'Logistics',
            'EPC',
            'Oil & Gas',
            'Ports & Shipping',
            'Power',
            'Real Estate',
            'Construction',
            'FMCG',
            'FMCD',
            'Beauty & Personal Care',
            'Beverage',
            'Fitness & Wellness',
            'Furniture & Furnishing',
            'Gems & Jewellery',
            'Hospitality',
            'Retail',
            'Textile & Apparel',
            'Travel & Tourism',
            'Financial Services',
            'Banking',
            'Credit Rating',
            'Life Insurance',
            'General Insurance',
            'Health Insurance',
            'NBFC',
            'Capital Markets/Securities/Broking',
            'Mutual Funds & AMC',
            'MII',
            'Investment Banking/Management',
            'PE/VC',
            'Payment-Tech',
            'Lending-Tech',
            'Wealth-Tech',
            'Insur-Tech',
            'Reg-Tech',
            'Crypto',
            'Consumer-Tech',
            'D2C Tech',
            'Retail-Tech',
            'Health-Tech',
            'Ed-Tech',
            'Prop-Tech',
            'Travel-Tech',
            'Logi-Tech',
            'HR-Tech',
            'Analytics Platform',
            'B2B SaaS',
            'ITeS',
            'Gaming',
            'Telecom',
            'Advertising',
            'PR',
            'Media',
            'Accounting',
            'Business Consulting & Services',
            'Law Firms',
            'NGOs',
            'Market Research',
            'Sports/Leisure/Recreation',
            'Education',
            'Miscellaneous',
        ];
        const FUNCTION_OPTIONS = [
            'Healthcare & Lifesciences',
            'B2B',
            'Property & Construction',
            'B2C',
            'Banking & Financial Services',
            'FinTech',
            'Technology',
            'Media, Entertainment & Telecom',
            'Professional Services',
            'Miscellaneous',
        ];
        let externalJobsCache = null;

        function normalizeHiringForValue(value) {
            const normalized = String(value ?? '')
                .replace(/\u2013|\u2014/g, '-')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, ' ');

            if (normalized === 'internal - to be posted on join us' || normalized === 'client (internal)') {
                return 'internal';
            }
            if (
                normalized === 'external - to be posted to "find opportunities"' ||
                normalized === "external - to be posted to 'find opportunities'" ||
                normalized === 'external - to be posted to find opportunities' ||
                normalized === 'client (external)'
            ) {
                return 'external';
            }
            if (normalized === 'do not post' || normalized === 'do not post (confidential)') {
                return 'hidden';
            }
            return '';
        }

        /**
         * Check if a job is marked for external posting.
         */
        function isExternalClientJob(job) {
            const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];

            return fields.some((field) => {
                if (!field || typeof field !== 'object') return false;

                const fieldId = Number(field.field_id ?? field.id ?? 0);
                const entityType = String(field.entity_type ?? '').trim().toLowerCase();
                const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                const fieldType = String(field.field_type ?? field.type ?? '').trim().toLowerCase();

                let value = field.value ?? field.field_value ?? field.selected ?? '';
                if (value && typeof value === 'object') {
                    value = value.value ?? value.label ?? value.name ?? '';
                }
                value = String(value ?? '').trim();

                return (
                    fieldId === 7 &&
                    entityType === 'job' &&
                    fieldName === 'hiring for' &&
                    fieldType === 'dropdown' &&
                    normalizeHiringForValue(value) === 'external'
                );
            });
        }

        function isInternalClientJob(job) {
            const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];

            return fields.some((field) => {
                if (!field || typeof field !== 'object') return false;

                const fieldId = Number(field.field_id ?? field.id ?? 0);
                const entityType = String(field.entity_type ?? '').trim().toLowerCase();
                const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                const fieldType = String(field.field_type ?? field.type ?? '').trim().toLowerCase();

                let value = field.value ?? field.field_value ?? field.selected ?? '';
                if (value && typeof value === 'object') {
                    value = value.value ?? value.label ?? value.name ?? '';
                }

                return (
                    fieldId === 7 &&
                    entityType === 'job' &&
                    fieldName === 'hiring for' &&
                    fieldType === 'dropdown' &&
                    normalizeHiringForValue(value) === 'internal'
                );
            });
        }

        /**
         * Check if a job should not be posted anywhere.
         */
        function isConfidentialJob(job) {
            const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];

            return fields.some((field) => {
                if (!field || typeof field !== 'object') return false;

                const fieldId = Number(field.field_id ?? field.id ?? 0);
                const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();

                if (fieldId !== 7 && fieldName !== 'hiring for') return false;

                let value = field.value ?? field.field_value ?? field.selected ?? '';
                if (value && typeof value === 'object') {
                    value = value.value ?? value.label ?? value.name ?? '';
                }

                return normalizeHiringForValue(value) === 'hidden';
            });
        }

        function isJobOpen(job) {
            const status = job?.job_status;
            if (status && typeof status === 'object') {
                const label = String(status.label ?? status.name ?? '').trim();
                if (label) {
                    return label.toLowerCase() === 'open';
                }
                return Number(status.id ?? 0) === 1;
            }
            return String(status ?? '').trim().toLowerCase() === 'open';
        }

        function shouldPostOnWebsite(job) {
            const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];

            return fields.some((field) => {
                if (!field || typeof field !== 'object') return false;

                const fieldId = Number(field.field_id ?? field.id ?? 0);
                const entityType = String(field.entity_type ?? '').trim().toLowerCase();
                const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                const fieldType = String(field.field_type ?? field.type ?? '').trim().toLowerCase();

                let value = field.value ?? field.field_value ?? field.selected ?? '';
                if (value && typeof value === 'object') {
                    value = value.value ?? value.label ?? value.name ?? '';
                }

                return (
                    fieldId === 10 &&
                    entityType === 'job' &&
                    fieldName === 'post on website' &&
                    fieldType === 'dropdown' &&
                    String(value ?? '').trim().toLowerCase() === 'yes'
                );
            });
        }

        /**
         * Fetch a single page of jobs from the RecruitCRM API.
         * @param {string} url - Full URL including query params
         * @returns {Promise<{ jobs: Array, nextPageUrl: string|null }>}
         */
        async function fetchPage(url) {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': API_TOKEN,
                },
            });

            if (!response.ok) {
                throw new Error(`API request failed with status ${response.status}`);
            }

            const data = await response.json();
            const jobs = data.data ?? data.jobs ?? (Array.isArray(data) ? data : []);
            const nextPageUrl = data.next_page_url ?? null;

            return { jobs, nextPageUrl };
        }

        /**
         * Fetch ALL pages and return only External Client jobs (non-confidential).
         * @param {number} limit - Items per page (passed to API)
         * @param {number} maxPages - Safety cap to avoid infinite loops
         * @returns {Promise<Array>}
         */
        async function fetchAllExternalClientJobs(limit = LIMIT, maxPages = 100) {
            let allJobs = [];
            let nextUrl = `${API_URL}?limit=${limit}`;
            let pageCount = 0;

            while (nextUrl && pageCount < maxPages) {
                pageCount++;
                const { jobs, nextPageUrl } = await fetchPage(nextUrl);
                allJobs = allJobs.concat(jobs);
                nextUrl = nextPageUrl;
            }

            return allJobs.filter((job) =>
                !isConfidentialJob(job) &&
                isExternalClientJob(job) &&
                isJobOpen(job) &&
                shouldPostOnWebsite(job)
            );
        }

        /**
         * Fetch only the first page (6 jobs) and filter.
         * @returns {Promise<Array>}
         */
        async function fetchExternalClientJobsFirstPage() {
            const { jobs } = await fetchPage(`${API_URL}?limit=${LIMIT}`);
            return jobs.filter((job) =>
                !isConfidentialJob(job) &&
                isExternalClientJob(job) &&
                isJobOpen(job) &&
                shouldPostOnWebsite(job)
            );
        }

        async function getExternalJobs() {
            if (!externalJobsCache) {
                externalJobsCache = await fetchAllExternalClientJobs();
            }
            return externalJobsCache;
        }

        function getIndustryValue(job) {
            const candidates = [
                job?.custom_fields,
                job?.custom_field,
                job?.custom_fields_values,
                job?.custom_field_values,
                job?.fields,
            ];

            for (const fields of candidates) {
                if (!Array.isArray(fields)) {
                    continue;
                }
                for (const field of fields) {
                    if (!field || typeof field !== 'object') {
                        continue;
                    }
                    const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
                    const fieldId = Number(field.field_id ?? field.id ?? 0);
                    if (fieldId !== 3 && fieldName !== 'job - industry') {
                        continue;
                    }
                    let value = field.value ?? field.field_value ?? field.selected ?? '';
                    if (Array.isArray(value)) {
                        const flat = value.map((item) => String(item)).filter((item) => item.trim() !== '');
                        if (flat.length) {
                            return flat[0].trim();
                        }
                        value = value.value ?? value.label ?? value.name ?? '';
                    }
                    return String(value ?? '').trim();
                }
            }

            const fallback = job?.job_industry ?? '';
            if (Array.isArray(fallback)) {
                return fallback.map((item) => String(item)).filter((item) => item.trim() !== '').join(', ').trim();
            }
            return String(fallback ?? '').trim();
        }

        function getFunctionValue(job) {
            const fallback = job?.job_function ?? job?.function ?? '';
            if (Array.isArray(fallback)) {
                return fallback.map((item) => String(item)).filter((item) => item.trim() !== '').join(', ').trim();
            }
            return String(fallback ?? '').trim();
        }

        function getSubFunctionValue(job) {
            const fallback = job?.job_category ?? job?.category ?? '';
            if (Array.isArray(fallback)) {
                return fallback.map((item) => String(item)).filter((item) => item.trim() !== '').join(', ').trim();
            }
            return String(fallback ?? '').trim();
        }

        function getQualificationValue(job) {
            const directValue = job?.qualification?.label ?? job?.qualification?.name ?? job?.qualification_name ?? job?.qualification_label ?? '';
            return String(directValue ?? '').trim();
        }

        function populateOptions(select, values, selectedValue) {
            if (!select) {
                return;
            }

            const lookup = {};
            const uniqueValues = [];

            values.forEach((value) => {
                const normalized = String(value ?? '').trim();
                if (!normalized) {
                    return;
                }
                const key = normalized.toLowerCase();
                if (!lookup[key]) {
                    lookup[key] = true;
                    uniqueValues.push(normalized);
                }
            });

            uniqueValues.sort((a, b) => a.localeCompare(b));
            select.innerHTML = '<option value="">All</option>';

            uniqueValues.forEach((value) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                select.appendChild(option);
            });

            if (selectedValue && lookup[selectedValue.toLowerCase()]) {
                select.value = selectedValue;
            }
        }

        async function fetchFilteredJobs(queryString) {
            const params = new URLSearchParams(queryString || '');
            const requestedPage = Math.max(1, parseInt(params.get('page') || '1', 10) || 1);
            try {
                const allExternalJobs = await getExternalJobs();
                const selectedIndustry = params.get('job_industry') ? params.get('job_industry').split(',')[0] : '';
                const selectedFunction = params.get('job_function') ? params.get('job_function').split(',')[0] : '';
                const selectedSubFunction = params.get('job_category') ? params.get('job_category').split(',')[0] : '';
                populateOptions(industrySelect, INDUSTRY_OPTIONS, selectedIndustry);
                populateOptions(functionSelect, FUNCTION_OPTIONS, selectedFunction);
                populateOptions(subFunctionSelect, allExternalJobs.map((job) => getSubFunctionValue(job)), selectedSubFunction);
                const jobs = applyClientFilters(allExternalJobs, params);
                const total = jobs.length;
                const lastPage = Math.max(1, Math.ceil(total / CLIENT_PAGE_SIZE));
                const currentPage = Math.min(requestedPage, lastPage);
                const offset = (currentPage - 1) * CLIENT_PAGE_SIZE;
                const pageJobs = jobs.slice(offset, offset + CLIENT_PAGE_SIZE);

                renderJobList(pageJobs);
                renderPagination({
                    current_page: currentPage,
                    per_page: CLIENT_PAGE_SIZE,
                    total,
                    last_page: lastPage,
                });
            } catch (error) {
                console.error('Error fetching jobs:', error.message);
                const list = document.getElementById('job-list');
                if (list) {
                    list.innerHTML = '<div class="alert alert-light shadow-sm" role="alert">Unable to load open roles right now.</div>';
                }
            }
        }

        function applyClientFilters(jobs, params) {
            const q = String(params.get('q') || '').trim().toLowerCase();
            const city = String(params.get('city') || '').trim().toLowerCase();
            const industry = String(params.get('job_industry') || '').trim().toLowerCase();
            const jobFunction = String(params.get('job_function') || '').trim().toLowerCase();
            const subFunction = String(params.get('job_category') || '').trim().toLowerCase();

            return jobs.filter((job) => {
                const title = String(job?.name ?? job?.title ?? '').toLowerCase();
                const jobCity = String(job?.city ?? '').toLowerCase();
                const jobIndustry = getIndustryValue(job).toLowerCase();
                const currentFunction = getFunctionValue(job).toLowerCase();
                const currentSubFunction = getSubFunctionValue(job).toLowerCase();

                if (q && !title.includes(q)) return false;
                if (city && jobCity !== city) return false;
                if (industry && jobIndustry !== industry) return false;
                if (jobFunction && currentFunction !== jobFunction) return false;
                if (subFunction && currentSubFunction !== subFunction) return false;

                return true;
            });
        }

        function getHiringForValue(job) {
            const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];
            const match = fields.find((field) => {
                if (!field || typeof field !== 'object') {
                    return false;
                }
                const fieldId = Number(field.field_id ?? field.id ?? 0);
                return fieldId === 7;
            });
            let value = match?.value ?? '';
            if (value && typeof value === 'object') {
                value = value.value ?? value.label ?? value.name ?? '';
            }
            return String(value ?? '').trim();
        }

        function formatExperienceRange(minExp, maxExp) {
            const min = String(minExp ?? '').trim();
            const max = String(maxExp ?? '').trim();

            if (min && max) {
                return min === max ? `${min} Years` : `${min} to ${max} Years`;
            }

            if (min) {
                return `${min} Years`;
            }

            if (max) {
                return `${max} Years`;
            }

            return 'Not specified';
        }

        function renderPagination(pagination) {
            const container = document.getElementById('jobs-pagination');
            if (!container) {
                return;
            }
            if (!pagination || pagination.last_page <= 1) {
                container.innerHTML = '';
                return;
            }
            const current = pagination.current_page || 1;
            const last = pagination.last_page || 1;
            let html = '';
            html += `<button class="pagination-btn" data-page="${Math.max(1, current - 1)}" ${current === 1 ? 'disabled' : ''}>Prev</button>`;
            const start = Math.max(1, current - 2);
            const end = Math.min(last, current + 2);
            if (start > 1) {
                html += `<button class="pagination-btn" data-page="1">1</button>`;
                if (start > 2) {
                    html += `<span class="pagination-ellipsis">…</span>`;
                }
            }
            for (let i = start; i <= end; i += 1) {
                html += `<button class="pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
            }
            if (end < last) {
                if (end < last - 1) {
                    html += `<span class="pagination-ellipsis">…</span>`;
                }
                html += `<button class="pagination-btn" data-page="${last}">${last}</button>`;
            }
            html += `<button class="pagination-btn" data-page="${Math.min(last, current + 1)}" ${current === last ? 'disabled' : ''}>Next</button>`;
            container.innerHTML = html;
            container.querySelectorAll('.pagination-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const nextPage = parseInt(btn.dataset.page || '1', 10);
                    if (Number.isNaN(nextPage) || nextPage === current) {
                        return;
                    }
                    applyFilters(nextPage);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        function renderJobList(jobs) {
            const list = document.getElementById('job-list');
            if (!list) {
                return;
            }
            list.innerHTML = '';
            if (!jobs.length) {
                list.innerHTML = '<div class="alert alert-light shadow-sm" role="alert">No open roles are available right now. Please check back soon.</div>';
                return;
            }

            jobs.forEach((job, index) => {
                const title = job.name || job.title || 'Open Position';
                const company = job.company || job.client || 'James Douglas Global';
                const city = job.city || '';
                const minExp = String(job.minimum_experience ?? job.min_experience ?? '').trim();
                const maxExp = String(job.maximum_experience ?? job.max_experience ?? '').trim();
                const expRange = formatExperienceRange(minExp, maxExp);
                const industry = getIndustryValue(job) || 'Not specified';
                const jobFunction = getFunctionValue(job) || 'Not specified';
                const subFunction = getSubFunctionValue(job) || 'Not specified';
                const qualification = getQualificationValue(job) || 'Not specified';
                const noteForCandidates = String(job.note_for_candidates ?? '').trim() || 'Not specified';
                const jobId = job.id ?? job.job_id ?? '';
                const jobSlug = job.slug ?? job.job_slug ?? '';
                const jobUrl = job.apply_link || job.url || '../career.php';
                const hiringForLabel = getHiringForValue(job);

                const card = document.createElement('a');
                card.className = `open-role-list-card ${index === 0 ? 'active' : ''} mb-3`;
                card.href = `job-details.php?slug=${encodeURIComponent(jobSlug)}`;
                card.dataset.jobId = jobId;
                card.dataset.jobSlug = jobSlug;
                card.dataset.title = title;
                card.dataset.company = company;
                card.dataset.location = city;
                card.dataset.description = (job.short_description || '').toString();
                card.dataset.applyUrl = jobUrl;

                card.innerHTML = `
                    <div class="open-role-header">
                        <div class="open-role-brand">
                            <div class="open-role-logo rounded-circle m-0">${(company || 'JD').charAt(0)}</div>
                            <div>
                                <div class="open-role-title">${title}</div>
                                <div class="open-role-subtitle">${company}</div>
                                ${normalizeHiringForValue(hiringForLabel) === 'external' ? `<div class="open-role-subtitle">Hiring For: ${hiringForLabel}</div>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="open-role-info">
                        <div>
                            <div class="open-role-info-label">Location</div>
                            <div class="open-role-info-value">${city || 'Not specified'}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">Industry</div>
                            <div class="open-role-info-value">${industry}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">Function</div>
                            <div class="open-role-info-value">${jobFunction}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">Sub Function</div>
                            <div class="open-role-info-value">${subFunction}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">Educational Qualification</div>
                            <div class="open-role-info-value">${qualification}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">YOE</div>
                            <div class="open-role-info-value">${expRange}</div>
                        </div>
                        <div class="open-role-info-note">
                            <div class="open-role-info-label">Note for Candidates</div>
                            <div class="open-role-info-value">${noteForCandidates}</div>
                        </div>
                    </div>
                `;

                list.appendChild(card);
            });

            const firstCard = list.querySelector('.open-role-list-card');
            if (firstCard) {
                setActiveCard(firstCard);
            }
            bindJobCardEvents();
        }

        function getActiveFilters() {
            const active = {
                q: searchInput ? searchInput.value.trim() : '',
                city: [],
                job_industry: [],
                job_function: [],
                job_category: [],
            };

            if (locationSelect && locationSelect.value) {
                active.city = [locationSelect.value];
            }
            if (industrySelect && industrySelect.value) {
                active.job_industry = [industrySelect.value];
            }
            if (functionSelect && functionSelect.value) {
                active.job_function = [functionSelect.value];
            }
            if (subFunctionSelect && subFunctionSelect.value) {
                active.job_category = [subFunctionSelect.value];
            }

            return active;
        }

        function buildFilterQuery(filters, page = 1) {
            const params = new URLSearchParams();
            if (filters.q) {
                params.set('q', filters.q);
            }
            if (filters.city.length) {
                params.set('city', filters.city.join(','));
            }
            if (filters.job_industry.length) {
                params.set('job_industry', filters.job_industry.join(','));
            }
            if (filters.job_function.length) {
                params.set('job_function', filters.job_function.join(','));
            }
            if (filters.job_category.length) {
                params.set('job_category', filters.job_category.join(','));
            }
            if (page > 1) {
                params.set('page', String(page));
            }
            return params.toString();
        }

       function applyFilters(page = 1) {
  const filters = getActiveFilters();
  const queryString = buildFilterQuery(filters, page);

  const hash = window.location.hash || ''; // ✅ keep existing hash
  const newUrl = `${window.location.pathname}${queryString ? `?${queryString}` : ''}${hash}`;

  window.history.replaceState({}, '', newUrl);
  fetchFilteredJobs(queryString);
}


        function setActiveFiltersFromParams(params) {
            if (locationSelect) {
                const cityValue = params.get('city') || '';
                locationSelect.value = cityValue ? cityValue.split(',')[0] : '';
            }
            if (industrySelect) {
                industrySelect.value = params.get('job_industry') ? params.get('job_industry').split(',')[0] : '';
            }
            if (functionSelect) {
                functionSelect.value = params.get('job_function') ? params.get('job_function').split(',')[0] : '';
            }
            if (subFunctionSelect) {
                subFunctionSelect.value = params.get('job_category') ? params.get('job_category').split(',')[0] : '';
            }
        }

        const initialParams = new URLSearchParams(window.location.search);
        if (initialParams.has('locality') && !initialParams.has('city')) {
            initialParams.set('city', initialParams.get('locality') || '');
            initialParams.delete('locality');
            const normalizedQuery = initialParams.toString();
            window.history.replaceState({}, '', `${window.location.pathname}${normalizedQuery ? `?${normalizedQuery}` : ''}`);
        }
        const initialPage = initialParams.get('page');
        setActiveFiltersFromParams(initialParams);
        applyFilters(initialPage ? parseInt(initialPage, 10) || 1 : 1);
    </script>

    <script>
 document.addEventListener('DOMContentLoaded', () => {
  const hash = window.location.hash;
  if (!hash) return;

  setTimeout(() => {
    const el = document.querySelector(hash);
    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }, 200);
});

</script>


</body>

</html>
 
