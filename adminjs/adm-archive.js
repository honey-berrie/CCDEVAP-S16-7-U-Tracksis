/* ==========================================================================
   Archive — teams ready to archive, paginated archived list, detail modal,
   archive/restore actions.
   ========================================================================== */

const archiveState = { search: "", page: 1, perPage: 9 };

document.addEventListener("DOMContentLoaded", () => {
    loadReadyToArchive();
    loadArchivedTeams();
    bindArchiveToolbar();

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener("click", () => document.getElementById(btn.dataset.closeModal).classList.remove("show"));
    });
});

function bindArchiveToolbar() {
    let searchTimer;
    document.getElementById("archiveSearchInput").addEventListener("input", (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            archiveState.search = e.target.value;
            archiveState.page = 1;
            loadArchivedTeams();
        }, 300);
    });
}

function loadReadyToArchive() {
    fetch("../configuration/adm-archive-data.php?section=ready")
        .then(res => res.json())
        .then(data => renderReadyToArchive(data.ready))
        .catch(err => console.error("Failed to load ready-to-archive groups:", err));
}

function renderReadyToArchive(items) {
    const container = document.getElementById("readyToArchiveList");
    if (!items || items.length === 0) {
        container.innerHTML = `<p class="empty-state">No thesis groups are ready to archive right now.</p>`;
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="panel-list-item">
            <div>
                <div class="item-title">${escapeHtml(item.groupName)}</div>
                <div class="item-sub">${escapeHtml(item.thesisTitle)} • Adviser: ${escapeHtml(item.adviser)}</div>
            </div>
            <button type="button" class="btn-primary-admin" onclick="archiveTeam(${item.id})">Archive</button>
        </div>
    `).join("");
}

function archiveTeam(id) {
    if (!confirm("Archive this thesis group? It will move to institutional records.")) return;

    const formData = new FormData();
    formData.append("action", "archive");
    formData.append("id", id);

    fetch("../configuration/adm-archive-actions.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || "Could not archive this group.");
                return;
            }
            loadReadyToArchive();
            loadArchivedTeams();
        });
}

function loadArchivedTeams() {
    const params = new URLSearchParams({
        section: "archived",
        search: archiveState.search,
        page: archiveState.page,
        perPage: archiveState.perPage,
    });

    fetch(`../configuration/adm-archive-data.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            renderArchiveGrid(data.archived);
            renderArchivePagination(data.total, data.page, data.perPage);
        })
        .catch(err => {
            console.error("Failed to load archived theses:", err);
            document.getElementById("archiveGrid").innerHTML = `<p class="empty-state">Could not load archived theses.</p>`;
        });
}

function renderArchiveGrid(items) {
    const grid = document.getElementById("archiveGrid");
    if (!items || items.length === 0) {
        grid.innerHTML = `<p class="empty-state">No archived theses match your search.</p>`;
        return;
    }

    grid.innerHTML = items.map(item => `
        <div class="record-card">
            <div class="record-header">
                <span class="record-title">${escapeHtml(item.groupName)}</span>
                <span class="status-badge status-archived">Archived</span>
            </div>
            <div class="record-sub">${escapeHtml(item.thesisTitle)}</div>
            <div class="record-sub">Adviser: ${escapeHtml(item.adviser)}</div>
            <div class="record-sub">Archived ${formatDate(item.archivedAt)}</div>
            <div class="record-footer">
                <button type="button" class="btn-secondary-admin" onclick="openArchiveDetail(${item.id})">Details</button>
                <button type="button" class="btn-primary-admin" onclick="restoreTeam(${item.id})">Restore</button>
            </div>
        </div>
    `).join("");
}

function restoreTeam(id) {
    if (!confirm("Restore this thesis group to active status?")) return;

    const formData = new FormData();
    formData.append("action", "restore");
    formData.append("id", id);

    fetch("../configuration/adm-archive-actions.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || "Could not restore this group.");
                return;
            }
            loadReadyToArchive();
            loadArchivedTeams();
        });
}

function renderArchivePagination(total, page, perPage) {
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const start = total === 0 ? 0 : (page - 1) * perPage + 1;
    const end = Math.min(total, page * perPage);
    document.getElementById("archivePaginationSummary").textContent = `Showing ${start}-${end} of ${total}`;

    const controls = document.getElementById("archivePaginationControls");
    let html = `<button ${page <= 1 ? "disabled" : ""} onclick="goToArchivePage(${page - 1})">Prev</button>`;
    for (let p = 1; p <= totalPages; p++) {
        html += `<button class="${p === page ? "active" : ""}" onclick="goToArchivePage(${p})">${p}</button>`;
    }
    html += `<button ${page >= totalPages ? "disabled" : ""} onclick="goToArchivePage(${page + 1})">Next</button>`;
    controls.innerHTML = html;
}

function goToArchivePage(page) {
    archiveState.page = page;
    loadArchivedTeams();
}

/* --- Detail modal (shares markup shape with Thesis Records) ----------------- */

function openArchiveDetail(id) {
    const overlay = document.getElementById("archiveModalOverlay");
    const body = document.getElementById("archiveModalBody");
    body.innerHTML = `<p class="empty-state">Loading…</p>`;
    overlay.classList.add("show");

    fetch(`../configuration/adm-archive-data.php?detail=${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("archiveModalTitle").textContent = data.groupName;
            body.innerHTML = renderArchiveDetail(data);
        })
        .catch(() => {
            body.innerHTML = `<p class="empty-state">Could not load this record.</p>`;
        });
}

function renderArchiveDetail(data) {
    const membersHtml = data.members.length
        ? data.members.map(m => `<span class="member-chip">${escapeHtml(m.name)} — ${escapeHtml(m.role)}</span>`).join("")
        : `<p class="empty-state">No members recorded.</p>`;

    const milestonesHtml = data.milestones.length
        ? data.milestones.map(m => `
            <div class="timeline-item">
                <div class="timeline-progress">
                    <div class="item-title">${escapeHtml(m.name)}</div>
                    <div class="item-sub">Completed ${formatDate(m.completedAt)}</div>
                </div>
                <span class="status-badge status-${m.status}">${m.status.replace("-", " ")}</span>
            </div>
        `).join("")
        : `<p class="empty-state">No milestones recorded.</p>`;

    const submissionsHtml = data.submissions.length
        ? `<table class="data-table">
             <thead><tr><th>Document</th><th>Type</th><th>Uploaded By</th><th>Status</th><th>Date</th></tr></thead>
             <tbody>
               ${data.submissions.map(s => `
                 <tr>
                   <td>${escapeHtml(s.title)}</td>
                   <td>${escapeHtml(s.documentType.replace(/-/g, " "))}</td>
                   <td>${escapeHtml(s.uploadedBy || "--")}</td>
                   <td><span class="status-badge status-${s.status}">${s.status.replace(/-/g, " ")}</span></td>
                   <td>${formatDate(s.uploadedAt)}</td>
                 </tr>
               `).join("")}
             </tbody>
           </table>`
        : `<p class="empty-state">No submissions on file.</p>`;

    return `
        <div class="detail-section">
            <h4>Defense Details</h4>
            <p class="record-sub">${escapeHtml(data.thesisTitle)}</p>
            <p class="record-sub">Adviser: ${escapeHtml(data.adviser)} ${data.adviserEmail ? "(" + escapeHtml(data.adviserEmail) + ")" : ""}</p>
            <p class="record-sub">Academic Year: ${escapeHtml(data.academicYear || "--")} • Defense: ${formatDate(data.defenseDate)}</p>
            <p class="record-sub">Archived: ${formatDate(data.archivedAt)}</p>
        </div>
        <div class="detail-section">
            <h4>Student Members &amp; Roles</h4>
            <div class="member-chip-list">${membersHtml}</div>
        </div>
        <div class="detail-section">
            <h4>Milestones</h4>
            <div class="timeline">${milestonesHtml}</div>
        </div>
        <div class="detail-section">
            <h4>Submission History</h4>
            <div class="table-responsive">${submissionsHtml}</div>
        </div>
    `;
}

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function formatDate(dateStr) {
    if (!dateStr) return "--";
    return new Date(dateStr).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}
