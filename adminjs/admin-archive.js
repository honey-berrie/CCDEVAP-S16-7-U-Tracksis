let archivedList = [...archivedTheses];
let pendingArchiveGroupId = null;

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("completedTableBody").innerHTML =
    `<tr><td colspan="6" class="loading-row"><span class="spinner-brand"></span></td></tr>`;
  document.getElementById("archivedTableBody").innerHTML =
    `<tr><td colspan="6" class="loading-row"><span class="spinner-brand"></span></td></tr>`;

  simulateRequest(() => {
    renderCompletedTable();
    renderArchivedTable();
  }, { delay: 400 });

  document.getElementById("confirmArchiveBtn").addEventListener("click", onConfirmArchive);
});

function getUnarchivedCompletedGroups() {
  const archivedGroupIds = new Set(archivedList.map(archivedItem => archivedItem.groupId));
  return groups.filter(group => group.status === "completed" && !archivedGroupIds.has(group.id));
}

function renderCompletedTable() {
  const unarchivedGroups = getUnarchivedCompletedGroups();
  const tableBody = document.getElementById("completedTableBody");
  const emptyState = document.getElementById("completedEmptyState");

  if (unarchivedGroups.length === 0) {
    tableBody.closest("table").style.display = "none";
    emptyState.style.display = "block";
    return;
  }

  tableBody.closest("table").style.display = "table";
  emptyState.style.display = "none";

  tableBody.innerHTML = unarchivedGroups.map(group => `
    <tr>
      <td>${group.name}</td>
      <td>${group.title}</td>
      <td>${getAdviserName(group.adviserId)}</td>
      <td>${group.batch}</td>
      <td>${formatDate(group.lastUpdate)}</td>
      <td>
        <button type="button" class="btn-secondary-action" style="padding:0.4rem 0.9rem; font-size:0.78rem;" onclick="openArchiveModal('${group.id}')">Archive</button>
      </td>
    </tr>
  `).join("");
}

function renderArchivedTable() {
  const tableBody = document.getElementById("archivedTableBody");
  const emptyState = document.getElementById("archivedEmptyState");

  if (archivedList.length === 0) {
    tableBody.closest("table").style.display = "none";
    emptyState.style.display = "block";
    return;
  }

  tableBody.closest("table").style.display = "table";
  emptyState.style.display = "none";

  tableBody.innerHTML = archivedList.map(archivedItem => `
    <tr>
      <td>${archivedItem.groupName}</td>
      <td>${archivedItem.title}</td>
      <td>${archivedItem.adviserName}</td>
      <td>${archivedItem.batch}</td>
      <td>${formatDate(archivedItem.archivedDate)}</td>
      <td>
        <button type="button" class="btn-restore" onclick="restoreArchive('${archivedItem.id}')">Restore</button>
      </td>
    </tr>
  `).join("");
}

function openArchiveModal(groupId) {
  pendingArchiveGroupId = groupId;
  const group = groups.find(group => group.id === groupId);

  document.getElementById("archiveModalBody").innerHTML = `
    <p style="font-size:0.875rem; color:var(--text); margin-bottom:1rem;">
      This will move <strong>${group.name}</strong>'s thesis into institutional archive records.
      The group's final status and milestone history will be preserved as read-only.
    </p>
    <div class="detail-row"><span class="detail-label">Thesis Title</span><span class="detail-value">${group.title}</span></div>
    <div class="detail-row"><span class="detail-label">Adviser</span><span class="detail-value">${getAdviserName(group.adviserId)}</span></div>
    <div class="detail-row"><span class="detail-label">Batch</span><span class="detail-value">${group.batch}</span></div>
    <div class="detail-row"><span class="detail-label">Milestones</span><span class="detail-value">${group.milestonesDone} / ${group.milestonesTotal} (Complete)</span></div>
  `;

  bootstrap.Modal.getOrCreateInstance(document.getElementById("archiveModal")).show();
}

function onConfirmArchive() {
  const group = groups.find(group => group.id === pendingArchiveGroupId);
  const btn = document.getElementById("confirmArchiveBtn");
  btn.disabled = true;
  btn.textContent = "Archiving...";

  simulateRequest(() => {
    archivedList.unshift({
      id: "A-" + Math.floor(100 + Math.random() * 900),
      groupId: group.id,
      groupName: group.name,
      title: group.title,
      adviserName: getAdviserName(group.adviserId),
      batch: group.batch,
      archivedDate: new Date().toISOString().slice(0, 10),
      archivedBy: "Gerald Justine Corpuz",
    });

    btn.disabled = false;
    btn.textContent = "Confirm Archive";
    bootstrap.Modal.getInstance(document.getElementById("archiveModal")).hide();

    renderCompletedTable();
    renderArchivedTable();
    showToast(`${group.name}'s thesis has been archived.`, "success");
  }, { delay: 500 });
}

function restoreArchive(archiveId) {
  const archivedItem = archivedList.find(archivedItem => archivedItem.id === archiveId);
  confirmAction(
    "Restore Archived Thesis",
    `Move ${archivedItem.groupName}'s thesis back to the completed (unarchived) list?`,
    () => {
      simulateRequest(() => {
        archivedList = archivedList.filter(archivedItem => archivedItem.id !== archiveId);
        renderCompletedTable();
        renderArchivedTable();
        showToast(`${archivedItem.groupName}'s thesis was restored.`, "success");
      }, { delay: 400 });
    },
    "Restore"
  );
}
