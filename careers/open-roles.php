<?php
declare(strict_types=1);

$apiUrl = 'https://api.recruitcrm.io/v1/jobs';
$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

$jobs = [];
$allJobs = [];
$errorMessage = '';
$searchQuery = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$categoryFilter = isset($_GET['job_category']) ? trim((string)$_GET['job_category']) : '';
$cityFilter = isset($_GET['city']) ? trim((string)$_GET['city']) : '';
$localityFilter = isset($_GET['locality']) ? trim((string)$_GET['locality']) : '';
$industryFilter = isset($_GET['job_industry']) ? trim((string)$_GET['job_industry']) : '';
$salaryFilter = isset($_GET['salary_range']) ? trim((string)$_GET['salary_range']) : '';

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

$errorMessage = '';
$nextUrl = $apiUrl;
$maxPages = 20;
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

$internalJobs = array_values(array_filter($allJobs, function ($job) {
    $hiringFor = strtolower(job_hiring_for_value($job));
    return $hiringFor !== 'client (external)';
}));
$externalJobs = array_values(array_filter($allJobs, function ($job) {
    $hiringFor = strtolower(job_hiring_for_value($job));
    return $hiringFor === 'client (external)';
}));

$jobs = $externalJobs;

if ($cityFilter !== '') {
    $searchUrl = $apiUrl . '/search?city=' . urlencode($cityFilter);
    $response = recruitcrm_get($searchUrl, $apiToken);
    if ($response['error']) {
        $errorMessage = 'We could not load open roles right now. Please try again later.';
    } else {
        $data = json_decode($response['body'], true);
        if ($response['status'] >= 200 && $response['status'] < 300 && is_array($data)) {
            $jobs = extract_jobs($data);
            $jobs = array_values(array_filter($jobs, function ($job) {
                $hiringFor = strtolower(job_hiring_for_value($job));
                return $hiringFor === 'client (external)';
            }));
        }
    }
}

if ($searchQuery !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($searchQuery, $stringify) {
        $title = $stringify($job['name'] ?? $job['title'] ?? '');
        return stripos($title, $searchQuery) !== false;
    }));
}

if ($categoryFilter !== '') {
    $jobs = array_values(array_filter($jobs, function ($job) use ($categoryFilter, $stringify) {
        $category = $stringify($job['job_category'] ?? $job['category'] ?? '');
        return strcasecmp($category, $categoryFilter) === 0;
    }));
}

if ($salaryFilter !== '') {
    $ranges = array_filter(array_map('trim', explode(',', $salaryFilter)));
    $jobs = array_values(array_filter($jobs, function ($job) use ($ranges) {
        $min = is_numeric($job['min_annual_salary'] ?? null) ? (int)$job['min_annual_salary'] : 0;
        $max = is_numeric($job['max_annual_salary'] ?? null) ? (int)$job['max_annual_salary'] : 0;
        foreach ($ranges as $range) {
            if ($range === '20000000+') {
                if ($max >= 20000000 || $min >= 20000000) {
                    return true;
                }
            } else {
                [$rMin, $rMax] = array_pad(explode('-', $range), 2, null);
                $rMin = (int)$rMin;
                $rMax = (int)$rMax;
                if ($max === 0 && $min === 0) {
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

$categories = [];
$cities = [];
$localities = [];
$industries = [];
$salaryCounts = [];
$categoryOptions = [
    'HR',
    'Human Resources',
    'Marketing',
    'Sales',
    'Technology',
];
$salaryRanges = [
    '0-5000000' => 'Up to 50 LPA',
    '5000000-10000000' => '50 LPA - 1 Cr',
    '10000000-20000000' => '1 Cr - 2 Cr',
    '20000000+' => '2 Cr+',
];
$industryOptions = [];
$industryOptionLookup = [];
foreach ($externalJobs as $job) {
    $industry = trim($stringify($job['job_industry'] ?? ''));
    if ($industry === '') {
        continue;
    }
    $key = strtolower($industry);
    if (!isset($industryOptionLookup[$key])) {
        $industryOptionLookup[$key] = true;
        $industryOptions[] = $industry;
    }
}
if ($industryOptions) {
    natcasesort($industryOptions);
    $industryOptions = array_values($industryOptions);
    $industryOptionLookup = array_fill_keys(array_map('strtolower', $industryOptions), true);
}

if ($industryFilter !== '') {
    if (isset($industryOptionLookup[strtolower($industryFilter)])) {
        $jobs = array_values(array_filter($jobs, function ($job) use ($industryFilter, $stringify) {
            $jobIndustry = $stringify($job['job_industry'] ?? '');
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
    $category = $stringify($job['job_category'] ?? $job['category'] ?? '');
    $city = $stringify($job['city'] ?? '');
    $location = $stringify($job['location'] ?? '');
    $locality = $stringify($job['locality'] ?? '');
    $state = $stringify($job['state'] ?? '');
    $industry = $stringify($job['job_industry'] ?? '');
    $minSalary = is_numeric($job['min_annual_salary'] ?? null) ? (int)$job['min_annual_salary'] : 0;
    $maxSalary = is_numeric($job['max_annual_salary'] ?? null) ? (int)$job['max_annual_salary'] : 0;
    if ($category !== '') {
        $categories[$category] = ($categories[$category] ?? 0) + 1;
    }
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
    foreach ($salaryRanges as $key => $label) {
        if ($key === '20000000+') {
            if ($maxSalary >= 20000000 || $minSalary >= 20000000) {
                $salaryCounts[$key] = ($salaryCounts[$key] ?? 0) + 1;
            }
            continue;
        }
        [$rMin, $rMax] = array_pad(explode('-', $key), 2, null);
        $rMin = (int)$rMin;
        $rMax = (int)$rMax;
        if ($minSalary <= $rMax && $maxSalary >= $rMin) {
            $salaryCounts[$key] = ($salaryCounts[$key] ?? 0) + 1;
        }
    }
}
foreach ($categoryOptions as $category) {
    $categories[$category] = $categories[$category] ?? 0;
}
foreach ($cityOptions as $city) {
    $localities[$city] = $localities[$city] ?? 0;
}
foreach ($industryOptions as $industry) {
    $industries[$industry] = $industries[$industry] ?? 0;
}
foreach (array_keys($salaryRanges) as $key) {
    $salaryCounts[$key] = $salaryCounts[$key] ?? 0;
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
    <section class="sections">
        <div class="container content-above-decorator">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-heading">
                    Why Join Us
                </h2>
                <div class="section-divider"></div>
               
                <p class="mt-1" style="color: #444; font-size: 1.1rem">
                    Your career deserves more than routine mandates. At James Douglas, you’ll work on high-stakes
                    leadership searches, learn from industry experts, and grow in an environment that rewards ambition
                    and sharp thinking.
                </p>
                <p class="mt-4" style="color: #444; font-size: 1.1rem">
                    We are creating a firm for those who believe advisory is more than a career — it is a calling. At
                    James Douglas, your work shapes leaders, organizations, and industries.
                </p>
            </div>
            <div class="row g-4 justify-content-center">

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
                                <img src="../assets/images/icons/Commitment.svg" alt="" height="42px;" width="42px;"
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

    <!-- Work With Us start here -->
     <section class="py-5 bg-white" id="apply">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-11 col-xl-11">
                    <div class="contact-form-container">
                        <div class="text-center mb-5">
                            <h2 class="section-heading">Work With Us</h2>
                            <div class="section-divider"></div>
                            <p class="mt-4 text-muted" style="font-size: 1.1rem;">
                                We're always looking for talented individuals to join our growing team. If you're
                                passionate about
                                connecting great leaders with exceptional opportunities, we'd love to hear from you.
                            </p>
                        </div>

                        <!-- 5-field candidate form -->
                        <form class="contact-form" id="applyForm">
                            <div class="row g-4">
                                <!-- Full Name -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="applicantName" class="form-label">Full Name</label>
                                        <input type="text" class="form-control contact-input" id="applicantName"
                                            placeholder="Enter your name" required>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="applicantEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control contact-input" id="applicantEmail"
                                            placeholder="you@example.com" required>
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="applicantPhone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control contact-input" id="applicantPhone"
                                            placeholder="+91 98XXXXXX" required>
                                    </div>
                                </div>

                                <!-- Role Applying For (prefilled from buttons) -->
                                <!-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="applicantRole" class="form-label">Appling for</label>
                                        <select class="form-select contact-input" id="applicantRole" required>
                                            <option value="" selected disabled>Select role</option>
                                            <option>Senior Executive Search Consultant</option>
                                            <option>Research Associate</option>
                                            <option>Business Development Manager</option>
                                        </select>
                                    </div>
                                </div> -->

                                <!-- LinkedIn / Resume URL -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="applicantLink" class="form-label">LinkedIn Profile</label>
                                        <input type="url" class="form-control contact-input" id="applicantLink"
                                            placeholder="https://www.linkedin.com/in/your-profile" required>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-submit">Submit</button>
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

    <!-- internal job section start -->
    <section class="open-roles-section" id="internal-jobs-section">
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
                            $department = $stringify($job['department'] ?? ($job['team'] ?? ''));
                            $posted = $stringify($job['updated_at'] ?? $job['created_at'] ?? '');
                            $minExp = $stringify($job['minimum_experience'] ?? $job['min_experience'] ?? $job['min_exp'] ?? '');
                            $maxExp = $stringify($job['maximum_experience'] ?? $job['max_experience'] ?? $job['max_exp'] ?? '');
                            $status = $stringify($job['job_status']['label'] ?? $job['job_status'] ?? $job['status'] ?? '');
                            $city = $stringify($job['city'] ?? '');
                            $address = $stringify($job['address'] ?? $job['address_line'] ?? $job['location_address'] ?? '');
                            $salary = $stringify($job['salary'] ?? $job['salary_range'] ?? $job['salary_expectation'] ?? $job['compensation'] ?? '');
                            if ($salary === '') {
                                $minSalary = is_numeric($job['min_annual_salary'] ?? null) ? (int)$job['min_annual_salary'] : 0;
                                $maxSalary = is_numeric($job['max_annual_salary'] ?? null) ? (int)$job['max_annual_salary'] : 0;
                                if ($minSalary || $maxSalary) {
                                    $formatSalary = function (int $value): string {
                                        if ($value >= 10000000) {
                                            return round($value / 10000000, 2) . ' Cr';
                                        }
                                        if ($value >= 100000) {
                                            return round($value / 100000, 2) . ' LPA';
                                        }
                                        if ($value >= 1000) {
                                            return round($value / 1000, 2) . ' K';
                                        }
                                        return (string)$value;
                                    };
                                    $salary = trim($formatSalary($minSalary) . ' - ' . $formatSalary($maxSalary));
                                }
                            }
                            $expRange = '';
                            if ($minExp !== '' || $maxExp !== '') {
                                $expRange = trim($minExp . ' to ' . $maxExp . ' Years');
                            }
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
                                            <div class="open-role-title mb-0open-role-info-label"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="open-role-subtitle">
                                                <?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if ($location || $city): ?>
                                                    • <?php echo htmlspecialchars($location ?: $city, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="open-role-info">
                                    <div>
                                        <div class="open-role-info-label">Experience</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($expRange ?: 'Not specified', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Job Type</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Salary</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($salary ?: 'Not disclosed', ENT_QUOTES, 'UTF-8'); ?></div>
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
                    Leadership Roles With Our Partners
                </h2>
                <div class="section-divider"></div>
                <p class="mt-3 text-muted">
                 Global Talent Acquisition Opportunities
                </p>
            </div>
            <div class="jobs-filter-form-wrapper">
                <form id="job-search-form">
                    <div class="jobs-filter-top">
                        <div class="jobs-filter-input">
                            <i class="bi bi-search"></i>
                            <input type="search" id="job-search-input" placeholder="Job title or keyword" name="q" value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <button type="button" class="jobs-filter-btn jobs-filter-btn-clear" id="job-search-clear">Clear</button>
                        <button type="button" class="jobs-filter-btn jobs-filter-btn-search" id="job-search-submit">Search</button>
                    </div>
                    <div class="jobs-filter-dropdowns">
                        <div class="jobs-filter-group">
                            <span class="jobs-filter-label">Job Category</span>
                            <div class="jobs-filter-select">
                            <select id="job-category-select" name="job_category">
                                <option value="">All</option>
                                <?php foreach ($categoryOptions as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                                <path d="M10.5 1.25L6 5.75L1.5 1.25" stroke="#0B3041" stroke-width="1.4" stroke-linecap="round" />
                            </svg>
                            </div>
                        </div>
                        <div class="jobs-filter-group">
                            <span class="jobs-filter-label">City</span>
                            <div class="jobs-filter-select">
                            <select id="job-city-select" name="city">
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
                            <span class="jobs-filter-label">Salary Range</span>
                            <div class="jobs-filter-select">
                            <select id="job-salary-select" name="salary_range">
                                <option value="">All</option>
                                <?php foreach ($salaryRanges as $key => $label): ?>
                                    <option value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $salaryFilter === $key ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
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
                        <?php foreach ($jobs as $index => $job): ?>
                            <?php
                            $title = $stringify($job['name'] ?? $job['title'] ?? 'Open Position');
                            $company = $stringify($job['company'] ?? $job['client'] ?? 'James Douglas Global');
                            $location = $stringify($job['location'] ?? ($job['city'] ?? ''));
                            $type = $stringify($job['job_type'] ?? ($job['type'] ?? 'Full time'));
                            $department = $stringify($job['department'] ?? ($job['team'] ?? ''));
                            $posted = $stringify($job['updated_at'] ?? $job['created_at'] ?? '');
                            $minExp = $stringify($job['minimum_experience'] ?? $job['min_experience'] ?? $job['min_exp'] ?? '');
                            $maxExp = $stringify($job['maximum_experience'] ?? $job['max_experience'] ?? $job['max_exp'] ?? '');
                            $status = $stringify($job['job_status']['label'] ?? $job['job_status'] ?? $job['status'] ?? '');
                            $city = $stringify($job['city'] ?? '');
                            $address = $stringify($job['address'] ?? $job['address_line'] ?? $job['location_address'] ?? '');
                            $salary = $stringify($job['salary'] ?? $job['salary_range'] ?? $job['salary_expectation'] ?? $job['compensation'] ?? '');
                            if ($salary === '') {
                                $minSalary = is_numeric($job['min_annual_salary'] ?? null) ? (int)$job['min_annual_salary'] : 0;
                                $maxSalary = is_numeric($job['max_annual_salary'] ?? null) ? (int)$job['max_annual_salary'] : 0;
                                if ($minSalary || $maxSalary) {
                                    $formatSalary = function (int $value): string {
                                        if ($value >= 10000000) {
                                            return round($value / 10000000, 2) . ' Cr';
                                        }
                                        if ($value >= 100000) {
                                            return round($value / 100000, 2) . ' LPA';
                                        }
                                        if ($value >= 1000) {
                                            return round($value / 1000, 2) . ' K';
                                        }
                                        return (string)$value;
                                    };
                                    $salary = trim($formatSalary($minSalary) . ' - ' . $formatSalary($maxSalary));
                                }
                            }
                            $expRange = '';
                            if ($minExp !== '' || $maxExp !== '') {
                                $expRange = trim($minExp . ' to ' . $maxExp . ' Years');
                            }
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
                                            <div class="open-role-title mb-0open-role-info-label"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="open-role-subtitle">
                                                <?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if ($location || $city): ?>
                                                    • <?php echo htmlspecialchars($location ?: $city, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="open-role-info">
                                    <div>
                                        <div class="open-role-info-label">Experience</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($expRange ?: 'Not specified', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Job Type</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div>
                                        <div class="open-role-info-label">Salary</div>
                                        <div class="open-role-info-value"><?php echo htmlspecialchars($salary ?: 'Not disclosed', ENT_QUOTES, 'UTF-8'); ?></div>
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
        const categorySelect = document.getElementById('job-category-select');
        const citySelect = document.getElementById('job-city-select');
        const industrySelect = document.getElementById('job-industry-select');
        const salarySelect = document.getElementById('job-salary-select');

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
                if (categorySelect) categorySelect.value = '';
                if (citySelect) citySelect.value = '';
                if (industrySelect) industrySelect.value = '';
                if (salarySelect) salarySelect.value = '';
                applyFilters(1);
            });
        }

        [categorySelect, citySelect, industrySelect, salarySelect].forEach((select) => {
            if (!select) return;
            select.addEventListener('change', () => {
                applyFilters(1);
            });
        });

        async function fetchFilteredJobs(queryString) {
            const url = `filter-jobs.php${queryString ? `?${queryString}` : ''}`;
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (!response.ok || data.error) {
                    return;
                }
                renderJobList(data.jobs || []);
                renderPagination(data.pagination || null);
            } catch (error) {
                console.error(error);
            }
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
                const location = job.location || job.city || '';
                const city = job.city || '';
                const minExp = job.minimum_experience ?? job.min_experience ?? '';
                const maxExp = job.maximum_experience ?? job.max_experience ?? '';
                const expRange = minExp !== '' || maxExp !== '' ? `${minExp} to ${maxExp} Years` : 'Not specified';
                const minSalary = job.min_annual_salary ?? '';
                const maxSalary = job.max_annual_salary ?? '';
                const formatSalary = (value) => {
                    if (!value) return '';
                    if (value >= 10000000) return `${(value / 10000000).toFixed(2)} Cr`;
                    if (value >= 100000) return `${(value / 100000).toFixed(2)} LPA`;
                    if (value >= 1000) return `${(value / 1000).toFixed(2)} K`;
                    return value.toString();
                };
                const salary = minSalary || maxSalary ? `${formatSalary(minSalary)} - ${formatSalary(maxSalary)}` : 'Not disclosed';
                const jobId = job.id ?? job.job_id ?? '';
                const jobSlug = job.slug ?? job.job_slug ?? '';
                const jobUrl = job.apply_link || job.url || '../career.php';

                const card = document.createElement('a');
                card.className = `open-role-list-card ${index === 0 ? 'active' : ''} mb-3`;
                card.href = `job-details.php?slug=${encodeURIComponent(jobSlug)}`;
                card.dataset.jobId = jobId;
                card.dataset.jobSlug = jobSlug;
                card.dataset.title = title;
                card.dataset.company = company;
                card.dataset.location = location;
                card.dataset.type = job.job_type || job.type || 'Full time';
                card.dataset.description = (job.short_description || '').toString();
                card.dataset.applyUrl = jobUrl;

                card.innerHTML = `
                    <div class="open-role-header">
                        <div class="open-role-brand">
                            <div class="open-role-logo rounded-circle m-0">${(company || 'JD').charAt(0)}</div>
                            <div>
                                <div class="open-role-title mb-0open-role-info-label">${title}</div>
                                <div class="open-role-subtitle">${company}${location || city ? ` • ${location || city}` : ''}</div>
                            </div>
                        </div>
                    </div>
                    <div class="open-role-info">
                        <div>
                            <div class="open-role-info-label">Experience</div>
                            <div class="open-role-info-value">${expRange}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">Job Type</div>
                            <div class="open-role-info-value">${job.job_type || job.type || 'Full time'}</div>
                        </div>
                        <div>
                            <div class="open-role-info-label">Salary</div>
                            <div class="open-role-info-value">${salary}</div>
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
                job_category: [],
                city: [],
                job_industry: [],
                salary_range: [],
            };

            if (categorySelect && categorySelect.value) {
                active.job_category = [categorySelect.value];
            }
            if (citySelect && citySelect.value) {
                active.city = [citySelect.value];
            }
            if (industrySelect && industrySelect.value) {
                active.job_industry = [industrySelect.value];
            }
            if (salarySelect && salarySelect.value) {
                active.salary_range = [salarySelect.value];
            }

            return active;
        }

        function buildFilterQuery(filters, page = 1) {
            const params = new URLSearchParams();
            if (filters.q) {
                params.set('q', filters.q);
            }
            if (filters.job_category.length) {
                params.set('job_category', filters.job_category.join(','));
            }
            if (filters.city.length) {
                params.set('city', filters.city.join(','));
            }
            if (filters.job_industry.length) {
                params.set('job_industry', filters.job_industry.join(','));
            }
            if (filters.salary_range.length) {
                params.set('salary_range', filters.salary_range.join(','));
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
            if (categorySelect) {
                categorySelect.value = params.get('job_category') ? params.get('job_category').split(',')[0] : '';
            }
            if (citySelect) {
                const cityValue = params.get('city') || '';
                citySelect.value = cityValue ? cityValue.split(',')[0] : '';
            }
            if (industrySelect) {
                industrySelect.value = params.get('job_industry') ? params.get('job_industry').split(',')[0] : '';
            }
            if (salarySelect) {
                salarySelect.value = params.get('salary_range') ? params.get('salary_range').split(',')[0] : '';
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
 
