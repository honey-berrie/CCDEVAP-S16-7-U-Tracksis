/* ==========================================================================
   Thesis Records — search, adviser/status filters, card grid, pagination,
   and a read-only detail modal (timeline, submissions, members, adviser).
   ========================================================================== */

const recordsState = {
    search: "",
    adviser: "",
    status: "",
    page: 1,
    perPage: 9,
};

document.addEventListener("DOMContentLoaded", () => {
    loadRecords();
    bindRecordsToolbar();
    bindTeamModalEvents();

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener("click", () => document.getElementById(btn.dataset.closeModal).classList.remove("show"));
    });

    // Dashboard's "Create Thesis Group" quick action links here and expects
    // the modal to open automatically.
    const params = new URLSearchParams(window.location.search);
    if (params.get("action") === "create-group") {
        openCreateTeamModal();
    }
});

function loadRecords() {
    const params = new URLSearchParams({
        search: recordsState.search,
        adviser: recordsState.adviser,
        status: recordsState.status,
        page: recordsState.page,
        perPage: recordsState.perPage,
    });

    fetch(`../configuration/adm-records-data.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            renderRecordsGrid(data.records);
            renderRecordsPagination(data.total, data.page, data.perPage);
            populateAdviserFilter(data.advisers);
        })
        .catch(err => {
            console.error("Failed to load thesis records:", err);
            document.getElementById("recordsGrid").innerHTML = `<p class="empty-state">Could not load thesis records.</p>`;
        });
}

function renderRecordsGrid(records) {
    const grid = document.getElementById("recordsGrid");
    if (!records || records.length === 0) {
        grid.innerHTML = `<p class="empty-state">No thesis groups match your filters.</p>`;
        return;
    }

    grid.innerHTML = records.map(r => `
        <div class="record-card">
            <div class="record-header">
                <span class="record-title">${escapeHtml(r.groupName)}</span>
                <span class="status-badge status-${statusClass(r.status)}">${statusLabel(r.status)}</span>
            </div>
            <div class="record-sub">${escapeHtml(r.thesisTitle)}</div>
            <div class="progress-row">
                <div class="progress-track"><div class="progress-fill" style="width:${r.progress}%"></div></div>
                <span>${r.progress}%</span>
            </div>
            <div class="record-footer">
                <span class="record-sub">Adviser: ${escapeHtml(r.adviser)}</span>
                <button type="button" class="btn-secondary-admin" onclick="openRecordDetail(${r.id})">View</button>
            </div>
        </div>
    `).join("");
}

function statusClass(status) {
    if (status === "completed") return "approved";
    if (status === "behind") return "rejected";
    return "in-progress";
}

function statusLabel(status) {
    return status.replace("-", " ");
}

function renderRecordsPagination(total, page, perPage) {
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const start = total === 0 ? 0 : (page - 1) * perPage + 1;
    const end = Math.min(total, page * perPage);
    document.getElementById("recordsPaginationSummary").textContent = `Showing ${start}-${end} of ${total}`;

    const controls = document.getElementById("recordsPaginationControls");
    let html = `<button ${page <= 1 ? "disabled" : ""} onclick="goToRecordsPage(${page - 1})">Prev</button>`;
    for (let p = 1; p <= totalPages; p++) {
        html += `<button class="${p === page ? "active" : ""}" onclick="goToRecordsPage(${p})">${p}</button>`;
    }
    html += `<button ${page >= totalPages ? "disabled" : ""} onclick="goToRecordsPage(${page + 1})">Next</button>`;
    controls.innerHTML = html;
}

function goToRecordsPage(page) {
    recordsState.page = page;
    loadRecords();
}

function populateAdviserFilter(advisers) {
    const select = document.getElementById("adviserFilter");
    const current = select.value;
    select.innerHTML = `<option value="">All Advisers</option>` + (advisers || []).map(a =>
        `<option value="${a.id}">${escapeHtml(a.name)}</option>`
    ).join("");
    select.value = current;
}

function bindRecordsToolbar() {
    let searchTimer;
    document.getElementById("recordsSearchInput").addEventListener("input", (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            recordsState.search = e.target.value;
            recordsState.page = 1;
            loadRecords();
        }, 300);
    });

    document.getElementById("adviserFilter").addEventListener("change", (e) => {
        recordsState.adviser = e.target.value;
        recordsState.page = 1;
        loadRecords();
    });

    document.querySelectorAll("#statusFilterChips .filter-chip").forEach(chip => {
        chip.addEventListener("click", () => {
            document.querySelectorAll("#statusFilterChips .filter-chip").forEach(c => c.classList.remove("active"));
            chip.classList.add("active");
            recordsState.status = chip.dataset.status;
            recordsState.page = 1;
            loadRecords();
        });
    });
}

/* --- Detail modal ------------------------------------------------------------ */

function openRecordDetail(id) {
    const overlay = document.getElementById("recordModalOverlay");
    const body = document.getElementById("recordModalBody");
    body.innerHTML = `<p class="empty-state">Loading…</p>`;
    overlay.classList.add("show");

    fetch(`../configuration/adm-records-data.php?detail=${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("recordModalTitle").textContent = data.groupName;
            body.innerHTML = renderRecordDetail(data);
        })
        .catch(() => {
            body.innerHTML = `<p class="empty-state">Could not load this record.</p>`;
        });
}

function renderRecordDetail(data) {
    const membersHtml = data.members.length
        ? data.members.map(m => `<span class="member-chip">${escapeHtml(m.name)} — ${escapeHtml(m.role)}</span>`).join("")
        : `<p class="empty-state">No members recorded.</p>`;

    const milestonesHtml = data.milestones.length
        ? data.milestones.map(m => `
            <div class="timeline-item">
                <div class="timeline-progress">
                    <div class="item-title">${escapeHtml(m.name)}</div>
                    <div class="progress-track"><div class="progress-fill" style="width:${m.progress}%"></div></div>
                    <div class="item-sub">Due ${formatDate(m.dueDate)}</div>
                </div>
                <span class="status-badge status-${m.status}">${m.status.replace("-", " ")}</span>
            </div>
        `).join("")
        : `<p class="empty-state">No milestones set yet.</p>`;

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
        : `<p class="empty-state">No submissions yet.</p>`;

    return `
        <div class="detail-section">
            <h4>Overview</h4>
            <p class="record-sub">${escapeHtml(data.thesisTitle)}</p>
            <p class="record-sub">Adviser: ${escapeHtml(data.adviser)} ${data.adviserEmail ? "(" + escapeHtml(data.adviserEmail) + ")" : ""}</p>
            <p class="record-sub">Academic Year: ${escapeHtml(data.academicYear || "--")} • Defense: ${formatDate(data.defenseDate)}</p>
            ${data.abstract ? `<p class="record-sub">${escapeHtml(data.abstract)}</p>` : ""}
        </div>
        <div class="detail-section">
            <h4>Student Members</h4>
            <div class="member-chip-list">${membersHtml}</div>
        </div>
        <div class="detail-section">
            <h4>Milestone Timeline</h4>
            <div class="timeline">${milestonesHtml}</div>
        </div>
        <div class="detail-section">
            <h4>Submission History</h4>
            <div class="table-responsive">${submissionsHtml}</div>
        </div>
    `;
}

/* --- Create Thesis Group modal ------------------------------------------------ */

function bindTeamModalEvents() {
    document.getElementById("openCreateTeamModal").addEventListener("click", openCreateTeamModal);
    document.getElementById("saveTeamBtn").addEventListener("click", saveTeam);
}

function openCreateTeamModal() {
    document.getElementById("teamForm").reset();
    document.getElementById("teamFormError").classList.remove("show");
    document.getElementById("teamModalOverlay").classList.add("show");
    loadTeamModalLookups();
}

function loadTeamModalLookups() {
    // Reuses adm-users-data.php, which already returns the adviser list and
    // unassigned-student checklist alongside the users table payload.
    fetch("../configuration/adm-users-data.php?perPage=5")
        .then(res => res.json())
        .then(data => {
            populateAdviserSelect(data.advisers);
            populateMembersChecklist(data.unassignedStudents);
        })
        .catch(() => {
            document.getElementById("teamMembersList").innerHTML =
                `<p class="empty-state">Could not load students.</p>`;
        });
}

function populateAdviserSelect(advisers) {
    const select = document.getElementById("teamAdviser");
    select.innerHTML = `<option value="">Unassigned</option>` + (advisers || []).map(a =>
        `<option value="${a.id}">${escapeHtml(a.name)} (${a.load} groups)</option>`
    ).join("");
}

function populateMembersChecklist(students) {
    const container = document.getElementById("teamMembersList");
    if (!students || students.length === 0) {
        container.innerHTML = `<p class="empty-state">No unassigned students available.</p>`;
        return;
    }

    container.innerHTML = students.map(s => `
        <label>
            <input type="checkbox" value="${s.id}" class="team-member-checkbox">
            ${escapeHtml(s.name)}
        </label>
    `).join("");
}

function saveTeam() {
    const groupName = document.getElementById("teamGroupName").value.trim();
    const thesisTitle = document.getElementById("teamThesisTitle").value.trim();
    const errorEl = document.getElementById("teamFormError");

    if (!groupName || !thesisTitle) {
        errorEl.textContent = "Group name and thesis title are required.";
        errorEl.classList.add("show");
        return;
    }

    const memberIds = Array.from(document.querySelectorAll(".team-member-checkbox:checked")).map(cb => cb.value);

    const formData = new FormData();
    formData.append("action", "create_team");
    formData.append("group_name", groupName);
    formData.append("thesis_title", thesisTitle);
    formData.append("abstract", document.getElementById("teamAbstract").value.trim());
    formData.append("adviser_id", document.getElementById("teamAdviser").value);
    formData.append("academic_year", document.getElementById("teamAcademicYear").value.trim());
    formData.append("defense_date", document.getElementById("teamDefenseDate").value);
    memberIds.forEach(id => formData.append("member_ids[]", id));

    fetch("../configuration/adm-users-actions.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                errorEl.textContent = data.error || "Could not create the group.";
                errorEl.classList.add("show");
                return;
            }
            document.getElementById("teamModalOverlay").classList.remove("show");
            document.getElementById("teamForm").reset();
            recordsState.page = 1;
            loadRecords();
        })
        .catch(() => {
            errorEl.textContent = "Network error. Please try again.";
            errorEl.classList.add("show");
        });
}

/* --- Shared helpers ------------------------------------------------------------ */

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function formatDate(dateStr) {
    if (!dateStr) return "--";
    return new Date(dateStr).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}
