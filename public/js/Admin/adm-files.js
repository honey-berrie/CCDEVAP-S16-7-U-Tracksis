const filesState = {
    search: "",
    documentType: "",
    sort: "uploaded_at",
    dir: "desc",
    page: 1,
    perPage: 10,
};

const DOCUMENT_TYPE_LABELS = {
    "title-proposal": "Title Proposal",
    "chapter-1": "Chapter 1",
    "chapter-2": "Chapter 2",
    "chapter-3": "Chapter 3",
    "chapter-4": "Chapter 4",
    "chapter-5": "Chapter 5",
    "final-thesis": "Final Thesis",
    "revision": "Revision",
    "other": "Other",
};

document.addEventListener("DOMContentLoaded", () => {
    loadFiles();
    bindFilesToolbarEvents();
});

function loadFiles() {
    const params = new URLSearchParams({
        search: filesState.search,
        documentType: filesState.documentType,
        sort: filesState.sort,
        dir: filesState.dir,
        page: filesState.page,
        perPage: filesState.perPage,
    });

    fetch(`/admin/data/files?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            renderFilesTable(data.files);
            renderTypeFilterOptions(data.typeCounts);
            renderFilesPagination(data.total, data.page, data.perPage);
        })
        .catch(err => {
            console.error("Failed to load uploaded files:", err);
            document.getElementById("filesTableBody").innerHTML =
                `<tr><td colspan="7" class="empty-state">Could not load uploaded files.</td></tr>`;
        });
}

function renderFilesTable(files) {
    const body = document.getElementById("filesTableBody");
    if (!files || files.length === 0) {
        body.innerHTML = `<tr><td colspan="7" class="empty-state">No uploaded files match your filters.</td></tr>`;
        return;
    }

    body.innerHTML = files.map(f => `
        <tr>
            <td>${escapeHtml(f.fileName)}</td>
            <td>${escapeHtml(f.groupName)}</td>
            <td>${escapeHtml(f.uploadedBy || "--")}</td>
            <td>${escapeHtml(documentTypeLabel(f.documentType))}${f.milestone ? " · " + escapeHtml(f.milestone) : ""}</td>
            <td>${escapeHtml(f.adviser || "--")}</td>
            <td>${formatDate(f.uploadedAt)}</td>
            <td>
                <a class="btn-secondary-admin" href="/admin/data/files-download?id=${f.id}" target="_blank" rel="noopener">View</a>
                <a class="btn-primary-admin" href="/admin/data/files-download?id=${f.id}&download=1">Download</a>
            </td>
        </tr>
    `).join("");
}

function renderTypeFilterOptions(typeCounts) {
    const select = document.getElementById("fileTypeFilter");
    if (!typeCounts || select.dataset.populated === "true") return;

    select.innerHTML = `<option value="">All Types</option>` + Object.keys(typeCounts).map(type =>
        `<option value="${type}">${escapeHtml(documentTypeLabel(type))} (${typeCounts[type]})</option>`
    ).join("");
    select.value = filesState.documentType;
    select.dataset.populated = "true";
}

function renderFilesPagination(total, page, perPage) {
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const start = total === 0 ? 0 : (page - 1) * perPage + 1;
    const end = Math.min(total, page * perPage);

    document.getElementById("filesPaginationSummary").textContent = `Showing ${start}-${end} of ${total}`;

    const controls = document.getElementById("filesPaginationControls");
    let html = `<button ${page <= 1 ? "disabled" : ""} onclick="goToFilesPage(${page - 1})">Prev</button>`;

    for (let p = 1; p <= totalPages; p++) {
        html += `<button class="${p === page ? "active" : ""}" onclick="goToFilesPage(${p})">${p}</button>`;
    }

    html += `<button ${page >= totalPages ? "disabled" : ""} onclick="goToFilesPage(${page + 1})">Next</button>`;
    controls.innerHTML = html;
}

function goToFilesPage(page) {
    filesState.page = page;
    loadFiles();
}

function bindFilesToolbarEvents() {
    let searchTimer;
    document.getElementById("fileSearchInput").addEventListener("input", (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            filesState.search = e.target.value;
            filesState.page = 1;
            loadFiles();
        }, 300);
    });

    document.getElementById("fileTypeFilter").addEventListener("change", (e) => {
        filesState.documentType = e.target.value;
        filesState.page = 1;
        loadFiles();
    });

    document.getElementById("fileSortSelect").addEventListener("change", (e) => {
        const [sort, dir] = e.target.value.split(":");
        filesState.sort = sort;
        filesState.dir = dir;
        filesState.page = 1;
        loadFiles();
    });

    document.getElementById("filesRowsPerPage").addEventListener("change", (e) => {
        filesState.perPage = parseInt(e.target.value, 10);
        filesState.page = 1;
        loadFiles();
    });
}

function documentTypeLabel(type) {
    return DOCUMENT_TYPE_LABELS[type] || type;
}

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function formatDate(dateStr) {
    if (!dateStr) return "--";
    return new Date(dateStr).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}
