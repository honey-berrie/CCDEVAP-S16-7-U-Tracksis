document.addEventListener("DOMContentLoaded", () => {
    fetch("../configuration/adm-dashboard-data.php")
        .then(res => {
            if (!res.ok) throw new Error("Failed to load dashboard data.");
            return res.json();
        })
        .then(data => {
            renderGreeting(data.currentUser);
            renderStats(data.stats);
            renderPendingApprovals(data.pendingApprovals);
            renderRecentActivity(data.recentActivity);
            renderRecentSubmissions(data.recentSubmissions);
        })
        .catch(err => console.error("Dashboard widgets error:", err));
});

function renderGreeting(user) {
    const hour = new Date().getHours();
    let greeting = "Good Evening";
    if (hour < 12) greeting = "Good Morning";
    else if (hour < 18) greeting = "Good Afternoon";

    const fullName = user && user.firstname
        ? `${user.firstname} ${user.lastname || ""}`.trim()
        : "Admin";

    document.getElementById("partDay").textContent = `${greeting},`;
    document.getElementById("namePerson").textContent = fullName;
}

function renderStats(stats) {
    if (!stats) return;
    document.getElementById("statStudents").textContent = stats.students;
    document.getElementById("statAdvisers").textContent = stats.advisers;
    document.getElementById("statActiveGroups").textContent = stats.activeGroups;
    document.getElementById("statApproved").textContent = stats.approvedTheses;
    document.getElementById("statPendingArchive").textContent = stats.pendingArchive;
    document.getElementById("statAvgProgress").textContent = `${stats.avgProgress}%`;
}

function renderPendingApprovals(items) {
    const container = document.getElementById("pendingApprovalsList");
    if (!items || items.length === 0) {
        container.innerHTML = `<p class="empty-state">No groups are currently awaiting review.</p>`;
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="panel-list-item">
            <div>
                <div class="item-title">${escapeHtml(item.groupName)}</div>
                <div class="item-sub">${escapeHtml(item.thesisTitle)} • Adviser: ${escapeHtml(item.adviser)}</div>
            </div>
            <span class="status-badge status-approved">${item.progress}%</span>
        </div>
    `).join("");
}

function renderRecentActivity(items) {
    const container = document.getElementById("recentActivityList");
    if (!items || items.length === 0) {
        container.innerHTML = `<p class="empty-state">No recent activity yet.</p>`;
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="panel-list-item">
            <div>
                <div class="item-title">${escapeHtml(item.description)}</div>
            </div>
            <span class="item-sub">${timeAgo(item.time)}</span>
        </div>
    `).join("");
}

function renderRecentSubmissions(items) {
    const body = document.getElementById("recentSubmissionsBody");
    if (!items || items.length === 0) {
        body.innerHTML = `<tr><td colspan="5" class="empty-state">No submissions yet.</td></tr>`;
        return;
    }

    body.innerHTML = items.map(item => `
        <tr>
            <td>${escapeHtml(item.groupName)}</td>
            <td>${escapeHtml(item.title)}</td>
            <td>${escapeHtml(formatDocType(item.documentType))}</td>
            <td><span class="status-badge status-${item.status}">${formatStatus(item.status)}</span></td>
            <td>${formatDate(item.uploadedAt)}</td>
        </tr>
    `).join("");
}



function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

function truncate(str, len) {
    if (!str) return "";
    return str.length > len ? str.slice(0, len) + "…" : str;
}

function formatDate(dateStr) {
    if (!dateStr) return "--";
    const d = new Date(dateStr);
    return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}

function formatStatus(status) {
    return status.replace(/-/g, " ");
}

function formatDocType(type) {
    return type.replace(/-/g, " ");
}

function timeAgo(dateStr) {
    const diffMs = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diffMs / 60000);
    if (mins < 1) return "just now";
    if (mins < 60) return `${mins}m ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
}
