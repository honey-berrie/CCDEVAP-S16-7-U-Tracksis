const analyticsColors = {
    approved: "#16a34a",
    inReview: "#f59e0b",
    rejected: "#dc2626",
    revision: "#3b82f6",
    bar: "#182e59",
    gridLine: "#e9e9e9",
};

let submissionsOverTimeChart, statusBreakdownChart, groupProgressChart;

const analyticsState = { group: "", adviser: "", status: "" };

document.addEventListener("DOMContentLoaded", () => {
    loadAnalytics();
    bindAnalyticsFilters();
});

function bindAnalyticsFilters() {
    document.getElementById("filterGroup").addEventListener("change", (e) => {
        analyticsState.group = e.target.value;
        loadAnalytics(true);
    });
    document.getElementById("filterAdviser").addEventListener("change", (e) => {
        analyticsState.adviser = e.target.value;
        loadAnalytics(true);
    });
    document.getElementById("filterStatus").addEventListener("change", (e) => {
        analyticsState.status = e.target.value;
        loadAnalytics(true);
    });
}

function loadAnalytics(keepFilterOptions) {
    const params = new URLSearchParams({
        group: analyticsState.group,
        adviser: analyticsState.adviser,
        status: analyticsState.status,
    });

    fetch(`../configuration/adm-analytics-data.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            renderSummary(data.summary);
            renderSubmissionsOverTime(data.submissionsOverTime);
            renderStatusBreakdown(data.statusBreakdown);
            renderGroupProgress(data.groupProgress);
            if (!keepFilterOptions) {
                populateFilterOptions(data.filters);
            }
        })
        .catch(err => console.error("Failed to load analytics:", err));
}

function renderSummary(summary) {
    if (!summary) return;
    document.getElementById("statOnTimeRate").textContent = `${summary.onTimeRate}%`;
    document.getElementById("statActiveGroups").textContent = summary.activeGroups;
    document.getElementById("statArchived").textContent = summary.archivedTheses;
    document.getElementById("statAvgProgress").textContent = `${summary.avgProgress}%`;
}

function populateFilterOptions(filters) {
    if (!filters) return;
    const groupSelect = document.getElementById("filterGroup");
    groupSelect.innerHTML = `<option value="">All Groups</option>` +
        filters.groups.map(g => `<option value="${g.id}">${escapeHtml(g.name)}</option>`).join("");

    const adviserSelect = document.getElementById("filterAdviser");
    adviserSelect.innerHTML = `<option value="">All Advisers</option>` +
        filters.advisers.map(a => `<option value="${a.id}">${escapeHtml(a.name)}</option>`).join("");
}

function renderSubmissionsOverTime(data) {
    const ctx = document.getElementById("submissionsOverTimeChart").getContext("2d");
    if (submissionsOverTimeChart) submissionsOverTimeChart.destroy();
    submissionsOverTimeChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: data.labels,
            datasets: [{
                label: "Submissions",
                data: data.values,
                borderColor: analyticsColors.bar,
                backgroundColor: "rgba(24, 46, 89, 0.1)",
                fill: true,
                tension: 0.3,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: analyticsColors.gridLine } },
            },
        },
    });
}

function renderStatusBreakdown(data) {
    const ctx = document.getElementById("statusBreakdownChart").getContext("2d");
    if (statusBreakdownChart) statusBreakdownChart.destroy();
    statusBreakdownChart = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: data.labels.map(l => l.replace(/-/g, " ")),
            datasets: [{
                data: data.values,
                backgroundColor: [analyticsColors.inReview, analyticsColors.approved, analyticsColors.rejected, analyticsColors.revision],
                borderColor: "#fff",
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            cutout: "60%",
            plugins: { legend: { position: "bottom", labels: { boxWidth: 14 } } },
        },
    });
}

function renderGroupProgress(data) {
    const ctx = document.getElementById("groupProgressChart").getContext("2d");
    if (groupProgressChart) groupProgressChart.destroy();
    groupProgressChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [{
                label: "Avg. Progress %",
                data: data.values,
                backgroundColor: analyticsColors.bar,
                borderRadius: 6,
                barThickness: 18,
            }],
        },
        options: {
            indexAxis: "y",
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, max: 100, grid: { color: analyticsColors.gridLine } },
                y: { grid: { display: false } },
            },
        },
    });
}

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
