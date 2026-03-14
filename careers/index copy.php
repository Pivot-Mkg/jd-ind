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
   <style>
    .job-caption-item-title {
      font-size: 16px;
      margin: 0%;
      padding: 0%;
    } 
    .job-card p {
      font-size: 14px;
      margin: 0%;
      padding: 0%;
    }

    .job-caption-item-details-item {
      display: grid;
    }

    .job-caption-item-details-item strong {
      font-size: 11px;
      font-weight: 400;
    }

    #external-job-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 9px;
      margin: 1em auto!important;
    }

    .open-role-title {
      font-size: 14px;
      font-weight: 700;
      color: #0b2e3c;
    }

    .open-role-info-value {
      font-size: 12px!important;
      font-weight: 600!important;
    }
   </style>
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

  <!-- External job section start  -->
  <section class="external-job-section">
    <div class="container">
      <div class="external-job-list" id="external-job-list">
        <!-- Job cards will be dynamically inserted here -->

      </div>
    </div>
  </section>

  <!-- External job section end -->


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



  <!-- Recruitcrm APIs Script -->
  <script>
    async function getFilteredJobs() {
      try {
        const response = await fetch('https://api.recruitcrm.io/v1/jobs', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg=='
          }
        });

        const result = await response.json();
        console.log("result");
        console.log(result);

        // Filter jobs where "Hiring For" equals "Client (External)"
        const filteredJobs = result.data.filter(job =>
          job.custom_fields.some(field => field.value == "Client (External)")
        );

        console.log("filteredJobs");
        console.log(filteredJobs);
        return filteredJobs;

      } catch (error) {
        console.error('Error:', error);
      }
    }

    getFilteredJobs();



  </script>

  <script>
    function renderJobs(jobs) {
      const container = document.getElementById('external-job-list');

      container.innerHTML = jobs.map(job => `
<a class="open-role-list-card"
   href="job-details.php?slug=${job.slug}"
   data-job-id="${job.id}"
   data-job-slug="${job.slug}"
   data-title="${job.name}"
   data-company="${job.company_name || 'James Douglas Global'}"
   data-location="${job.city}, ${job.country}"
   data-type="${job.job_type}"
   data-description="${job.description || ''}"
   data-apply-url="${job.application_form_url}">

    <div class="open-role-header">
        <div class="open-role-brand">
            <div class="open-role-logo rounded-circle m-0">
                ${(job.company_name || 'J').charAt(0)}
            </div>
            <div>
                <div class="open-role-title mb-0 open-role-info-label">
                    ${job.name}
                </div>
                <div class="open-role-subtitle">
                    ${job.company_name || 'James Douglas Global'} • ${job.city}, ${job.country}
                </div>
            </div>
        </div>
    </div>

    <div class="open-role-info">
        <div>
            <div class="open-role-info-label">Experience</div>
            <div class="open-role-info-value">
                ${job.minimum_experience} to ${job.maximum_experience} Years
            </div>
        </div>

        <div>
            <div class="open-role-info-label">Job Type</div>
            <div class="open-role-info-value">
                ${job.job_type}
            </div>
        </div>

        <div>
            <div class="open-role-info-label">Salary</div>
            <div class="open-role-info-value">
                ₹${(job.min_annual_salary / 100000).toFixed(0)}L - 
                ₹${(job.max_annual_salary / 100000).toFixed(0)}L
            </div>
        </div>
    </div>

</a>
`).join('');
    }

    // Call after filtering
    getFilteredJobs().then(jobs => {
      renderJobs(jobs);
    });

    getFilteredJobs()

  </script>
</body>

</html>