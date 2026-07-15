/* ==========================================================================
   Announcements — single list (no drafts, no audience -- the schema no
   longer supports either), compose, edit, delete, preview.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
    loadAnnouncements();
    bindAnnouncementForm();

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener("click", () => document.getElementById(btn.dataset.closeModal).classList.remove("show"));
    });
});

function loadAnnouncements() {
    fetch("../configuration/adm-announcements-data.php")
        .then(res => res.json())
        .then(data => {
            renderList(data.announcements);
            document.getElementById("announcementsCount").textContent = `${data.announcements.length} total`;
            window.__announcementsCache = data.announcements;
        })
        .catch(err => console.error("Failed to load announcements:", err));
}

function renderList(items) {
    const container = document.getElementById("announcementsList");
    if (!items || items.length === 0) {
        container.innerHTML = `<p class="empty-state">No announcements posted yet.</p>`;
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="announcement-item">
            <div class="announcement-top">
                <div>
                    <strong>${escapeHtml(item.title)}</strong>
                </div>
                <div class="announcement-actions">
                    <button type="button" class="btn-secondary-admin" onclick="editAnnouncement(${item.id})">Edit</button>
                    <button type="button" class="btn-danger-admin" onclick="deleteAnnouncement(${item.id})">Delete</button>
                </div>
            </div>
            <div class="item-sub">${escapeHtml(item.message)}</div>
            <div class="item-sub">By ${escapeHtml(item.author)} • ${formatDate(item.createdAt)}</div>
        </div>
    `).join("");
}

function editAnnouncement(id) {
    const item = (window.__announcementsCache || []).find(a => a.id === id);
    if (!item) return;

    document.getElementById("composeTitle").textContent = "Edit Announcement";
    document.getElementById("annId").value = item.id;
    document.getElementById("annTitleInput").value = item.title;
    document.getElementById("annMessage").value = item.message;
    document.getElementById("cancelEditBtn").style.display = "inline-flex";
    window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
}

function resetForm() {
    document.getElementById("composeTitle").textContent = "New Announcement";
    document.getElementById("annId").value = "";
    document.getElementById("announcementForm").reset();
    document.getElementById("cancelEditBtn").style.display = "none";
    document.getElementById("annFormError").classList.remove("show");
}

function deleteAnnouncement(id) {
    if (!confirm("Delete this announcement? This cannot be undone.")) return;

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("id", id);

    fetch("../configuration/adm-announcements-actions.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || "Could not delete this announcement.");
                return;
            }
            loadAnnouncements();
        });
}

function bindAnnouncementForm() {
    document.getElementById("cancelEditBtn").addEventListener("click", resetForm);
    document.getElementById("previewAnnBtn").addEventListener("click", showPreview);
    document.getElementById("postAnnBtn").addEventListener("click", saveAnnouncement);
}

function saveAnnouncement() {
    const errorEl = document.getElementById("annFormError");
    const title = document.getElementById("annTitleInput").value.trim();
    const message = document.getElementById("annMessage").value.trim();

    if (!title || !message) {
        errorEl.textContent = "Title and message are required.";
        errorEl.classList.add("show");
        return;
    }

    const formData = new FormData();
    formData.append("action", "save");
    const id = document.getElementById("annId").value;
    if (id) formData.append("id", id);
    formData.append("title", title);
    formData.append("message", message);

    fetch("../configuration/adm-announcements-actions.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                errorEl.textContent = data.error || "Could not save this announcement.";
                errorEl.classList.add("show");
                return;
            }
            resetForm();
            loadAnnouncements();
        })
        .catch(() => {
            errorEl.textContent = "Network error. Please try again.";
            errorEl.classList.add("show");
        });
}

function showPreview() {
    const title = document.getElementById("annTitleInput").value.trim() || "Untitled Announcement";
    const message = document.getElementById("annMessage").value.trim() || "No message yet.";

    document.getElementById("previewBody").innerHTML = `
        <div class="announcement-item">
            <div class="announcement-top">
                <div>
                    <strong>${escapeHtml(title)}</strong>
                </div>
            </div>
            <div class="item-sub">${escapeHtml(message)}</div>
        </div>
    `;
    document.getElementById("previewModalOverlay").classList.add("show");
}

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function formatDate(dateStr) {
    if (!dateStr) return "--";
    return new Date(dateStr).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}
