let announcements = [...systemAnnouncements];
let editingAnnouncementId = null;

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("announcementList").innerHTML =
    `<div class="loading-row"><span class="spinner-brand"></span></div>`;

  simulateRequest(() => renderAnnouncements(), { delay: 350 });

  document.getElementById("newAnnouncementBtn").addEventListener("click", () => openAnnouncementModal());
  document.getElementById("announcementForm").addEventListener("submit", onSaveAnnouncement);
  document.getElementById("announcementModal").addEventListener("hidden.bs.modal", resetAnnouncementForm);
});

function renderAnnouncements() {
  const sorted = [...announcements].sort((announcementA, announcementB) => new Date(announcementB.datePosted) - new Date(announcementA.datePosted));
  const listContainer = document.getElementById("announcementList");
  const emptyState = document.getElementById("announcementEmptyState");
  const countLabel = document.getElementById("announcementCount");

  countLabel.textContent = `${sorted.length} announcement${sorted.length === 1 ? "" : "s"} posted`;

  if (sorted.length === 0) {
    listContainer.innerHTML = "";
    emptyState.style.display = "block";
    return;
  }

  emptyState.style.display = "none";
  listContainer.innerHTML = sorted.map(announcement => `
    <div class="announcement-card">
      <div class="announcement-meta">${formatDate(announcement.datePosted)} &middot; Posted by ${announcement.postedBy} &middot; ${announcement.audience}</div>
      <h6>${announcement.title}</h6>
      <p>${announcement.body}</p>
      <div class="announcement-actions">
        <button type="button" class="btn-secondary-action" style="padding:0.4rem 0.9rem; font-size:0.78rem;" onclick="openAnnouncementModal('${announcement.id}')">Edit</button>
        <button type="button" class="btn-outline-danger-action" style="padding:0.4rem 0.9rem; font-size:0.78rem;" onclick="deleteAnnouncement('${announcement.id}')">Delete</button>
      </div>
    </div>
  `).join("");
}

function openAnnouncementModal(id = null) {
  editingAnnouncementId = id;
  const modalTitle = document.getElementById("announcementModalTitle");

  if (id) {
    const announcement = announcements.find(announcement => announcement.id === id);
    modalTitle.textContent = "Edit Announcement";
    document.getElementById("announcementId").value = announcement.id;
    document.getElementById("announcementTitle").value = announcement.title;
    document.getElementById("announcementBody").value = announcement.body;
    document.getElementById("announcementAudience").value = announcement.audience;
  } else {
    modalTitle.textContent = "New Announcement";
  }

  bootstrap.Modal.getOrCreateInstance(document.getElementById("announcementModal")).show();
}

function resetAnnouncementForm() {
  document.getElementById("announcementForm").reset();
  document.getElementById("announcementId").value = "";
  editingAnnouncementId = null;
}

function onSaveAnnouncement(e) {
  e.preventDefault();
  const formData = {
    title: document.getElementById("announcementTitle").value.trim(),
    body: document.getElementById("announcementBody").value.trim(),
    audience: document.getElementById("announcementAudience").value,
  };

  const btn = e.target.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.textContent = "Posting...";

  simulateRequest(() => {
    if (editingAnnouncementId) {
      const announcementIndex = announcements.findIndex(announcement => announcement.id === editingAnnouncementId);
      announcements[announcementIndex] = { ...announcements[announcementIndex], ...formData };
      showToast("Announcement updated.", "success");
    } else {
      announcements.unshift({
        id: "SA-" + Math.floor(10 + Math.random() * 90),
        postedBy: "Gerald Justine Corpuz",
        datePosted: new Date().toISOString().slice(0, 10),
        ...formData,
      });
      showToast("Announcement posted.", "success");
    }

    btn.disabled = false;
    btn.textContent = "Post Announcement";
    bootstrap.Modal.getInstance(document.getElementById("announcementModal")).hide();
    renderAnnouncements();
  }, { delay: 500 });
}

function deleteAnnouncement(id) {
  const announcement = announcements.find(announcement => announcement.id === id);
  confirmAction(
    "Delete Announcement",
    `Remove "${announcement.title}"? This cannot be undone.`,
    () => {
      simulateRequest(() => {
        announcements = announcements.filter(announcement => announcement.id !== id);
        renderAnnouncements();
        showToast("Announcement deleted.", "success");
      }, { delay: 400 });
    },
    "Delete"
  );
}
