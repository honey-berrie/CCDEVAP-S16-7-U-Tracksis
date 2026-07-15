/* ============================================================
   utils.js
   Shared vanilla-JS helpers for the Administrator and
   Thesis Coordinator pages. No frameworks, no build step,
   matching the rest of the project's plain DOM JS style.
   ============================================================ */

/* ---------- Toast Notifications ----------
   Expects a fixed container in the page: <div id="toastStack"></div>
   Usage: showToast("Saved successfully.", "success")
   types: "success" | "error" | "warning" | "info"
*/
function showToast(message, type = "success") {
  const stack = document.getElementById("toastStack");
  if (!stack) return;

  const icons = {
    success: "&#10003;",
    error: "&#10005;",
    warning: "!",
    info: "i",
  };

  const toast = document.createElement("div");
  toast.className = `app-toast app-toast-${type}`;
  toast.innerHTML = `
    <span class="app-toast-icon">${icons[type] || icons.info}</span>
    <span class="app-toast-message">${message}</span>
    <button type="button" class="app-toast-close" aria-label="Dismiss">&times;</button>
  `;

  stack.appendChild(toast);

  const remove = () => {
    toast.classList.add("app-toast-hide");
    setTimeout(() => toast.remove(), 200);
  };

  toast.querySelector(".app-toast-close").addEventListener("click", remove);
  setTimeout(remove, 4000);
}

/* ---------- Confirmation Dialog ----------
   Reuses the shared #confirmModal Bootstrap modal markup
   (include it once per page, see dashboard-shared partial).
   Usage: confirmAction("Delete this user?", "This cannot be undone.", () => { ...do it... })
*/
function confirmAction(title, body, onConfirm, confirmLabel = "Confirm") {
  const modalEl = document.getElementById("confirmModal");
  if (!modalEl) {
    // fallback if markup isn't present on this page
    if (window.confirm(`${title}\n${body}`)) onConfirm();
    return;
  }

  modalEl.querySelector(".confirm-modal-title").textContent = title;
  modalEl.querySelector(".confirm-modal-body").textContent = body;
  const confirmBtn = modalEl.querySelector(".confirm-modal-confirm");
  confirmBtn.textContent = confirmLabel;

  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  const handler = () => {
    modal.hide();
    onConfirm();
    confirmBtn.removeEventListener("click", handler);
  };

  confirmBtn.addEventListener("click", handler);
  modal.show();
}

/* ---------- Data Table ----------
   A tiny reusable helper for search + filter + sort + paginate
   over an in-memory array of row objects, rendered through a
   caller-supplied render function. Keeps each page's own JS
   focused on "what a row looks like" rather than reimplementing
   table mechanics every time.
*/
class DataTable {
  /**
   * @param {Array} data - the full list of items to show in the table
   * @param {Function} renderRow - (item) => string (returns a <tr>...</tr>)
   * @param {Object} opts - { pageSize, tableBodySelector, emptyStateSelector, resultCountSelector }
   */
  constructor(data, renderRow, opts = {}) {
    this.items = data;
    this.renderRow = renderRow;
    this.pageSize = opts.pageSize || 8;
    this.tableBody = document.querySelector(opts.tableBodySelector);
    this.emptyState = opts.emptyStateSelector ? document.querySelector(opts.emptyStateSelector) : null;
    this.resultCount = opts.resultCountSelector ? document.querySelector(opts.resultCountSelector) : null;
    this.pagination = opts.paginationSelector ? document.querySelector(opts.paginationSelector) : null;

    this.searchTerm = "";
    this.searchKeys = opts.searchKeys || [];
    this.filters = {};
    this.sortBy = opts.defaultSortBy || null;
    this.sortOrder = opts.defaultSortOrder || "asc";
    this.currentPage = 1;
  }

  setSearch(term) {
    this.searchTerm = (term || "").trim().toLowerCase();
    this.currentPage = 1;
    this.render();
  }

  setFilter(key, value) {
    if (value === "" || value === "all") delete this.filters[key];
    else this.filters[key] = value;
    this.currentPage = 1;
    this.render();
  }

  setSort(key) {
    if (this.sortBy === key) {
      this.sortOrder = this.sortOrder === "asc" ? "desc" : "asc";
    } else {
      this.sortBy = key;
      this.sortOrder = "asc";
    }
    this.render();
  }

  setData(data) {
    this.items = data;
    this.currentPage = 1;
    this.render();
  }

  goToPage(page) {
    this.currentPage = page;
    this.render();
  }

  getFilteredItems() {
    let rows = this.items;

    if (this.searchTerm) {
      rows = rows.filter(item =>
        this.searchKeys.some(key => String(getNested(item, key) ?? "").toLowerCase().includes(this.searchTerm))
      );
    }

    Object.entries(this.filters).forEach(([key, value]) => {
      rows = rows.filter(item => String(getNested(item, key)) === String(value));
    });

    if (this.sortBy) {
      const key = this.sortBy;
      const direction = this.sortOrder === "asc" ? 1 : -1;
      rows = [...rows].sort((a, b) => {
        const av = getNested(a, key);
        const bv = getNested(b, key);
        if (typeof av === "number" && typeof bv === "number") return (av - bv) * direction;
        return String(av ?? "").localeCompare(String(bv ?? "")) * direction;
      });
    }

    return rows;
  }

  render() {
    const filtered = this.getFilteredItems();
    const totalPages = Math.max(1, Math.ceil(filtered.length / this.pageSize));
    if (this.currentPage > totalPages) this.currentPage = totalPages;

    const start = (this.currentPage - 1) * this.pageSize;
    const pageRows = filtered.slice(start, start + this.pageSize);

    if (this.tableBody) {
      this.tableBody.innerHTML = pageRows.map(this.renderRow).join("");
    }

    if (this.emptyState) {
      this.emptyState.style.display = filtered.length === 0 ? "block" : "none";
      if (this.tableBody) this.tableBody.closest("table").style.display = filtered.length === 0 ? "none" : "table";
    }

    if (this.resultCount) {
      const shownStart = filtered.length === 0 ? 0 : start + 1;
      const shownEnd = Math.min(start + this.pageSize, filtered.length);
      this.resultCount.textContent = `Showing ${shownStart}-${shownEnd} of ${filtered.length}`;
    }

    if (this.pagination) {
      this.renderPagination(totalPages);
    }
  }

  renderPagination(totalPages) {
    let html = "";
    const pageButton = (page, label, disabled, active) => `
      <li class="page-item ${disabled ? "disabled" : ""} ${active ? "active" : ""}">
        <button type="button" class="page-link" data-page="${page}" ${disabled ? "disabled" : ""}>${label}</button>
      </li>`;

    html += pageButton(this.currentPage - 1, "&laquo;", this.currentPage === 1, false);
    for (let p = 1; p <= totalPages; p++) {
      html += pageButton(p, p, false, p === this.currentPage);
    }
    html += pageButton(this.currentPage + 1, "&raquo;", this.currentPage === totalPages, false);

    this.pagination.innerHTML = html;
    this.pagination.querySelectorAll(".page-link").forEach(btn => {
      btn.addEventListener("click", () => {
        const page = parseInt(btn.dataset.page, 10);
        if (!isNaN(page) && page >= 1 && page <= totalPages) this.goToPage(page);
      });
    });
  }
}

function getNested(obj, key) {
  return key.split(".").reduce((value, prop) => (value ? value[prop] : undefined), obj);
}

/* ---------- Simulated async action (loading state helper) ----------
   Usage: simulateRequest(() => { ...success logic... }, { delay: 600 })
*/
function simulateRequest(onDone, opts = {}) {
  const delay = opts.delay ?? 500;
  const failRate = opts.failRate ?? 0; // 0 = never fail, for predictable demos
  setTimeout(() => {
    if (failRate > 0 && Math.random() < failRate) {
      opts.onError ? opts.onError() : showToast("Something went wrong. Please try again.", "error");
      return;
    }
    onDone();
  }, delay);
}

/* ---------- Status badge helper ----------
   Maps a status string to the right badge-* class already
   established in css/student.css, extended with new variants
   in css/dashboard-shared.css.
*/
function statusBadge(status) {
  const statusMap = {
    active: "badge-approved",
    completed: "badge-approved",
    approved: "badge-approved",
    pending: "badge-pending",
    "needs revision": "badge-pending",
    rejected: "badge-rejected",
    archived: "badge-neutral",
    overdue: "badge-rejected",
    inactive: "badge-neutral",
    "in progress": "badge-review",
    "under review": "badge-review",
    "on track": "badge-approved",
    "needs attention": "badge-pending",
  };
  const badgeClass = statusMap[String(status).toLowerCase()] || "badge-neutral";
  const label = String(status).replace(/\b\w/g, c => c.toUpperCase());
  return `<span class="badge-status ${badgeClass}">${label}</span>`;
}

function roleBadge(role) {
  const roleMap = {
    student: "badge-role-student",
    adviser: "badge-role-adviser",
    coordinator: "badge-role-coordinator",
    admin: "badge-role-admin",
  };
  const badgeClass = roleMap[String(role).toLowerCase()] || "badge-neutral";
  const label = String(role).replace(/\b\w/g, c => c.toUpperCase());
  return `<span class="badge-status ${badgeClass}">${label}</span>`;
}

function formatDate(isoString) {
  if (!isoString) return "—";
  const date = new Date(isoString);
  if (isNaN(date)) return isoString;
  return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
}
