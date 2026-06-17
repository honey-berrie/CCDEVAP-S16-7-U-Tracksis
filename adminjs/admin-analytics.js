document.addEventListener("DOMContentLoaded", () => {
  simulateRequest(() => {
    renderAnalyticsStats();
    renderSubmissionAnalyticsChart();
    renderCompletionChart();
    renderWorkloadChart();
    renderProgressReport();
  }, { delay: 400 });

  document.getElementById("exportPdfBtn").addEventListener("click", () => simulateExport("PDF"));
  document.getElementById("exportCsvBtn").addEventListener("click", () => simulateExport("CSV"));
});

function renderAnalyticsStats() {
  const totalGroups = groups.length;
  const completed = groups.filter(group => group.status === "completed").length;
  const completionRate = Math.round((completed / totalGroups) * 100);
  const avgProgress = Math.round(groups.reduce((sum, group) => sum + group.progress, 0) / totalGroups);

  const totalOnTime = submissionStats.onTime.reduce((a, b) => a + b, 0);
  const totalLate = submissionStats.late.reduce((a, b) => a + b, 0);
  const onTimeRate = Math.round((totalOnTime / (totalOnTime + totalLate)) * 100);

  const cards = [
    { label: "Completion Rate", value: `${completionRate}%`, accent: "" },
    { label: "Average Group Progress", value: `${avgProgress}%`, accent: "" },
    { label: "On-Time Submission Rate", value: `${onTimeRate}%`, accent: "" },
    { label: "Total Submissions Logged", value: totalOnTime + totalLate, accent: "accent-neutral" },
  ];

  document.getElementById("analyticsStatGrid").innerHTML = cards.map(card => `
    <div class="stat-card ${card.accent}">
      <div class="stat-card-label">${card.label}</div>
      <div class="stat-card-value">${card.value}</div>
    </div>
  `).join("");
}

function renderSubmissionAnalyticsChart() {
  new Chart(document.getElementById("analyticsSubmissionChart"), {
    type: "line",
    data: {
      labels: submissionStats.labels,
      datasets: [
        {
          label: "On-time",
          data: submissionStats.onTime,
          borderColor: "#006936",
          backgroundColor: "rgba(0,105,54,0.1)",
          tension: 0.35,
          fill: true,
        },
        {
          label: "Late",
          data: submissionStats.late,
          borderColor: "#E0A800",
          backgroundColor: "rgba(224,168,0,0.1)",
          tension: 0.35,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: "bottom", labels: { font: { size: 11 } } } },
      scales: { y: { beginAtZero: true, grid: { color: "#F0F2F0" } }, x: { grid: { display: false } } },
    },
  });
}

function renderCompletionChart() {
  new Chart(document.getElementById("completionChart"), {
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

function renderWorkloadChart() {
  new Chart(document.getElementById("analyticsWorkloadChart"), {
    type: "bar",
    data: {
      labels: adviserWorkload.map(entry => entry.adviser),
      datasets: [{
        label: "Assigned Groups",
        data: adviserWorkload.map(entry => entry.groups),
        backgroundColor: "#006936",
        borderRadius: 4,
      }],
    },
    options: {
      indexAxis: "y",
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: "#F0F2F0" } }, y: { grid: { display: false } } },
    },
  });
}

function renderProgressReport() {
  const rows = groups.map(group => `
    <tr>
      <td>${group.name}<br><small style="color:var(--text-light);">${group.title}</small></td>
      <td>${getAdviserName(group.adviserId)}</td>
      <td>${group.batch}</td>
      <td>${group.milestonesDone} / ${group.milestonesTotal}</td>
      <td>
        <div class="progress" style="height:18px; min-width:90px;">
          <div class="progress-bar" style="width:${group.progress}%; font-size:0.7rem;">${group.progress}%</div>
        </div>
      </td>
      <td>${statusBadge(group.status)}</td>
    </tr>
  `).join("");

  document.getElementById("progressReportBody").innerHTML = rows;
}

function simulateExport(format) {
  showToast(`Preparing ${format} export...`, "info");
  simulateRequest(() => {
    showToast(`Report exported as ${format} (simulated — Phase 1 frontend only).`, "success");
  }, { delay: 900 });
}
