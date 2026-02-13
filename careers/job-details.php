<?php
declare(strict_types=1);

$jobSlug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$errorMessage = '';
$job = [];
$similarJobs = [];

$apiToken = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';

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
$company = $stringify($job['company'] ?? $job['client'] ?? 'James Douglas Global');
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

                            <div class="job-detail-tags">
                                <span class="job-detail-tag"><?php echo htmlspecialchars($type ?: 'Full time', ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="job-detail-tag"><?php echo htmlspecialchars($expRange, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="job-detail-tag"><?php echo htmlspecialchars($salary, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <div class="job-detail-section">
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

                            <?php if ($note): ?>
                                <div class="job-detail-section">
                                    <h5>Note for candidates</h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($skills): ?>
                                <div class="job-detail-section">
                                    <h5>Skills</h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($skills, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($category || $address): ?>
                                <div class="job-detail-section">
                                    <h5>Location</h5>
                                    <p class="text-muted">
                                        <?php echo htmlspecialchars($address ?: $location, ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                    <?php if ($category): ?>
                                        <p class="text-muted mb-0">Category: <?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
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
                                $sideCompany = $stringify($item['company'] ?? $item['client'] ?? 'James Douglas Global');
                                $sideLocation = $stringify($item['location'] ?? ($item['city'] ?? ''));
                                $sideType = $stringify($item['job_type'] ?? ($item['type'] ?? 'Full time'));
                                $sideMin = is_numeric($item['min_annual_salary'] ?? null) ? (int)$item['min_annual_salary'] : 0;
                                $sideMax = is_numeric($item['max_annual_salary'] ?? null) ? (int)$item['max_annual_salary'] : 0;
                                $sideSalary = ($sideMin || $sideMax) ? trim($formatSalary($sideMin) . ' - ' . $formatSalary($sideMax)) : 'Not disclosed';
                                $sideSlug = $item['slug'] ?? $item['job_slug'] ?? '';
                                ?>
                                <a class="job-detail-sidecard" href="job-details.php?slug=<?php echo htmlspecialchars((string)$sideSlug, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="job-detail-sidecard-title"><?php echo htmlspecialchars($sideTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="job-detail-sidecard-meta"><?php echo htmlspecialchars($sideCompany, ENT_QUOTES, 'UTF-8'); ?><?php echo $sideLocation ? ' • ' . htmlspecialchars($sideLocation, ENT_QUOTES, 'UTF-8') : ''; ?></div>
                                    <div class="job-detail-sidecard-tags">
                                        <span><?php echo htmlspecialchars($sideType, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span><?php echo htmlspecialchars($sideSalary, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
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
                        <input type="hidden" name="job_slug" id="apply-job-slug" value="<?php echo htmlspecialchars($applySlug, ENT_QUOTES, 'UTF-8'); ?>">
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

        if (applyButton && applyModal) {
            applyButton.addEventListener('click', () => {
                applyStatus.textContent = '';
                applyModal.show();
            });
        }

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
    </script>
</body>
</html>
