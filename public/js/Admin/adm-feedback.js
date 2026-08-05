const feedbackState = {
    search: "",
    sort: "created_at",
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
    loadFeedback();
    bindFeedbackToolbarEvents();
    bindFeedbackModalEvents();
});

function loadFeedback() {
    const params = new URLSearchParams({
        search: feedbackState.search,
        sort: feedbackState.sort,
        dir: feedbackState.dir,
        page: feedbackState.page,
        perPage: feedbackState.perPage,
    });

    fetch(`../../Controllers/Admin/adm-feedback-data.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            renderFeedbackTable(data.feedback);
            renderFeedbackPagination(data.total, data.page, data.perPage);
        })
        .catch(err => {
            console.error("Failed to load feedback:", err);
            document.getElementById("feedbackTableBody").innerHTML =
                `<tr><td colspan="6" class="empty-state">Could not load feedback.</td></tr>`;
        });
}

function renderFeedbackTable(feedback) {
    const body = document.getElementById("feedbackTableBody");

    if (!feedback || feedback.length === 0) {
        body.innerHTML = `<tr><td colspan="6" class="empty-state">No feedback has been submitted yet.</td></tr>`;
        window.__feedbackCache = [];
        return;
    }

    body.innerHTML = feedback.map(f => `
        <tr>
            <td>${escapeHtml(f.authorName)}${f.authorEmail ? "<br><span class=\"text-light-small\">" + escapeHtml(f.authorEmail) + "</span>" : ""}</td>
            <td>${escapeHtml(f.groupName)}</td>
            <td><span class="feedback-message-preview">${escapeHtml(f.message)}</span></td>
            <td>${feedbackRelatedLabel(f)}</td>
            <td>${formatDate(f.createdAt)}</td>
            <td>
                <button type="button" class="btn-secondary-admin" onclick="openFeedbackDetail(${f.id})">View</button>
            </td>
        </tr>
    `).join("");

    window.__feedbackCache = feedback;
}

function renderFeedbackPagination(total, page, perPage) {
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const start = total === 0 ? 0 : (page - 1) * perPage + 1;
    const end = Math.min(total, page * perPage);

    document.getElementById("feedbackPaginationSummary").textContent = `Showing ${start}-${end} of ${total}`;

    const controls = document.getElementById("feedbackPaginationControls");
    let html = `<button ${page <= 1 ? "disabled" : ""} onclick="goToFeedbackPage(${page - 1})">Prev</button>`;

    for (let p = 1; p <= totalPages; p++) {
        html += `<button class="${p === page ? "active" : ""}" onclick="goToFeedbackPage(${p})">${p}</button>`;
    }

    html += `<button ${page >= totalPages ? "disabled" : ""} onclick="goToFeedbackPage(${page + 1})">Next</button>`;
    controls.innerHTML = html;
}

function goToFeedbackPage(page) {
    feedbackState.page = page;
    loadFeedback();
}

function bindFeedbackToolbarEvents() {
    let searchTimer;
    document.getElementById("feedbackSearchInput").addEventListener("input", (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            feedbackState.search = e.target.value;
            feedbackState.page = 1;
            loadFeedback();
        }, 300);
    });

    document.getElementById("feedbackSortSelect").addEventListener("change", (e) => {
        const [sort, dir] = e.target.value.split(":");
        feedbackState.sort = sort;
        feedbackState.dir = dir;
        feedbackState.page = 1;
        loadFeedback();
    });

    document.getElementById("feedbackRowsPerPage").addEventListener("change", (e) => {
        feedbackState.perPage = parseInt(e.target.value, 10);
        feedbackState.page = 1;
        loadFeedback();
    });
}

function bindFeedbackModalEvents() {
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener("click", () => {
            document.getElementById(btn.dataset.closeModal).classList.remove("show");
        });
    });
}

function openFeedbackDetail(id) {
    const feedback = (window.__feedbackCache || []).find(f => f.id === id);
    if (!feedback) return;

    document.getElementById("feedbackDetailAuthor").textContent =
        feedback.authorEmail ? `${feedback.authorName} (${feedback.authorEmail})` : feedback.authorName;
    document.getElementById("feedbackDetailGroup").textContent =
        feedback.thesisTitle ? `${feedback.groupName} — ${feedback.thesisTitle}` : feedback.groupName;
    document.getElementById("feedbackDetailDate").textContent = formatDate(feedback.createdAt);
    document.getElementById("feedbackDetailMessage").textContent = feedback.message;

    const relatedWrap = document.getElementById("feedbackDetailRelatedWrap");
    const related = feedbackRelatedLabel(feedback, true);
    if (related) {
        document.getElementById("feedbackDetailRelated").textContent = related;
        relatedWrap.style.display = "";
    } else {
        relatedWrap.style.display = "none";
    }

    document.getElementById("feedbackModalOverlay").classList.add("show");
}

function feedbackRelatedLabel(feedback, plain) {
    if (!feedback.submissionTitle && !feedback.documentType) return plain ? "" : "--";

    const type = feedback.documentType ? documentTypeLabel(feedback.documentType) : "";
    const title = feedback.submissionTitle || "";

    const label = [type, title].filter(Boolean).join(" · ");
    return plain ? label : escapeHtml(label);
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
