<?php
declare(strict_types=1);

$jobSlug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$errorMessage = '';
$job = [];
$similarJobs = [];

$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

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

if ($jobSlug === '') {
    $errorMessage = 'Missing job slug.';
} else {
    $apiUrl = 'https://api.recruitcrm.io/v1/jobs/' . rawurlencode($jobSlug);
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

    if ($curlError) {
        $errorMessage = 'We could not load this job right now. Please try again later.';
    } else {
        $data = json_decode($response ?: '', true);
        if ($statusCode >= 200 && $statusCode < 300 && is_array($data)) {
            $job = $data;
        } else {
            $errorMessage = 'Job not found.';
        }
    }
}

if (!$errorMessage) {
    $listUrl = 'https://api.recruitcrm.io/v1/jobs';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $listUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: ' . $apiToken,
        ],
    ]);
    $listResponse = curl_exec($ch);
    $listError = curl_error($ch);
    $listStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$listError && $listStatus >= 200 && $listStatus < 300) {
        $listData = json_decode($listResponse ?: '', true);
        $listJobs = [];
        if (isset($listData['data']) && is_array($listData['data'])) {
            $listJobs = $listData['data'];
        } elseif (isset($listData[0])) {
            $listJobs = $listData;
        }
        foreach ($listJobs as $item) {
            $slug = $item['slug'] ?? $item['job_slug'] ?? '';
            if ($slug === '' || $slug === $jobSlug) {
                continue;
            }
            if (is_confidential_job($item)) {
                continue;
            }
            $similarJobs[] = $item;
            if (count($similarJobs) >= 5) {
                break;
            }
        }
    }
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

$title = $stringify($job['name'] ?? $job['title'] ?? 'Open Position');
$company = $stringify($job['company'] ?? $job['client'] ?? 'James Douglas');
$location = $stringify($job['location'] ?? ($job['city'] ?? ''));
$type = $stringify($job['job_type'] ?? ($job['type'] ?? 'Full time'));
$minExp = $stringify($job['minimum_experience'] ?? $job['min_experience'] ?? $job['min_exp'] ?? '');
$maxExp = $stringify($job['maximum_experience'] ?? $job['max_experience'] ?? $job['max_exp'] ?? '');
$expRange = $minExp !== '' || $maxExp !== '' ? trim($minExp . ' to ' . $maxExp . ' Years') : 'Not specified';
$minSalary = is_numeric($job['min_annual_salary'] ?? null) ? (int)$job['min_annual_salary'] : 0;
$maxSalary = is_numeric($job['max_annual_salary'] ?? null) ? (int)$job['max_annual_salary'] : 0;
$salary = ($minSalary || $maxSalary) ? trim($formatSalary($minSalary) . ' - ' . $formatSalary($maxSalary)) : 'Not disclosed';
$description = $stringify($job['description'] ?? $job['short_description'] ?? '');
$descriptionText = $job['job_description_text'] ?? '';
$descriptionFile = '';
if (!empty($job['job_description_file'])) {
    if (is_array($job['job_description_file'])) {
        $descriptionFile = $job['job_description_file']['file_link'] ?? '';
    } else {
        $descriptionFile = (string)$job['job_description_file'];
    }
}
$note = $stringify($job['note_for_candidates'] ?? '');
$skills = $stringify($job['job_skill'] ?? $job['skills'] ?? '');
$category = $stringify($job['job_category'] ?? $job['category'] ?? '');
$address = $stringify($job['address'] ?? $job['address_line'] ?? $job['location_address'] ?? '');
$applySlug = $stringify($job['slug'] ?? $job['job_slug'] ?? $jobSlug);
$candidateIndustryOptions = [
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

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Job Details | James Douglas</title>
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

    <section class="open-roles-section">
        <div class="container">
            <?php if ($errorMessage): ?>
                <div class="alert alert-light shadow-sm" role="alert">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php else: ?>
                <div class="job-detail-layout">
                    <div class="job-detail-main">
                        <div class="job-detail-card">
                            <div class="job-detail-header">
                                <div>
                                    <h2 class="job-detail-title"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <div class="job-detail-meta">
                                        <span class="job-detail-company"><?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($location): ?>
                                            <span>• <?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button class="open-role-apply job-detail-apply" id="job-detail-apply" type="button">Apply Now</button>
                            </div>

                            <div class="job-detail-section pt-3">
                                <h5>About this role</h5>
                                <?php if ($description): ?>
                                    <p class="text-muted"><?php echo htmlspecialchars(strip_tags($description), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                                <?php if ($descriptionText): ?>
                                    <div class="text-muted job-detail-rich">
                                        <?php echo $descriptionText; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($descriptionFile): ?>
                                    <a class="job-detail-file-link" href="<?php echo htmlspecialchars($descriptionFile, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-file-earmark-text"></i>
                                        View job description file
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if ($skills): ?>
                                <div class="job-detail-section">
                                    <h5>Skills</h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($skills, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <aside class="job-detail-sidebar">
                        <h6 class="job-detail-sidebar-title">Similar Jobs</h6>
                        <?php if (!$similarJobs): ?>
                            <div class="job-detail-sidecard">
                                <p class="text-muted mb-0">No similar jobs available.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($similarJobs as $item): ?>
                                <?php
                                $sideTitle = $stringify($item['name'] ?? $item['title'] ?? 'Open Position');
                                $sideCompany = $stringify($item['company'] ?? $item['client'] ?? 'James Douglas');
                                $sideLocation = $stringify($item['location'] ?? ($item['city'] ?? ''));
                                $sideSlug = $item['slug'] ?? $item['job_slug'] ?? '';
                                ?>
                                <a class="job-detail-sidecard" href="job-details.php?slug=<?php echo htmlspecialchars((string)$sideSlug, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="job-detail-sidecard-title"><?php echo htmlspecialchars($sideTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="job-detail-sidecard-meta"><?php echo htmlspecialchars($sideCompany, ENT_QUOTES, 'UTF-8'); ?><?php echo $sideLocation ? ' • ' . htmlspecialchars($sideLocation, ENT_QUOTES, 'UTF-8') : ''; ?></div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </aside>
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
                        <input type="hidden" name="source" value="Website">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email ID</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Current Organization</label>
                                <input type="text" class="form-control" name="current_organization" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Current Designation</label>
                                <input type="text" class="form-control" name="position" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Candidate Industry</label>
                                <select class="form-select" name="specialization" required>
                                    <option value="">Select Industry</option>
                                    <?php foreach ($candidateIndustryOptions as $industryOption): ?>
                                        <option value="<?php echo htmlspecialchars($industryOption, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($industryOption, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Experience</label>
                                <input type="number" class="form-control" name="work_ex_year" min="0" step="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fixed CTC</label>
                                <input type="number" class="form-control" name="current_salary" min="0" step="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Salary Expectation</label>
                                <input type="number" class="form-control" name="salary_expectation" min="0" step="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender_id" required>
                                    <option value="">Select Gender</option>
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notice Period</label>
                                <input type="number" class="form-control" name="notice_period" min="0" step="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <select class="form-select" name="city" required>
                                    <option value="">Select City</option>
                                    <?php foreach ($cityOptions as $cityOption): ?>
                                        <option value="<?php echo htmlspecialchars($cityOption, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($cityOption, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Relevant Experience</label>
                                <input type="number" class="form-control" name="relevant_experience" min="0" step="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Upload Resume</label>
                                <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                            </div>
                        </div>
                        <div class="mt-4 d-flex align-items-center gap-3">
                            <button type="submit" class="open-role-apply px-4">Submit Application</button>
                            <div id="apply-job-status" class="small text-muted"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../inc/footer2.php'; ?>

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
        const applyModalEl = document.getElementById('applyModal');
        const applyModal = applyModalEl ? new bootstrap.Modal(applyModalEl) : null;
        const applyButton = document.getElementById('job-detail-apply');
        const applyForm = document.getElementById('apply-job-form');
        const applyStatus = document.getElementById('apply-job-status');
        const applySubmitButton = applyForm ? applyForm.querySelector('button[type="submit"]') : null;

        if (applyButton && applyModal) {
            applyButton.addEventListener('click', () => {
                applyStatus.textContent = '';
                applyModal.show();
            });
        }

        if (applyForm) {
            applyForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (applySubmitButton) {
                    applySubmitButton.disabled = true;
                }
                applyStatus.textContent = 'Submitting...';
                applyStatus.classList.remove('text-danger', 'text-success');
                const formData = new FormData(applyForm);
                try {
                    const response = await fetch('https://api.recruitcrm.io/v1/candidates', {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            Authorization: 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==',
                        },
                        body: formData,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && !data.error) {
                        applyStatus.textContent = data.message || 'Application submitted successfully.';
                        applyStatus.classList.remove('text-danger');
                        applyStatus.classList.add('text-success');
                        applyForm.reset();
                        window.setTimeout(() => {
                            if (applyModal) {
                                applyModal.hide();
                            }
                        }, 800);
                    } else {
                        applyStatus.textContent = data.message || 'Failed to submit application.';
                        applyStatus.classList.remove('text-success');
                        applyStatus.classList.add('text-danger');
                    }
                } catch (error) {
                    applyStatus.textContent = 'Failed to submit application.';
                    applyStatus.classList.remove('text-success');
                    applyStatus.classList.add('text-danger');
                } finally {
                    if (applySubmitButton) {
                        applySubmitButton.disabled = false;
                    }
                }
            });
        }
    </script>
</body>
</html>
