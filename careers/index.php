<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Open Roles | James Douglas</title>
  <link rel="icon" type="image/x-icon" href="../images/icons/favicon-jdg.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/open-roles.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

  <?php include '../inc/gtm-head-code.php'; ?>
</head>

<body>
  <?php include '../inc/gtm-body-code.php'; ?>
  <?php include '../inc/navbar2.php'; ?>

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

  <section class="open-roles-section">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-heading">Leadership Roles With Us</h2>
        <div class="section-divider"></div>
        <p class="mt-3 text-muted">Global Talent Acquisition Opportunities</p>
      </div>

      <div class="jobs-filter-form-wrapper">
        <form id="job-search-form">
          <div class="jobs-filter-top">
            <div class="jobs-filter-input">
              <i class="bi bi-search"></i>
              <input type="search" id="job-search-input" placeholder="Search by Job Title" name="q">
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
                  <option value="Agra">Agra</option>
                  <option value="Ahmedabad">Ahmedabad</option>
                  <option value="Ajmer">Ajmer</option>
                  <option value="Aligarh">Aligarh</option>
                  <option value="Amritsar">Amritsar</option>
                  <option value="Aurangabad">Aurangabad</option>
                  <option value="Bengaluru">Bengaluru</option>
                  <option value="Bhopal">Bhopal</option>
                  <option value="Bhubaneswar">Bhubaneswar</option>
                  <option value="Chandigarh">Chandigarh</option>
                  <option value="Chennai">Chennai</option>
                  <option value="Coimbatore">Coimbatore</option>
                  <option value="Dehradun">Dehradun</option>
                  <option value="Delhi">Delhi</option>
                  <option value="Dhanbad">Dhanbad</option>
                  <option value="Faridabad">Faridabad</option>
                  <option value="Ghaziabad">Ghaziabad</option>
                  <option value="Goa">Goa</option>
                  <option value="Gurgaon">Gurgaon</option>
                  <option value="Guwahati">Guwahati</option>
                  <option value="Gwalior">Gwalior</option>
                  <option value="Hyderabad">Hyderabad</option>
                  <option value="Indore">Indore</option>
                  <option value="Jaipur">Jaipur</option>
                  <option value="Jamshedpur">Jamshedpur</option>
                  <option value="Jodhpur">Jodhpur</option>
                  <option value="Kanpur">Kanpur</option>
                  <option value="Kochi">Kochi</option>
                  <option value="Kolkata">Kolkata</option>
                  <option value="Lucknow">Lucknow</option>
                  <option value="Ludhiana">Ludhiana</option>
                  <option value="Madurai">Madurai</option>
                  <option value="Mangalore">Mangalore</option>
                  <option value="Meerut">Meerut</option>
                  <option value="Mumbai">Mumbai</option>
                  <option value="Mysuru">Mysuru</option>
                  <option value="Nagpur">Nagpur</option>
                  <option value="Nashik">Nashik</option>
                  <option value="Noida">Noida</option>
                  <option value="Patna">Patna</option>
                  <option value="Pimpri-Chinchwad">Pimpri-Chinchwad</option>
                  <option value="Pune">Pune</option>
                  <option value="Raipur">Raipur</option>
                  <option value="Rajkot">Rajkot</option>
                  <option value="Ranchi">Ranchi</option>
                  <option value="Surat">Surat</option>
                  <option value="Thane">Thane</option>
                  <option value="Tiruchirappalli">Tiruchirappalli</option>
                  <option value="Udaipur">Udaipur</option>
                  <option value="Vadodara">Vadodara</option>
                  <option value="Varanasi">Varanasi</option>
                  <option value="Vijayawada">Vijayawada</option>
                  <option value="Visakhapatnam">Visakhapatnam</option>
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
                </select>
                <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                  <path d="M10.5 1.25L6 5.75L1.5 1.25" stroke="#0B3041" stroke-width="1.4" stroke-linecap="round" />
                </svg>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="open-roles-grid">
        <div id="job-list">
          <div class="alert alert-light shadow-sm" role="alert">Loading open roles...</div>
        </div>
        <div id="jobs-pagination" class="jobs-pagination"></div>
      </div>
    </div>
  </section>

  <?php include '../inc/footer2.php'; ?>

  <script src="../assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>

  <script>
    const API_URL = 'https://api.recruitcrm.io/v1/jobs';
    const API_TOKEN = 'Bearer 2k5UW8wswGNHr7zRCWuvP0F7t8wpLFPxJxLfegndOi6PAYs4cXtCfLbVbZg5v8YiGWlAY_F8m-UlRJrWOE9aCV8xNzY5MTYxNjIyOnw6cHJvZHVjdGlvbg==';
    const API_FETCH_LIMIT = 50;
    const PAGE_SIZE = 9;
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

    const searchForm = document.getElementById('job-search-form');
    const searchInput = document.getElementById('job-search-input');
    const clearButton = document.getElementById('job-search-clear');
    const submitButton = document.getElementById('job-search-submit');
    const locationSelect = document.getElementById('job-location-select');
    const industrySelect = document.getElementById('job-industry-select');
    const functionSelect = document.getElementById('job-function-select');
    const subFunctionSelect = document.getElementById('job-sub-function-select');
    const jobsContainer = document.getElementById('job-list');

    let externalJobsCache = null;

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function stringify(value) {
      if (Array.isArray(value)) {
        return value.filter((item) => item !== null && item !== undefined && String(item).trim() !== '').join(', ');
      }
      if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
      }
      return value === null || value === undefined ? '' : String(value);
    }

    function getHiringForValue(job) {
      const fields = Array.isArray(job?.custom_fields) ? job.custom_fields : [];
      for (const field of fields) {
        if (!field || typeof field !== 'object') {
          continue;
        }
        const fieldId = Number(field.field_id ?? field.id ?? 0);
        const entityType = String(field.entity_type ?? '').trim().toLowerCase();
        const fieldName = String(field.field_name ?? field.name ?? '').trim().toLowerCase();
        const fieldType = String(field.field_type ?? field.type ?? '').trim().toLowerCase();
        if (fieldId !== 7 || entityType !== 'job' || fieldName !== 'hiring for' || fieldType !== 'dropdown') {
          continue;
        }

        let value = field.value ?? '';
        if (value && typeof value === 'object') {
          return '';
        }
        value = String(value ?? '').trim();
        return value;
      }
      return '';
    }

    function isExternalClientJob(job) {
      return getHiringForValue(job) === 'Client (External)';
    }

    function isConfidentialJob(job) {
      return getHiringForValue(job).toLowerCase() === 'do not post (confidential)';
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

    async function fetchPage(url) {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          Authorization: API_TOKEN,
        },
      });

      if (!response.ok) {
        throw new Error('Unable to load roles');
      }

      const data = await response.json();
      const jobs = data.data ?? data.jobs ?? (Array.isArray(data) ? data : []);
      const nextPageUrl = data.next_page_url ?? null;

      return { jobs, nextPageUrl };
    }

    async function fetchAllExternalClientJobs(limit = API_FETCH_LIMIT, maxPages = 100) {
      let allJobs = [];
      let nextUrl = `${API_URL}?limit=${limit}`;
      let pageCount = 0;

      while (nextUrl && pageCount < maxPages) {
        pageCount += 1;
        const { jobs, nextPageUrl } = await fetchPage(nextUrl);
        allJobs = allJobs.concat(jobs);
        nextUrl = nextPageUrl;
      }

      return allJobs.filter((job) => !isConfidentialJob(job) && isExternalClientJob(job));
    }

    async function getExternalJobs() {
      if (!externalJobsCache) {
        externalJobsCache = await fetchAllExternalClientJobs();
      }
      return externalJobsCache;
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

    function populateIndustryOptions(jobs, selectedIndustry) {
      populateOptions(industrySelect, INDUSTRY_OPTIONS, selectedIndustry);
    }

    function populateFunctionOptions(jobs, selectedFunction) {
      populateOptions(functionSelect, FUNCTION_OPTIONS, selectedFunction);
    }

    function populateSubFunctionOptions(jobs, selectedSubFunction) {
      populateOptions(subFunctionSelect, jobs.map((job) => getSubFunctionValue(job)), selectedSubFunction);
    }

    function applyClientFilters(jobs, params) {
      const q = String(params.get('q') || '').trim().toLowerCase();
      const city = String(params.get('city') || '').trim().toLowerCase();
      const industry = String(params.get('job_industry') || '').trim().toLowerCase();
      const jobFunction = String(params.get('job_function') || '').trim().toLowerCase();
      const subFunction = String(params.get('job_category') || '').trim().toLowerCase();

      return jobs.filter((job) => {
        const title = stringify(job?.name ?? job?.title ?? '').toLowerCase();
        const jobCity = stringify(job?.city ?? '').toLowerCase();
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

    function formatSalary(value) {
      const numeric = Number(value || 0);
      if (!numeric) return '';
      if (numeric >= 10000000) return `${(numeric / 10000000).toFixed(2)} Cr`;
      if (numeric >= 100000) return `${(numeric / 100000).toFixed(2)} LPA`;
      if (numeric >= 1000) return `${(numeric / 1000).toFixed(2)} K`;
      return String(numeric);
    }

    function renderJobList(jobs) {
      jobsContainer.innerHTML = '';

      if (!jobs.length) {
        jobsContainer.innerHTML = '<div class="alert alert-light shadow-sm" role="alert">No open roles are available right now. Please check back soon.</div>';
        return;
      }

      jobs.forEach((job, index) => {
        const title = stringify(job?.name ?? job?.title ?? 'Open Position');
        const company = stringify(job?.company ?? job?.company_name ?? job?.client ?? 'James Douglas Global');
        const city = stringify(job?.city ?? '');
        const minExp = stringify(job?.minimum_experience ?? job?.min_experience ?? job?.min_exp ?? '');
        const industry = getIndustryValue(job) || 'Not specified';
        const jobFunction = getFunctionValue(job) || 'Not specified';
        const subFunction = getSubFunctionValue(job) || 'Not specified';
        const qualification = getQualificationValue(job) || 'Not specified';
        const noteForCandidates = stringify(job?.note_for_candidates ?? '').trim() || 'Not specified';
        const jobSlug = String(job?.slug ?? job?.job_slug ?? '');

        const card = document.createElement('a');
        card.className = `open-role-list-card ${index === 0 ? 'active' : ''} mb-3`;
        card.href = `job-details.php?slug=${encodeURIComponent(jobSlug)}`;
        card.innerHTML = `
          <div class="open-role-header">
            <div class="open-role-brand">
              <div class="open-role-logo rounded-circle m-0">${escapeHtml((company || 'JD').charAt(0))}</div>
              <div>
                <div class="open-role-title">${escapeHtml(title)}</div>
                <div class="open-role-subtitle">${escapeHtml(company)}</div>
              </div>
            </div>
          </div>
          <div class="open-role-info">
            <div>
              <div class="open-role-info-label">Location</div>
              <div class="open-role-info-value">${escapeHtml(city || 'Not specified')}</div>
            </div>
            <div>
              <div class="open-role-info-label">Industry</div>
              <div class="open-role-info-value">${escapeHtml(industry)}</div>
            </div>
            <div>
              <div class="open-role-info-label">Function</div>
              <div class="open-role-info-value">${escapeHtml(jobFunction)}</div>
            </div>
            <div>
              <div class="open-role-info-label">Sub Function</div>
              <div class="open-role-info-value">${escapeHtml(subFunction)}</div>
            </div>
            <div>
              <div class="open-role-info-label">Educational Qualification</div>
              <div class="open-role-info-value">${escapeHtml(qualification)}</div>
            </div>
            <div>
              <div class="open-role-info-label">YOE</div>
              <div class="open-role-info-value">${escapeHtml(minExp ? `${minExp} Years` : 'Not specified')}</div>
            </div>
            <div class="open-role-info-note">
              <div class="open-role-info-label">Note for Candidates</div>
              <div class="open-role-info-value">${escapeHtml(noteForCandidates)}</div>
            </div>
          </div>
        `;
        jobsContainer.appendChild(card);
      });
    }

    function renderPagination(pagination) {
      const container = document.getElementById('jobs-pagination');
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
        html += '<button class="pagination-btn" data-page="1">1</button>';
        if (start > 2) {
          html += '<span class="pagination-ellipsis">...</span>';
        }
      }

      for (let i = start; i <= end; i += 1) {
        html += `<button class="pagination-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
      }

      if (end < last) {
        if (end < last - 1) {
          html += '<span class="pagination-ellipsis">...</span>';
        }
        html += `<button class="pagination-btn" data-page="${last}">${last}</button>`;
      }

      html += `<button class="pagination-btn" data-page="${Math.min(last, current + 1)}" ${current === last ? 'disabled' : ''}>Next</button>`;
      container.innerHTML = html;

      container.querySelectorAll('.pagination-btn').forEach((button) => {
        button.addEventListener('click', () => {
          const nextPage = parseInt(button.dataset.page || '1', 10);
          if (Number.isNaN(nextPage) || nextPage === current) {
            return;
          }
          applyFilters(nextPage);
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      });
    }

    function getActiveFilters() {
      const filters = {
        q: searchInput ? searchInput.value.trim() : '',
        city: [],
        job_industry: [],
        job_function: [],
        job_category: [],
      };

      if (locationSelect && locationSelect.value) {
        filters.city = [locationSelect.value];
      }
      if (industrySelect && industrySelect.value) {
        filters.job_industry = [industrySelect.value];
      }
      if (functionSelect && functionSelect.value) {
        filters.job_function = [functionSelect.value];
      }
      if (subFunctionSelect && subFunctionSelect.value) {
        filters.job_category = [subFunctionSelect.value];
      }

      return filters;
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

    function setActiveFiltersFromParams(params) {
      if (searchInput) {
        searchInput.value = params.get('q') || '';
      }
      if (locationSelect) {
        const value = params.get('city') || '';
        locationSelect.value = value ? value.split(',')[0] : '';
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
        populateIndustryOptions(allExternalJobs, selectedIndustry);
        populateFunctionOptions(allExternalJobs, selectedFunction);
        populateSubFunctionOptions(allExternalJobs, selectedSubFunction);
        if (selectedIndustry && industrySelect.value !== selectedIndustry) {
          industrySelect.value = '';
        }
        if (selectedFunction && functionSelect.value !== selectedFunction) {
          functionSelect.value = '';
        }
        if (selectedSubFunction && subFunctionSelect.value !== selectedSubFunction) {
          subFunctionSelect.value = '';
        }

        const filteredJobs = applyClientFilters(allExternalJobs, params);
        const filtedJobData = filteredJobs;
        console.log('filtedJobData', filtedJobData);
        const total = filteredJobs.length;
        const lastPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
        const currentPage = Math.min(requestedPage, lastPage);
        const offset = (currentPage - 1) * PAGE_SIZE;
        const pageJobs = filteredJobs.slice(offset, offset + PAGE_SIZE);

        renderJobList(pageJobs);
        renderPagination({
          current_page: currentPage,
          per_page: PAGE_SIZE,
          total,
          last_page: lastPage,
        });
      } catch (error) {
        jobsContainer.innerHTML = '<div class="alert alert-light shadow-sm" role="alert">Unable to load open roles right now. Please try again later.</div>';
        document.getElementById('jobs-pagination').innerHTML = '';
      }
    }

    function applyFilters(page = 1) {
      const filters = getActiveFilters();
      const queryString = buildFilterQuery(filters, page);
      const hash = window.location.hash || '';
      const newUrl = `${window.location.pathname}${queryString ? `?${queryString}` : ''}${hash}`;
      window.history.replaceState({}, '', newUrl);
      fetchFilteredJobs(queryString);
    }

    if (searchForm) {
      searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
      });
    }

    if (searchInput) {
      let searchTimer = null;
      searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => applyFilters(1), 250);
      });
    }

    if (submitButton) {
      submitButton.addEventListener('click', () => applyFilters(1));
    }

      if (clearButton) {
      clearButton.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (locationSelect) locationSelect.value = '';
        if (industrySelect) industrySelect.value = '';
        if (functionSelect) functionSelect.value = '';
        if (subFunctionSelect) subFunctionSelect.value = '';
        applyFilters(1);
      });
    }

    [locationSelect, industrySelect, functionSelect, subFunctionSelect].forEach((select) => {
      if (!select) return;
      select.addEventListener('change', () => applyFilters(1));
    });

    const initialParams = new URLSearchParams(window.location.search);
    if (initialParams.has('locality') && !initialParams.has('city')) {
      initialParams.set('city', initialParams.get('locality') || '');
      initialParams.delete('locality');
      const normalizedQuery = initialParams.toString();
      window.history.replaceState({}, '', `${window.location.pathname}${normalizedQuery ? `?${normalizedQuery}` : ''}`);
    }
    setActiveFiltersFromParams(initialParams);
    const initialPage = parseInt(initialParams.get('page') || '1', 10) || 1;
    applyFilters(initialPage);
  </script>
</body>

</html>
