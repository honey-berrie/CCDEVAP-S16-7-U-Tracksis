document.addEventListener("DOMContentLoaded", () => {
  simulateRequest(() => {
    renderStatCards();
    renderSubmissionChart();
    renderProgressChart();
    renderWorkloadList();
  }, { delay: 400 });
});

function renderStatCards() {
  const summary = getAdminSummary();
  const grid = document.getElementById("statGrid");

  const cards = [
    { label: "Total Students", value: summary.totalStudents, accent: "" },
    { label: "Total Advisers", value: summary.totalAdvisers, accent: "" },
    { label: "Total Coordinators", value: summary.totalCoordinators, accent: "" },
    { label: "Total Groups", value: summary.totalGroups, accent: "" },
    { label: "Active Theses", value: summary.activeTheses, accent: "" },
    { label: "Archived Theses", value: summary.archivedTheses, accent: "accent-neutral" },
    { label: "Pending Reviews", value: summary.pendingReviews, accent: "accent-warning" },
  ];

  grid.innerHTML = cards.map(card => `
    <div class="stat-card ${card.accent}">
      <div class="stat-card-label">${card.label}</div>
      <div class="stat-card-value">${card.value}</div>
    </div>
  `).join("");
}

function renderSubmissionChart() {
  const ctx = document.getElementById("submissionChart");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: submissionStats.labels,
      datasets: [
        {
          label: "On-time",
          data: submissionStats.onTime,
          backgroundColor: "#006936",
          borderRadius: 4,
        },
        {
          label: "Late",
          data: submissionStats.late,
          backgroundColor: "#E0A800",
          borderRadius: 4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: "bottom", labels: { font: { size: 11 } } } },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: "#F0F2F0" } },
      },
    },
  });
}

function renderProgressChart() {
  const ctx = document.getElementById("progressChart");
  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: progressData.labels,
      datasets: [{
        data: progressData.values,
        backgroundColor: ["#006936", "#3D8B5F", "#E0A800", "#C0392B"],
        borderWidth: 0,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: "bottom", labels: { font: { size: 11 }, boxWidth: 12 } } },
    },
  });
}

function renderWorkloadList() {
  const container = document.getElementById("workloadList");
  const max = Math.max(...adviserWorkload.map(entry => entry.groups), 1);

  container.innerHTML = adviserWorkload.map(entry => `
    <div class="workload-row">
      <div class="workload-name">${entry.adviser}</div>
      <div class="workload-bar-track">
        <div class="workload-bar-fill" style="width:${(entry.groups / max) * 100}%"></div>
      </div>
      <div class="workload-count">${entry.groups}</div>
    </div>
  `).join("");
}
