(() => {
  const icon = (name) => `<i class="ph-fill ${name}" aria-hidden="true"></i>`;
  const status = (label, tone) => `<span class="status-chip status-chip--${tone}">${label}</span>`;
  const prefersReducedMotion = () => window.matchMedia?.("(prefers-reduced-motion: reduce)").matches;
  const sparkline = (values, tone) => {
    const width = 112;
    const height = 32;
    const min = Math.min(...values);
    const max = Math.max(...values);
    const range = max - min || 1;
    const points = values.map((value, index) => {
      const x = (index / (values.length - 1)) * width;
      const y = height - 3 - ((value - min) / range) * (height - 8);
      return `${x.toFixed(1)},${y.toFixed(1)}`;
    }).join(" ");
    return `<svg class="kpi-sparkline sparkline-${tone}" viewBox="0 0 ${width} ${height}" aria-hidden="true" focusable="false"><polyline points="${points}" /></svg>`;
  };
  const calendar = (calendarData) => {
    const leadingDays = 3;
    const days = Array.from({ length: 31 }, (_, index) => index + 1);
    return `<div class="mini-calendar"><div class="calendar-weekdays" aria-hidden="true">${["S", "M", "T", "W", "T", "F", "S"].map((day) => `<span>${day}</span>`).join("")}</div><div class="calendar-days">${Array.from({ length: leadingDays }, () => '<span class="calendar-day is-empty" aria-hidden="true"></span>').join("")}${days.map((day) => `<span class="calendar-day${day === calendarData.selectedDay ? " is-selected" : ""}${calendarData.eventDays.includes(day) ? " has-event" : ""}" ${day === calendarData.selectedDay ? 'aria-current="date"' : ""} aria-label="July ${day}, 2026${calendarData.eventDays.includes(day) ? ", sample event" : ""}">${day}</span>`).join("")}</div></div>`;
  };
  const renderOperations = (operations) => `
    <section class="hospital-operations dashboard-section dashboard-chapter" aria-labelledby="hospital-operations-title">
      <div class="operations-heading dashboard-section-header">
        <div><p class="dashboard-kicker">Operations Center</p><h2 id="hospital-operations-title" class="operations-title">Hospital Operations</h2><p class="dashboard-section-desc">Current capacity, throughput, response readiness, and workforce coverage.</p></div>
        <span class="operations-updated"><i class="ph-fill ph-broadcast" aria-hidden="true"></i>${operations.updatedLabel}</span>
      </div>

      <div class="live-status-ribbon" role="region" tabindex="0" aria-label="Real-time hospital service status; scroll horizontally to view all statuses">${operations.liveStatus.map((item) => `<span class="live-status-pill status-${item.tone}"><i aria-hidden="true"></i><strong>${item.label}</strong><small>${item.state}</small></span>`).join("")}</div>

      <div class="operations-metric-grid">${operations.liveMetrics.map((item) => `<article class="operations-metric-card card"><div class="operations-metric-head"><span class="operations-metric-icon ${item.tone}">${icon(item.icon)}</span><span class="status-chip status-chip--${item.tone}">Sample data</span></div><p>${item.label}</p><strong class="operations-metric-value">${item.value}</strong><div class="operations-metric-details">${item.details.map((detail) => `<span><small>${detail.label}</small><strong>${detail.value}</strong></span>`).join("")}</div></article>`).join("")}</div>

      <div class="operations-intelligence-grid operations-center-grid">
        <article class="card operations-widget snapshot-widget"><div class="card-header"><div><p class="widget-eyebrow">Daily Throughput</p><h2>Today's Hospital Snapshot</h2><p class="card-subtitle">Consolidated frontend sample activity.</p></div><span class="widget-count">${operations.snapshot.length}</span></div><div class="snapshot-grid">${operations.snapshot.map((item) => `<div class="snapshot-item"><span>${icon(item.icon)}</span><div><strong>${item.value}</strong><small>${item.label}</small></div></div>`).join("")}</div></article>

        <article class="card operations-widget emergency-widget"><div class="card-header"><div><p class="widget-eyebrow">Response Readiness</p><h2>Emergency Monitor</h2><p class="card-subtitle">Compact sample emergency overview.</p></div><span class="emergency-state"><i aria-hidden="true"></i>Active response</span></div><div class="emergency-monitor-list">${operations.emergencyMonitor.map((item) => `<div class="emergency-monitor-row"><span class="emergency-marker ${item.tone}" aria-hidden="true"></span><strong>${item.label}</strong><b>${item.value}</b></div>`).join("")}</div></article>

        <article class="card operations-widget departments-widget"><div class="card-header"><div><p class="widget-eyebrow">Capacity and Availability</p><h2>Department Status</h2><p class="card-subtitle">Sample workload across core hospital departments.</p></div><span class="widget-count">${operations.departments.length}</span></div><div class="department-grid">${operations.departments.map((item) => `<article class="department-card"><div class="department-head"><span class="department-icon ${item.tone}">${icon(item.icon)}</span><div><h3>${item.name}</h3><p>${item.status}</p></div><span class="department-capacity">${item.capacity}%</span></div><progress max="100" value="${item.capacity}" aria-label="${item.name} sample capacity ${item.capacity} percent"></progress><div class="department-meta"><span>Workload <strong>${item.workload}</strong></span><span>Available <strong>${item.availability}</strong></span></div></article>`).join("")}</div></article>

        <article class="card operations-widget map-widget"><div class="card-header"><div><p class="widget-eyebrow">Future Integration</p><h2>Hospital Floor Overview</h2><p class="card-subtitle">Reserved operational visualization area.</p></div></div><div class="hospital-map-placeholder"><span class="map-placeholder-icon">${icon("ph-map-trifold")}</span><strong>Hospital Floor Map</strong><p>Interactive mapping is not connected in this frontend build.</p><div>${operations.mapIntegrations.map((item) => `<span>${item}</span>`).join("")}</div></div></article>

        <article class="card operations-widget shift-widget"><div class="card-header"><div><p class="widget-eyebrow">Workforce Coverage</p><h2>Shift Overview</h2><p class="card-subtitle">Sample staffing availability by shift.</p></div></div><div class="shift-table-wrap"><table class="shift-table"><thead><tr><th scope="col">Shift</th><th scope="col">Doctors</th><th scope="col">Nurses</th><th scope="col">Support</th><th scope="col">Availability</th></tr></thead><tbody>${operations.shifts.map((item) => `<tr><th scope="row">${item.name}</th><td>${item.doctors}</td><td>${item.nurses}</td><td>${item.support}</td><td><span>${item.availability}</span></td></tr>`).join("")}</tbody></table></div></article>

      </div>
    </section>`;

  const renderInsights = (operations) => `<article class="card operations-widget insights-widget"><div class="card-header"><div><p class="widget-eyebrow">Operational Intelligence</p><h2>AI Operational Insights</h2><p class="card-subtitle">Illustrative observations only—not live AI output.</p></div><i class="ph-fill ph-sparkle insight-heading-icon" aria-hidden="true"></i></div><div class="insight-list">${operations.aiInsights.map((item) => `<div class="insight-item"><span class="insight-icon ${item.tone}">${icon(item.icon)}</span><div><small>Sample AI Insight</small><p>${item.text}</p></div></div>`).join("")}</div></article>`;

  const renderWeather = (operations) => `<article class="card operations-widget weather-widget"><div class="card-header"><div><p class="widget-eyebrow">Local Conditions</p><h2>Weather</h2><p class="card-subtitle">${operations.weather.location}</p></div></div><div class="weather-main"><span class="weather-icon">${icon(operations.weather.icon)}</span><div><strong>${operations.weather.temperature}</strong><span>${operations.weather.condition}</span></div></div><div class="weather-meta"><small>${operations.weather.detail}</small></div></article>`;

  const renderNews = (operations) => `<article class="card operations-widget news-widget"><div class="card-header"><div><p class="widget-eyebrow">Hospital Communications</p><h2>Hospital News</h2><p class="card-subtitle">Frontend sample notices and protocol updates.</p></div><span class="widget-count">${operations.news.length}</span></div><div class="hospital-news-list">${operations.news.map((item) => `<article class="hospital-news-item"><span>${item.type}</span><h3>${item.title}</h3><time>${item.date}</time></article>`).join("")}</div></article>`;

  const animateKpiValue = (card) => {
    const output = card.querySelector(".kpi-value");
    if (!output || output.dataset.counted === "true") return;
    output.dataset.counted = "true";
    const rawValue = output.dataset.value || output.textContent.trim();
    const normalized = rawValue.replace(/,/g, "");
    if (prefersReducedMotion() || !/^-?\d+(?:\.\d+)?$/.test(normalized)) return;

    const target = Number(normalized);
    const startTime = performance.now();
    const duration = 760;
    const format = new Intl.NumberFormat(undefined, {
      maximumFractionDigits: Number.isInteger(target) ? 0 : 1,
    });
    output.textContent = "0";

    const step = (now) => {
      const progress = Math.min((now - startTime) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      output.textContent = format.format(target * eased);
      if (progress < 1) requestAnimationFrame(step);
      else output.textContent = rawValue;
    };
    requestAnimationFrame(step);
  };

  const initializeMotion = (host) => {
    const targets = host.querySelectorAll([
      ".dashboard-header",
      ".dashboard-banner",
      ".dashboard-section-header",
      ".stat-card",
      ".module-launcher-tile",
      ".dashboard-primary-grid > .card:not(.modules-widget)",
      ".dashboard-bottom-grid > .card",
      ".operations-metric-card",
      ".operations-intelligence-grid > .operations-widget",
    ].join(","));
    targets.forEach((target) => target.classList.add("motion-enter"));

    if (prefersReducedMotion() || !("IntersectionObserver" in window)) {
      targets.forEach((target) => {
        target.classList.add("is-visible");
        if (target.classList.contains("stat-card")) animateKpiValue(target);
      });
      return;
    }

    host.classList.add("motion-ready");
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        if (entry.target.classList.contains("stat-card")) {
          entry.target.addEventListener("animationstart", () => animateKpiValue(entry.target), { once: true });
        }
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: "0px 0px -24px" });
    targets.forEach((target) => observer.observe(target));
  };

  const render = () => {
    const host = document.getElementById("command-center");
    const data = window.HimsDashboardData;
    if (!host || !data) return;
    const currentUser = window.HimsSession?.getUser();
    const user = currentUser || data.user;
    const fleet = window.HimsModuleRegistry?.getModule("fleet");
    const modules = data.modules.map((module) => module.registryId === "fleet" && fleet
      ? { ...module, name: fleet.name, description: fleet.description, status: fleet.statusLabel, state: fleet.state, integrationPending: !fleet.enabled, canNavigate: fleet.enabled }
      : module);
    host.innerHTML = `
      <div class="dashboard-page">
        <section class="dashboard-chapter dashboard-overview" aria-labelledby="hospital-overview-title">
        <header class="dashboard-header">
          <div class="dashboard-header-main">
            <p class="dashboard-kicker">Executive Overview</p>
            <h1 id="hospital-overview-title">Hospital Overview</h1>
            <p class="dashboard-lead">Executive view of hospital status, current operations, and items requiring attention.</p>
          </div>
          <div class="dashboard-header-actions">
            <div class="dashboard-session"><strong>Good day, ${user.name}</strong><span>${user.role} · <time id="dashboard-time"></time></span></div>
            <button type="button" class="btn-outline" id="refresh-dashboard">${icon("ph-arrows-clockwise")}<span>Refresh</span></button>
          </div>
        </header>

        <section class="dashboard-banner" aria-label="Command Center context">
          <div class="dashboard-banner-item"><span class="dashboard-banner-label">Facility</span><strong>${data.user.hospital}</strong></div>
          <div class="dashboard-banner-item"><span class="dashboard-banner-label">Workspace</span><strong>Hospital-wide overview</strong></div>
          <div class="dashboard-banner-item"><span class="dashboard-banner-label">Data mode</span><strong>Frontend sample data</strong></div>
          <div class="dashboard-banner-item"><span class="dashboard-banner-label">Service state</span><strong class="dashboard-banner-status">Main frontend shell operational</strong></div>
        </section>
        </section>

        <section class="dashboard-section dashboard-chapter executive-kpi-chapter" aria-labelledby="overview-title">
          <div class="dashboard-section-header"><div><p class="dashboard-kicker">Executive Summary</p><h2 id="overview-title" class="dashboard-section-title">Executive KPI Summary</h2><p class="dashboard-section-desc">The most important current indicators. All values are frontend sample data.</p></div></div>
          <div class="stats-grid command-kpi-grid">${data.overview.map((item) => `<article class="stat-card"><div class="stat-card-main"><div class="stat-icon ${item.tone}">${icon(item.icon)}</div><div class="stat-content"><span class="kpi-label">${item.label}</span><h2 class="kpi-value" data-value="${item.value}">${item.value}</h2></div></div><div class="stat-card-insight"><div><span class="kpi-support">${item.detail}</span><span class="kpi-trend">${icon("ph-info")} ${item.trend}</span></div>${sparkline(item.sparkline, item.tone)}</div></article>`).join("")}</div>
        </section>

        ${renderOperations(data.operations)}

        <section class="dashboard-section dashboard-chapter monitoring-chapter" aria-labelledby="monitoring-title">
          <div class="dashboard-section-header"><div><p class="dashboard-kicker">Priority Monitoring</p><h2 id="monitoring-title" class="dashboard-section-title">Attention and Activity</h2><p class="dashboard-section-desc">Items requiring review, recent events, and supporting operational insight.</p></div></div>
          <div class="dashboard-primary-grid dashboard-monitoring-grid">
          <article class="card dashboard-widget activity-widget"><div class="card-header"><div><p class="widget-eyebrow">Recent Events</p><h2>Recent Activity</h2><p class="card-subtitle">Latest sample events across hospital operations.</p></div><span class="widget-count">${data.activity.length}</span></div><div class="activity-list">${data.activity.map((item) => `<div class="activity-item"><span class="activity-icon ${item.tone}">${icon(item.icon)}</span><div class="activity-body"><div class="activity-title-row"><strong>${item.title}</strong><span class="module-label">${item.module || "System"}</span></div><span>${item.detail}</span><small>${item.time}</small></div></div>`).join("")}</div></article>

          <article class="card dashboard-widget alerts-widget"><div class="card-header"><div><p class="widget-eyebrow">Needs Attention</p><h2>Priority Alerts</h2><p class="card-subtitle">Severity-ranked frontend operational notices.</p></div><span class="widget-count widget-count-danger">${data.alerts.length}</span></div><div class="alert-list">${data.alerts.map((item) => `<div class="alert-item alert-${item.tone}"><span class="alert-icon ${item.tone}">${icon(item.icon)}</span><div class="alert-content"><div class="alert-row"><strong>${item.title}</strong>${status(item.status, item.tone)}</div><p>${item.detail}</p><button type="button" class="alert-action" disabled aria-disabled="true">${item.actionLabel || "Review unavailable"}<i class="ph ph-arrow-right" aria-hidden="true"></i></button></div></div>`).join("")}</div></article>

          <article class="card dashboard-widget modules-widget" aria-labelledby="modules-title"><div class="card-header"><div><p class="widget-eyebrow">Application launcher</p><h2 id="modules-title">Management modules</h2><p class="card-subtitle">Integration-ready and planned HIMS services.</p></div><span class="widget-count">${modules.length}</span></div><div class="module-launcher-list">${modules.map((module) => `<article class="module-launcher-tile"><span class="module-icon ${module.tone}">${icon(module.icon)}</span><div class="module-tile-copy"><h3>${module.name}</h3><p class="module-connection"><i class="connection-dot ${module.tone}" aria-hidden="true"></i>${module.state}</p></div><div class="module-tile-actions">${status(module.status, module.tone)}<button type="button" class="module-open" ${module.canNavigate ? 'data-module-link="fleet"' : 'disabled aria-disabled="true"'} aria-label="${module.integrationPending ? `${module.name}: Integration Pending` : (module.canNavigate ? `Open ${module.name}` : `${module.name} is ${module.status}`)}"><i class="ph ph-arrow-up-right" aria-hidden="true"></i></button></div></article>`).join("")}</div></article>
          ${renderInsights(data.operations)}
          </div>
        </section>

        <section class="dashboard-section dashboard-chapter administration-chapter" aria-labelledby="administration-title">
          <div class="dashboard-section-header"><div><p class="dashboard-kicker">Administration</p><h2 id="administration-title" class="dashboard-section-title">Systems and Supporting Information</h2><p class="dashboard-section-desc">Module readiness, system context, schedules, communications, and utilities.</p></div></div>
          <div class="dashboard-bottom-grid dashboard-support-grid" aria-label="System tools and information">
          <article class="card dashboard-widget health-widget"><div class="card-header"><div><p class="widget-eyebrow">Service readiness</p><h2>System health</h2><p class="card-subtitle">Accurate status for this frontend build.</p></div></div><div class="health-list">${data.health.map((item) => `<div class="health-row"><span class="health-icon ${item.tone}">${icon(item.icon)}</span><div class="health-copy"><strong>${item.name}</strong><small>${item.detail}</small></div>${status(item.status, item.tone)}</div>`).join("")}</div></article>

          <article class="card dashboard-widget actions-widget"><div class="card-header"><div><p class="widget-eyebrow">Shortcuts</p><h2>Quick actions</h2><p class="card-subtitle">Only available routes are enabled.</p></div></div><div class="quick-actions quick-action-grid"><button type="button" class="quick-action" disabled aria-disabled="true">${icon("ph-truck")}<span><strong>Fleet Integration Pending</strong><small>Ready for Integration</small></span></button><button type="button" class="quick-action" disabled>${icon("ph-chart-bar")}<span><strong>Reports</strong><small>Coming soon</small></span></button><button type="button" class="quick-action" disabled>${icon("ph-users-three")}<span><strong>Users</strong><small>Coming soon</small></span></button><button type="button" class="quick-action" disabled>${icon("ph-gear")}<span><strong>Settings</strong><small>Coming soon</small></span></button></div></article>

          <article class="card dashboard-widget announcements-widget"><div class="card-header"><div><p class="widget-eyebrow">Hospital updates</p><h2>Announcements</h2><p class="card-subtitle">Current frontend notices.</p></div><button type="button" class="widget-link" disabled aria-disabled="true">View all</button></div><div class="announcement-list">${data.announcements.map((item) => `<article class="announcement-item"><span class="announcement-priority">${item.priority}</span><h3>${item.title}</h3><p>${item.summary}</p><time>${item.date}</time></article>`).join("")}</div></article>

          <article class="card dashboard-widget calendar-widget"><div class="card-header"><div><p class="widget-eyebrow">Schedule</p><h2>${data.calendar.month}</h2><p class="card-subtitle">Sample hospital events.</p></div><span class="calendar-today">Today · 21</span></div>${calendar(data.calendar)}<div class="calendar-legend"><span><i aria-hidden="true"></i>Sample event</span><strong>3 upcoming</strong></div></article>
          ${renderWeather(data.operations)}
          ${renderNews(data.operations)}
          </div>
        </section>
      </div>`;

    const monitoringGrid = host.querySelector(".dashboard-monitoring-grid");
    const priorityAlerts = monitoringGrid?.querySelector(".alerts-widget");
    if (monitoringGrid && priorityAlerts) monitoringGrid.prepend(priorityAlerts);

    const administrationChapter = host.querySelector(".administration-chapter");
    const administrationHeading = administrationChapter?.querySelector(".dashboard-section-header");
    const modulesWidget = host.querySelector(".modules-widget");
    if (administrationHeading && modulesWidget) administrationHeading.insertAdjacentElement("afterend", modulesWidget);

    const supportGrid = administrationChapter?.querySelector(".dashboard-support-grid");
    [".health-widget", ".announcements-widget", ".calendar-widget", ".weather-widget", ".actions-widget", ".news-widget"].forEach((selector) => {
      const widget = supportGrid?.querySelector(selector);
      if (supportGrid && widget) supportGrid.append(widget);
    });

    const updateTime = () => {
      const output = document.getElementById("dashboard-time");
      if (output) output.textContent = new Intl.DateTimeFormat(undefined, { dateStyle: "full", timeStyle: "short" }).format(new Date());
    };
    updateTime();
    window.setInterval(updateTime, 60_000);
    document.getElementById("refresh-dashboard")?.addEventListener("click", (event) => {
      const button = event.currentTarget;
      const page = host.querySelector(".dashboard-page");
      if (button.disabled) return;
      button.disabled = true;
      button.classList.add("is-refreshing");
      page?.classList.add("is-refreshing");
      updateTime();
      window.setTimeout(() => {
        button.classList.remove("is-refreshing");
        page?.classList.remove("is-refreshing");
        button.disabled = false;
      }, 650);
    });
    initializeMotion(host);
    window.HimsModuleRegistry?.wireLinks(host);
  };

  document.addEventListener("DOMContentLoaded", render);
})();
