let recordsTable;

document.addEventListener("DOMContentLoaded", () => {
  recordsTable = new DataTable(thesisRecords, renderRecordRow, {
    tableBodySelector: "#recordTableBody",
    emptyStateSelector: "#recordEmptyState",
    resultCountSelector: "#recordResultCount",
    paginationSelector: "#recordPagination",
    pageSize: 8,
    searchKeys: ["title", "groupName"],
    defaultSortBy: "title",
  });

  document.getElementById("recordTableBody").innerHTML =
    `<tr><td colspan="6" class="loading-row"><span class="spinner-brand"></span></td></tr>`;

  simulateRequest(() => recordsTable.render(), { delay: 350 });

  document.getElementById("recordSearch").addEventListener("input", e => recordsTable.setSearch(e.target.value));
  document.getElementById("batchFilter").addEventListener("change", e => recordsTable.setFilter("batch", e.target.value));
  document.getElementById("recordStatusFilter").addEventListener("change", e => recordsTable.setFilter("status", e.target.value));

  document.querySelectorAll(".data-table thead th.sortable").forEach(th => {
    th.addEventListener("click", () => recordsTable.setSort(th.dataset.sort));
  });
});

function renderRecordRow(record) {
  return `
    <tr>
      <td>${record.title}</td>
      <td>${record.groupName}</td>
      <td>${getAdviserName(record.adviserId)}</td>
      <td>${record.batch}</td>
      <td>${statusBadge(record.status)}</td>
      <td>
        <div class="row-actions">
          <button type="button" class="btn-icon-action" title="View Details" onclick="viewRecordDetail('${record.groupId}')">&#128065;</button>
        </div>
      </td>
    </tr>
  `;
}

function viewRecordDetail(groupId) {
  const record = thesisRecords.find(record => record.groupId === groupId);
  const group = groups.find(group => group.id === groupId);

  document.getElementById("recordDetailBody").innerHTML = `
    <div class="detail-row"><span class="detail-label">Thesis Title</span><span class="detail-value">${record.title}</span></div>
    <div class="detail-row"><span class="detail-label">Group</span><span class="detail-value">${record.groupName}</span></div>
    <div class="detail-row"><span class="detail-label">Members</span><span class="detail-value">${group.members.join(", ")}</span></div>
    <div class="detail-row"><span class="detail-label">Adviser</span><span class="detail-value">${getAdviserName(record.adviserId)}</span></div>
    <div class="detail-row"><span class="detail-label">Course</span><span class="detail-value">${getCourseName(group.courseId)}</span></div>
    <div class="detail-row"><span class="detail-label">Batch</span><span class="detail-value">${record.batch}</span></div>
    <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">${statusBadge(record.status)}</span></div>
    <div class="detail-row"><span class="detail-label">Milestones</span><span class="detail-value">${group.milestonesDone} / ${group.milestonesTotal} (${record.progress}%)</span></div>
    <div class="detail-row"><span class="detail-label">Last Update</span><span class="detail-value">${formatDate(group.lastUpdate)}</span></div>
  `;

  bootstrap.Modal.getOrCreateInstance(document.getElementById("recordDetailModal")).show();
}
