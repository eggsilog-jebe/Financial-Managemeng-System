(() => {
  const icon = (name) => `<i class="ph-fill ${name}" aria-hidden="true"></i>`;
  const status = (label, tone) => `<span class="status-chip status-chip--${tone}">${label}</span>`;
  const storageKey = "hims-dashboard-support-tab";
  const moduleStorageKey = "hims-dashboard-modules-expanded";
  const dateTimeFormatter = new Intl.DateTimeFormat(undefined, { dateStyle: "medium", timeStyle: "short" });
  let clockTimer;
  let refreshTimer;
  const emptyState = (iconName, title, detail) => `<div class="enterprise-empty" role="status"><span>${icon(iconName)}</span><div><strong>${title}</strong><p>${detail}</p></div></div>`;

  const header = (eyebrow, title, subtitle = "", trailing = "", id = "") => `
    <div class="card-header"><div><p class="widget-eyebrow">${eyebrow}</p><h2${id ? ` id="${id}"` : ""}>${title}</h2>${subtitle ? `<p class="card-subtitle">${subtitle}</p>` : ""}</div>${trailing}</div>`;

  const snapshot = (ops) => `
    <article class="card command-card">
      ${header("Daily throughput", "Today's Hospital Snapshot", "Consolidated frontend sample activity.", `<span class="widget-count">${ops.snapshot.length}</span>`)}
      ${ops.snapshot.length ? `<div class="snapshot-grid">${ops.snapshot.map((item) => `<div class="snapshot-item"><span>${icon(item.icon)}</span><div><strong>${item.value}</strong><small>${item.label}</small></div></div>`).join("")}</div>` : emptyState("ph-chart-bar", "No throughput activity", "Sample daily throughput will appear here.")}
    </article>`;

  const primaryOperations = (ops) => {
    return `<article class="card command-card primary-operations-card">
      ${header("Live sample operations", "Hospital Operations", "", '<span class="emergency-state"><i aria-hidden="true"></i>Emergency ready</span>', "primary-operations-title")}
      ${ops.departments.length ? `<div class="operations-status-list">${ops.departments.map((item) => `<div class="operations-status-row"><i class="operations-dot ${item.tone}" aria-hidden="true"></i><div><strong>${item.name}</strong><small>${item.status}</small></div><span>${item.availability}</span><progress max="100" value="${item.capacity}" aria-label="${item.name} sample utilization ${item.capacity} percent"></progress><b>${item.capacity}%</b></div>`).join("")}</div>` : emptyState("ph-buildings", "Operations are ready for review", "No sample department statuses are currently available.")}
    </article>`;
  };

  const alerts = (items) => {
    const groups = [
      { label: "Critical", tone: "danger", icon: "ph-warning-octagon", items: items.filter((item) => item.tone === "danger") },
      { label: "Warning", tone: "warning", icon: "ph-warning", items: items.filter((item) => item.tone === "warning") },
      { label: "Information", tone: "info", icon: "ph-info", items: items.filter((item) => !["danger", "warning"].includes(item.tone)) },
    ];
    return `<aside class="card priority-alerts" aria-labelledby="priority-alerts-title">
      ${header("Structured attention", "Smart Alerts", "", `<span class="widget-count danger-count">${items.length}</span>`, "priority-alerts-title")}
      ${items.length ? `<div class="smart-alert-groups">${groups.map((group) => `<section class="smart-alert-group alert-group-${group.tone}" aria-labelledby="alert-group-${group.tone}"><div class="smart-alert-heading"><h3 id="alert-group-${group.tone}">${icon(group.icon)}${group.label}</h3><span>${group.items.length}</span></div>${group.items.length ? `<div class="smart-alert-list">${group.items.map((item) => `<article class="smart-alert-item"><span class="alert-icon ${item.tone}">${icon(item.icon)}</span><div><strong>${item.title}</strong><p>${item.detail}</p><small><b>Recommended:</b> ${item.recommendation || "Review this sample notice."}</small></div><button type="button" disabled aria-disabled="true">View Details</button></article>`).join("")}</div>` : (group.tone === "danger" ? emptyState("ph-check-circle", "Everything looks good", "No critical alerts require attention.") : emptyState("ph-bell-slash", `No ${group.label.toLowerCase()} alerts`, `No sample ${group.label.toLowerCase()} notices require review.`))}</section>`).join("")}</div>` : emptyState("ph-check-circle", "Everything looks good", "No sample alerts require attention.")}
    </aside>`;
  };

  const quickActions = () => {
    const actions = [
      { label: "Register Patient", meta: "Integration pending", icon: "ph-user-plus" },
      { label: "Dispatch Fleet", meta: "Integration pending", icon: "ph-ambulance" },
      { label: "Generate Report", meta: "Coming soon", icon: "ph-chart-bar" },
      { label: "View Admissions", meta: "Integration pending", icon: "ph-sign-in" },
      { label: "Manage Staff", meta: "Integration pending", icon: "ph-users-three" },
      { label: "Open Inventory", meta: "Integration pending", icon: "ph-package" },
    ];
    return `<section class="quick-actions-section" aria-labelledby="quick-actions-title"><div class="quick-actions-heading"><p class="dashboard-kicker">Common workflows</p><h2 id="quick-actions-title">Quick Actions</h2></div><div class="executive-actions" role="list">${actions.map((item) => `<button type="button" class="executive-action" disabled aria-disabled="true" role="listitem"><span>${icon(item.icon)}</span><span><strong>${item.label}</strong><small>${item.meta}</small></span></button>`).join("")}</div></section>`;
  };

  const departments = (ops) => `
    <article class="support-card">
      ${header("Capacity and availability", "Department Status", "Sample workload across core hospital departments.", `<span class="widget-count">${ops.departments.length}</span>`)}
      ${ops.departments.length ? `<div class="department-grid">${ops.departments.map((item) => `<article class="department-card"><div class="department-head"><span class="department-icon ${item.tone}">${icon(item.icon)}</span><div><h3>${item.name}</h3><p>${item.status}</p></div><span>${item.capacity}%</span></div><progress max="100" value="${item.capacity}" aria-label="${item.name} sample capacity ${item.capacity} percent"></progress><div class="department-meta"><span>Workload <strong>${item.workload}</strong></span><span>Available <strong>${item.availability}</strong></span></div></article>`).join("")}</div>` : emptyState("ph-buildings", "No department statuses", "Sample department availability will appear here.")}
    </article>`;

  const shifts = (ops) => `
    <article class="support-card">
      ${header("Workforce coverage", "Shift Overview", "Sample staffing availability by shift.")}
      ${ops.shifts.length ? `<div class="shift-table-wrap"><table class="shift-table"><thead><tr><th scope="col">Shift</th><th scope="col">Doctors</th><th scope="col">Nurses</th><th scope="col">Support</th><th scope="col">Availability</th></tr></thead><tbody>${ops.shifts.map((item) => `<tr><th scope="row">${item.name}</th><td>${item.doctors}</td><td>${item.nurses}</td><td>${item.support}</td><td><span>${item.availability}</span></td></tr>`).join("")}</tbody></table></div>` : emptyState("ph-users-three", "No shift coverage", "Sample workforce schedules will appear here.")}
    </article>`;

  const health = (items) => `
    <article class="support-card">
      ${header("Service readiness", "System Health", "Accurate status for this frontend build.")}
      ${items.length ? `<div class="health-grid">${items.map((item) => `<div class="health-row"><span class="health-icon ${item.tone}">${icon(item.icon)}</span><div><strong>${item.name}</strong><small>${item.detail}</small></div>${status(item.status, item.tone)}</div>`).join("")}</div>` : emptyState("ph-heartbeat", "No system notices", "Sample service readiness will appear here.")}
    </article>`;

  const activity = (items) => {
    const visibleItems = items.slice(0, 5);
    return `<article class="support-card activity-timeline-card">
      ${header("Newest first · sample events", "Live Activity Timeline", "", `<span class="widget-count">${visibleItems.length}</span>`)}
      ${visibleItems.length ? `<ol class="activity-timeline">${visibleItems.map((item) => `<li class="timeline-item"><span class="timeline-icon ${item.tone}">${icon(item.icon)}</span><div class="timeline-copy"><div><strong>${item.title}</strong><span class="timeline-status"><i class="${item.tone}" aria-hidden="true"></i>${item.status || (item.tone === "success" ? "Completed" : "Sample event")}</span></div><p>${item.detail}</p><footer><span>${item.module || "System"}</span><time>${item.time}</time></footer></div></li>`).join("")}</ol>` : emptyState("ph-clock-counter-clockwise", "No recent activity", "New sample operational events will appear here.")}
      <button type="button" class="feed-action" disabled aria-disabled="true">View Full Activity</button>
    </article>`;
  };

  const calendar = (data) => {
    const days = Array.from({ length: 31 }, (_, index) => index + 1);
    return `<article class="support-card bordered-support">
      ${header("Schedule", data.month, "Compact sample hospital calendar.", '<span class="calendar-today">Today · 21</span>')}
      <div class="calendar-layout"><div class="mini-calendar"><div class="calendar-weekdays" aria-hidden="true">${["S", "M", "T", "W", "T", "F", "S"].map((day) => `<span>${day}</span>`).join("")}</div><div class="calendar-days">${Array.from({ length: 3 }, () => '<span aria-hidden="true"></span>').join("")}${days.map((day) => `<span class="calendar-day${day === data.selectedDay ? " is-selected" : ""}${data.eventDays.includes(day) ? " has-event" : ""}" ${day === data.selectedDay ? 'aria-current="date"' : ""} aria-label="July ${day}, 2026${data.eventDays.includes(day) ? ", sample event" : ""}">${day}</span>`).join("")}</div></div><div class="upcoming-events"><strong>Upcoming</strong>${data.eventDays.length ? data.eventDays.map((day) => `<div><span>Jul ${day}</span><p>Sample hospital event</p></div>`).join("") : emptyState("ph-calendar-x", "No scheduled events", "No sample hospital events are currently scheduled.")}</div></div>
    </article>`;
  };

  const announcements = (items) => {
    const visibleItems = items.slice(0, 3);
    return `<article class="support-card bordered-support announcement-feed-card">
      ${header("Hospital updates · sample notices", "Announcements")}
      ${visibleItems.length ? `<div class="announcement-feed">${visibleItems.map((item) => `<article><div><span>${item.priority}</span><time>${item.date}</time></div><h3>${item.title}</h3><p>${item.summary}</p></article>`).join("")}</div>` : emptyState("ph-megaphone", "No announcements", "New sample hospital notices will appear here.")}
      <button type="button" class="feed-action" disabled aria-disabled="true">View All Announcements</button>
    </article>`;
  };

  const modules = (items, expanded = false) => `
    <section class="dashboard-section modules-section" aria-labelledby="modules-title">
      <div class="dashboard-section-header module-section-header"><div><p class="dashboard-kicker">Enterprise launcher</p><h2 id="modules-title" class="dashboard-section-title">Management Modules</h2><p class="dashboard-section-desc">Current frontend readiness and integration state.</p></div><button type="button" class="btn-outline module-toggle" aria-expanded="${expanded}" aria-controls="module-list">${expanded ? "Show fewer modules" : "View all modules"}</button></div>
      ${items.length ? `<div class="module-list${expanded ? " show-all" : ""}" id="module-list">${items.map((item, index) => {
        const moduleId = item.registryId || `module-${index + 1}`;
        const available = Boolean(item.canNavigate);
        const explanation = item.integrationPending ? "Integration pending; no backend connection is configured." : (available ? "Available using the approved module destination." : `${item.state}; no approved route is available.`);
        return `<article class="module-tile${index > 3 ? " module-extra" : ""}${available ? " is-available" : " is-unavailable"}" tabindex="0" data-module-discovery-id="${moduleId}" aria-describedby="module-state-${index + 1}"><span class="module-icon ${item.tone}">${icon(item.icon)}</span><div class="module-copy"><h3>${item.name}</h3><p id="module-state-${index + 1}"><i class="connection-dot ${item.tone}" aria-hidden="true"></i>${item.state}<span class="visually-hidden">. ${explanation}</span></p></div><div class="module-actions">${status(item.status, item.tone)}<button type="button" ${available ? `data-module-link="${item.registryId}"` : 'disabled aria-disabled="true"'} aria-label="${available ? `Open ${item.name}` : `${item.name}: ${item.status}; unavailable`}"><i class="ph ${available ? "ph-arrow-up-right" : "ph-lock-key"}" aria-hidden="true"></i></button></div></article>`;
      }).join("")}</div>` : `<div id="module-list">${emptyState("ph-squares-four", "No modules listed", "Sample module readiness will appear here.")}</div>`}
    </section>`;

  const supportTabs = (data) => {
    const ops = data.operations;
    const tabs = [
      { id: "operations", label: "Operations", body: `${snapshot(ops)}${departments(ops)}<details class="dashboard-details"><summary>Operational intelligence and future integrations</summary><div class="details-grid"><article><h3>AI Operational Insights</h3>${ops.aiInsights.map((item) => `<p><span class="detail-icon ${item.tone}">${icon(item.icon)}</span>${item.text}</p>`).join("")}</article><article><h3>Hospital Floor Overview</h3><p>Interactive mapping is not connected in this frontend build.</p><div class="integration-chips">${ops.mapIntegrations.map((item) => `<span>${item}</span>`).join("")}</div></article></div></details>` },
      { id: "workforce", label: "Workforce", body: shifts(ops) },
      { id: "system", label: "System", body: `${health(data.health)}<div class="summary-grid"><article class="summary-card"><span class="weather-icon">${icon(ops.weather.icon)}</span><div><p class="widget-eyebrow">Local conditions</p><strong>${ops.weather.temperature} · ${ops.weather.condition}</strong><small>${ops.weather.location} · ${ops.weather.detail}</small></div></article><article class="summary-card"><span class="health-icon primary">${icon("ph-lightning")}</span><div><p class="widget-eyebrow">Shortcuts</p><strong>4 integration-ready placeholders</strong><small>Fleet, reports, users, and settings remain unavailable.</small></div></article></div>` },
      { id: "updates", label: "Updates", body: `<div class="updates-grid">${announcements(data.announcements)}${calendar(data.calendar)}</div>${activity(data.activity)}<details class="dashboard-details"><summary>Hospital news</summary><div class="news-grid">${ops.news.map((item) => `<article><span>${item.type}</span><h3>${item.title}</h3><time>${item.date}</time></article>`).join("")}</div></details>` },
    ];
    return `<section class="dashboard-section supporting-section" aria-labelledby="supporting-title">
      <div class="dashboard-section-header"><div><p class="dashboard-kicker">Supporting intelligence</p><h2 id="supporting-title" class="dashboard-section-title">Operational Workspace</h2><p class="dashboard-section-desc">Choose one workspace to review supporting information without a long stacked page.</p></div></div>
      <div class="dashboard-tabs"><div class="tab-list" role="tablist" aria-label="Dashboard supporting information">${tabs.map((tab, index) => `<button type="button" role="tab" id="tab-${tab.id}" aria-controls="panel-${tab.id}" aria-selected="${index === 0}" tabindex="${index === 0 ? 0 : -1}">${tab.label}</button>`).join("")}</div>${tabs.map((tab, index) => `<div class="tab-panel" role="tabpanel" id="panel-${tab.id}" aria-labelledby="tab-${tab.id}"${index ? " hidden" : ""}>${tab.body}</div>`).join("")}</div>
    </section>`;
  };

  const activateTab = (host, id, focus = false) => {
    const selected = host.querySelector(`#tab-${id}`);
    if (!selected) return;
    host.querySelectorAll('[role="tab"]').forEach((tab) => {
      const active = tab === selected;
      tab.setAttribute("aria-selected", String(active));
      tab.tabIndex = active ? 0 : -1;
      if (active && focus) tab.focus();
    });
    host.querySelectorAll('[role="tabpanel"]').forEach((panel) => { panel.hidden = panel.id !== `panel-${id}`; });
    try { sessionStorage.setItem(storageKey, id); } catch (_) { /* Optional persistence. */ }
  };

  const wireTabs = (host) => {
    const list = host.querySelector('[role="tablist"]');
    if (!list) return;
    try { activateTab(host, sessionStorage.getItem(storageKey)); } catch (_) { /* Optional persistence. */ }
    list.addEventListener("click", (event) => {
      const tab = event.target.closest('[role="tab"]');
      if (tab) activateTab(host, tab.id.slice(4));
    });
    list.addEventListener("keydown", (event) => {
      const tabs = [...list.querySelectorAll('[role="tab"]')];
      const current = tabs.indexOf(event.target);
      let next = current;
      if (event.key === "ArrowRight") next = (current + 1) % tabs.length;
      else if (event.key === "ArrowLeft") next = (current - 1 + tabs.length) % tabs.length;
      else if (event.key === "Home") next = 0;
      else if (event.key === "End") next = tabs.length - 1;
      else return;
      event.preventDefault();
      activateTab(host, tabs[next].id.slice(4), true);
    });
  };

  const setModuleDisclosure = (host, expanded) => {
    const list = host.querySelector(".module-list");
    const button = host.querySelector(".module-toggle");
    list?.classList.toggle("show-all", expanded);
    if (button) {
      button.setAttribute("aria-expanded", String(expanded));
      button.textContent = expanded ? "Show fewer modules" : "View all modules";
    }
    try { sessionStorage.setItem(moduleStorageKey, String(expanded)); } catch (_) { /* Optional session persistence. */ }
  };

  document.addEventListener("hims:module-locate", (event) => {
    const host = document.getElementById("command-center");
    const moduleId = event.detail?.moduleId;
    if (!host || !moduleId) return;
    const tile = [...host.querySelectorAll("[data-module-discovery-id]")].find((item) => item.dataset.moduleDiscoveryId === moduleId);
    if (!tile) return;
    if (tile.classList.contains("module-extra")) setModuleDisclosure(host, true);
    tile.scrollIntoView({ behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth", block: "center" });
    tile.focus({ preventScroll: true });
    tile.classList.add("is-located");
    window.setTimeout(() => tile.classList.remove("is-located"), 1600);
  });

  const render = () => {
    const host = document.getElementById("command-center");
    const data = window.HimsDashboardData;
    if (!host || !data) return;
    const user = window.HimsSession?.getUser() || data.user;
    const fleet = window.HimsModuleRegistry?.getModule("fleet");
    const moduleData = data.modules.map((item) => item.registryId === "fleet" && fleet ? { ...item, name: fleet.name, description: fleet.description, status: fleet.statusLabel, state: fleet.state, integrationPending: !fleet.enabled, canNavigate: fleet.enabled } : item);
    const discoveryModules = moduleData.map((item, index) => Object.freeze({
      id: item.registryId || `module-${index + 1}`,
      registryId: item.registryId || null,
      name: item.name,
      shortName: item.name,
      description: `${item.state}. ${item.status}.`,
      icon: item.icon,
      statusLabel: item.status,
      state: item.state,
      enabled: Boolean(item.canNavigate),
    }));
    window.HimsModuleDiscovery = Object.freeze({ modules: Object.freeze(discoveryModules) });
    let modulesExpanded = false;
    try { modulesExpanded = sessionStorage.getItem(moduleStorageKey) === "true"; } catch (_) { /* Optional session persistence. */ }
    const overviewByLabel = Object.fromEntries(data.overview.map((item) => [item.label, item]));
    const snapshotByLabel = Object.fromEntries(data.operations.snapshot.map((item) => [item.label, item]));
    const metricByLabel = Object.fromEntries(data.operations.liveMetrics.map((item) => [item.label, item]));
    const executiveKpis = [
      { label: "Appointments Today", value: snapshotByLabel.Appointments.value, icon: "ph-calendar-check", tone: "primary" },
      { label: "Admissions", value: snapshotByLabel["Admissions Today"].value, icon: "ph-sign-in", tone: "info" },
      { label: "Available Beds", value: metricByLabel["Available Beds"].value, icon: "ph-bed", tone: "success" },
      { label: "Critical Alerts", value: overviewByLabel["Critical Alerts"].value, icon: "ph-warning-circle", tone: "danger" },
      { label: "Staff On Duty", value: metricByLabel["Staff On Duty"].value, icon: "ph-users-three", tone: "info" },
      { label: "Module Readiness", value: overviewByLabel["Modules Ready for Integration"].value, icon: "ph-squares-four", tone: "warning" },
    ];

    host.innerHTML = `<div class="dashboard-page"><section class="command-center-top" aria-labelledby="hospital-overview-title">
      <header class="dashboard-header executive-header"><div class="dashboard-header-main"><p class="dashboard-kicker">Executive command center</p><h1 id="hospital-overview-title">Hospital Overview</h1><p class="dashboard-lead">Good day, ${user.name}. Here is today’s operational position.</p></div><div class="dashboard-header-actions"><div class="executive-live"><span><i aria-hidden="true"></i>System live</span><small>${data.user.hospital} · Frontend sample data · <span id="dashboard-freshness">Updated just now</span></small></div><div class="dashboard-session"><strong>${user.role}</strong><span><time id="dashboard-time"></time></span></div><button type="button" class="btn-outline" id="refresh-dashboard" aria-label="Refresh dashboard status">${icon("ph-arrows-clockwise")}<span>Refresh</span></button></div></header>
      <div class="live-status-ribbon" role="region" tabindex="0" aria-label="Hospital operational summary; scroll horizontally to view all statuses"><span class="live-status-pill status-success"><i aria-hidden="true"></i><strong>Emergency Services</strong><small>Ready</small></span>${data.operations.liveStatus.map((item) => `<span class="live-status-pill status-${item.tone}"><i aria-hidden="true"></i><strong>${item.label}</strong><small>${item.state}</small></span>`).join("")}</div>
      <div class="command-kpi-grid">${executiveKpis.map((item) => `<article class="stat-card compact-kpi"><span class="stat-icon ${item.tone}">${icon(item.icon)}</span><div class="stat-content"><strong class="kpi-value">${item.value}</strong><span class="kpi-label">${item.label}</span></div></article>`).join("")}</div>
      ${quickActions()}
      <div class="command-primary-grid">${primaryOperations(data.operations)}${alerts(data.alerts)}</div>
    </section>${modules(moduleData, modulesExpanded)}${supportTabs(data)}</div>`;

    let refreshedAt = new Date();
    const updateTime = () => {
      const now = new Date();
      const output = host.querySelector("#dashboard-time");
      const freshness = host.querySelector("#dashboard-freshness");
      if (output) output.textContent = dateTimeFormatter.format(now);
      if (freshness) {
        const minutes = Math.floor((now - refreshedAt) / 60_000);
        freshness.textContent = minutes < 1 ? "Updated just now" : `Last updated ${minutes} min ago`;
      }
    };
    updateTime();
    window.clearInterval(clockTimer);
    clockTimer = window.setInterval(updateTime, 60_000);
    host.querySelector("#refresh-dashboard")?.addEventListener("click", (event) => {
      const button = event.currentTarget;
      const label = button.querySelector("span");
      button.disabled = true;
      button.classList.add("is-loading");
      button.setAttribute("aria-busy", "true");
      if (label) label.textContent = "Refreshing";
      refreshedAt = new Date();
      updateTime();
      window.clearTimeout(refreshTimer);
      refreshTimer = window.setTimeout(() => {
        button.disabled = false;
        button.classList.remove("is-loading");
        button.removeAttribute("aria-busy");
        if (label) label.textContent = "Refresh";
      }, 450);
    });
    host.querySelector(".module-toggle")?.addEventListener("click", (event) => {
      const button = event.currentTarget;
      const expanded = button.getAttribute("aria-expanded") === "true";
      setModuleDisclosure(host, !expanded);
    });
    wireTabs(host);
    window.HimsModuleRegistry?.wireLinks(host);
  };

  document.addEventListener("DOMContentLoaded", render);
})();
