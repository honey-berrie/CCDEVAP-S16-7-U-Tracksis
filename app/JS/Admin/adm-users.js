const usersState = {
  search: "",
  role: "",
  status: "",
  page: 1,
  perPage: 10,
};

document.addEventListener("DOMContentLoaded", () => {
  loadUsers();
  bindToolbarEvents();
  bindUserModalEvents();
});

function loadUsers() {
  const params = new URLSearchParams({
    search: usersState.search,
    role: usersState.role,
    status: usersState.status,
    page: usersState.page,
    perPage: usersState.perPage,
  });

  fetch(`../../Controllers/Admin/adm-users-data.php?${params.toString()}`)
    .then((res) => res.json())
    .then((data) => {
      renderUsersTable(data.users);
      renderRoleCounts(data.roleCounts);
      renderPagination(data.total, data.page, data.perPage);
    })
    .catch((err) => {
      console.error("Failed to load users:", err);
      document.getElementById("usersTableBody").innerHTML =
        `<tr><td colspan="6" class="empty-state">Could not load accounts.</td></tr>`;
    });
}

function renderUsersTable(users) {
  const body = document.getElementById("usersTableBody");
  if (!users || users.length === 0) {
    body.innerHTML = `<tr><td colspan="6" class="empty-state">No accounts match your filters.</td></tr>`;
    return;
  }

  body.innerHTML = users
    .map(
      (u) => `
        <tr>
            <td><button class="link-button" onclick="openViewUserDrawer(${u.id})">${escapeHtml(u.firstname)} ${escapeHtml(u.lastname)}</button></td>
            <td>${escapeHtml(u.email)}</td>
            <td style="text-transform:capitalize;">${escapeHtml(u.role)}</td>
            <td><span class="status-badge status-${u.isActive ? "active" : "inactive"}">${u.isActive ? "Active" : "Inactive"}</span></td>
            <td>${formatDate(u.createdAt)}</td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openEditUserModal(${u.id})">Edit</button>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(${u.id}, '${escapeHtml(u.firstname)} ${escapeHtml(u.lastname)}')">Delete</button>
            </td>
        </tr>
    `,
    )
    .join("");

  window.__usersCache = users;
}

function renderRoleCounts(counts) {
  if (!counts) return;
  document.getElementById("countStudent").textContent = `(${counts.student})`;
  document.getElementById("countAdviser").textContent = `(${counts.adviser})`;
  document.getElementById("countAdmin").textContent = `(${counts.admin})`;
}

function renderPagination(total, page, perPage) {
  const totalPages = Math.max(1, Math.ceil(total / perPage));
  const start = total === 0 ? 0 : (page - 1) * perPage + 1;
  const end = Math.min(total, page * perPage);

  document.getElementById("paginationSummary").textContent =
    `Showing ${start}-${end} of ${total}`;

  const controls = document.getElementById("paginationControls");
  let html = `<button ${page <= 1 ? "disabled" : ""} onclick="goToPage(${page - 1})">Prev</button>`;

  for (let p = 1; p <= totalPages; p++) {
    html += `<button class="${p === page ? "active" : ""}" onclick="goToPage(${p})">${p}</button>`;
  }

  html += `<button ${page >= totalPages ? "disabled" : ""} onclick="goToPage(${page + 1})">Next</button>`;
  controls.innerHTML = html;
}

function goToPage(page) {
  usersState.page = page;
  loadUsers();
}

function bindToolbarEvents() {
  let searchTimer;
  document.getElementById("userSearchInput").addEventListener("input", (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      usersState.search = e.target.value;
      usersState.page = 1;
      loadUsers();
    }, 300);
  });

  document.getElementById("statusFilter").addEventListener("change", (e) => {
    usersState.status = e.target.value;
    usersState.page = 1;
    loadUsers();
  });

  document.getElementById("rowsPerPage").addEventListener("change", (e) => {
    usersState.perPage = parseInt(e.target.value, 10);
    usersState.page = 1;
    loadUsers();
  });

  document.querySelectorAll("#roleFilterChips .filter-chip").forEach((chip) => {
    chip.addEventListener("click", () => {
      document
        .querySelectorAll("#roleFilterChips .filter-chip")
        .forEach((c) => c.classList.remove("active"));
      chip.classList.add("active");
      usersState.role = chip.dataset.role;
      usersState.page = 1;
      loadUsers();
    });
  });
}

function bindUserModalEvents() {
  document
    .getElementById("openCreateUserModal")
    .addEventListener("click", openCreateUserModal);
  document.getElementById("saveUserBtn").addEventListener("click", saveUser);

  document.querySelectorAll("[data-close-modal]").forEach((btn) => {
    btn.addEventListener("click", () => closeModal(btn.dataset.closeModal));
  });
}

function openCreateUserModal() {
  document.getElementById("userModalTitle").textContent = "Add Account";
  document.getElementById("userId").value = "";
  document.getElementById("userFirstname").value = "";
  document.getElementById("userLastname").value = "";
  document.getElementById("userEmail").value = "";
  document.getElementById("userRole").value = "student";
  document.getElementById("userPassword").value = "";
  document.getElementById("userStatusGroup").style.display = "none";
  document.getElementById("userPasswordGroup").style.display = "block";
  document.getElementById("userFormError").classList.remove("show");
  openModal("userModalOverlay");
}

function openEditUserModal(id) {
  const user = (window.__usersCache || []).find((u) => u.id === id);
  if (!user) return;

  document.getElementById("userModalTitle").textContent = "Edit Account";
  document.getElementById("userId").value = user.id;
  document.getElementById("userFirstname").value = user.firstname;
  document.getElementById("userLastname").value = user.lastname;
  document.getElementById("userEmail").value = user.email;
  document.getElementById("userRole").value = user.role;
  document.getElementById("userStatus").value = user.isActive ? "1" : "0";
  document.getElementById("userStatusGroup").style.display = "block";
  document.getElementById("userPasswordGroup").style.display = "none";
  document.getElementById("userFormError").classList.remove("show");
  openModal("userModalOverlay");
}

function saveUser() {
  const id = document.getElementById("userId").value;
  const firstname = document.getElementById("userFirstname").value.trim();
  const lastname = document.getElementById("userLastname").value.trim();
  const email = document.getElementById("userEmail").value.trim();
  const role = document.getElementById("userRole").value;
  const errorEl = document.getElementById("userFormError");

  if (!firstname || !lastname || !email) {
    errorEl.textContent = "Please fill in all required fields.";
    errorEl.classList.add("show");
    return;
  }

  const formData = new FormData();
  formData.append("firstname", firstname);
  formData.append("lastname", lastname);
  formData.append("email", email);
  formData.append("role", role);

  if (id) {
    formData.append("action", "update_user");
    formData.append("id", id);
    formData.append("is_active", document.getElementById("userStatus").value);
  } else {
    const password = document.getElementById("userPassword").value.trim();
    if (!password) {
      errorEl.textContent = "Please set a temporary password.";
      errorEl.classList.add("show");
      return;
    }
    formData.append("action", "create_user");
    formData.append("password", password);
  }

  fetch("../../Controllers/Admin/adm-users-actions.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        errorEl.textContent = data.error || "Something went wrong.";
        errorEl.classList.add("show");
        return;
      }
      closeModal("userModalOverlay");
      loadUsers();
    })
    .catch(() => {
      errorEl.textContent = "Network error. Please try again.";
      errorEl.classList.add("show");
    });
}

function deleteUser(id, name) {
  if (!confirm(`Delete the account for ${name}? This cannot be undone.`))
    return;

  const formData = new FormData();
  formData.append("action", "delete_user");
  formData.append("id", id);

  fetch("../../Controllers/Admin/adm-users-actions.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        alert(data.error || "Could not delete this account.");
        return;
      }
      loadUsers();
    });
}

function openModal(id) {
  document.getElementById(id).classList.add("show");
}

function closeModal(id) {
  document.getElementById(id).classList.remove("show");
}

function escapeHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

function formatDate(dateStr) {
  if (!dateStr) return "--";
  return new Date(dateStr).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

/* ---- Drawer: view user details ---- */
function openViewUserDrawer(id) {
  const url = `../../Controllers/Admin/adm-user-stats.php?id=${encodeURIComponent(id)}`;
  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      populateDrawer(data);
      const offEl = document.getElementById("userOffcanvas");
      if (
        offEl &&
        window.bootstrap &&
        typeof window.bootstrap.Offcanvas === "function"
      ) {
        const inst =
          window.bootstrap.Offcanvas.getInstance(offEl) ||
          new window.bootstrap.Offcanvas(offEl);
        inst.show();

        // Ensure offcanvas header/body follow current CSS variables (theme)
        const applyOffcanvasTheme = () => {
          try {
            const cs = getComputedStyle(document.documentElement);
            const headerBg = cs
              .getPropertyValue("--offcanvas-header-bg")
              .trim();
            const headerColor = cs
              .getPropertyValue("--offcanvas-header-color")
              .trim();
            const bodyBg = cs.getPropertyValue("--offcanvas-body-bg").trim();
            const bodyColor = cs
              .getPropertyValue("--offcanvas-body-color")
              .trim();
            const header = offEl.querySelector(".offcanvas-header");
            const body = offEl.querySelector(".offcanvas-body");
            const closeBtn = offEl.querySelector(".btn-close");

            // Set CSS variables on the offcanvas element itself so var() resolves locally
            if (headerBg)
              offEl.style.setProperty("--offcanvas-header-bg", headerBg);
            if (headerColor)
              offEl.style.setProperty("--offcanvas-header-color", headerColor);
            if (bodyBg) offEl.style.setProperty("--offcanvas-body-bg", bodyBg);
            if (bodyColor)
              offEl.style.setProperty("--offcanvas-body-color", bodyColor);

            // Also set inline styles with priority to override Bootstrap rules
            if (header) {
              if (headerBg) {
                header.style.setProperty("background", headerBg, "important");
                header.style.setProperty(
                  "background-image",
                  "none",
                  "important",
                );
                header.style.setProperty(
                  "background-color",
                  headerBg,
                  "important",
                );
              }
              if (headerColor)
                header.style.setProperty("color", headerColor, "important");
            }
            if (body) {
              if (bodyBg) {
                body.style.setProperty("background", bodyBg, "important");
                body.style.setProperty("background-image", "none", "important");
                body.style.setProperty("background-color", bodyBg, "important");
              }
              if (bodyColor)
                body.style.setProperty("color", bodyColor, "important");
            }
            if (closeBtn) {
              const theme = document.documentElement.getAttribute("data-theme");
              if (theme === "dark")
                closeBtn.style.setProperty(
                  "filter",
                  "invert(1) brightness(1.2)",
                );
              else closeBtn.style.removeProperty("filter");
            }
            // Also set styles on the offcanvas root as a last resort
            if (bodyBg)
              offEl.style.setProperty("background-color", bodyBg, "important");
            if (bodyColor)
              offEl.style.setProperty("color", bodyColor, "important");

            // Diagnostic logging to help debug why styles might be overridden
            try {
              const headerCs = header ? getComputedStyle(header) : null;
              const bodyCs = body ? getComputedStyle(body) : null;
              console.debug(
                "[Offcanvas Theme] header inline:",
                header ? header.style.cssText : null,
              );
              console.debug(
                "[Offcanvas Theme] header computed background:",
                headerCs ? headerCs.backgroundColor : null,
              );
              console.debug(
                "[Offcanvas Theme] body inline:",
                body ? body.style.cssText : null,
              );
              console.debug(
                "[Offcanvas Theme] body computed background:",
                bodyCs ? bodyCs.backgroundColor : null,
              );
            } catch (e) {
              // ignore
            }
          } catch (e) {
            // silent
          }
        };

        // Apply immediately and also when offcanvas is fully shown
        applyOffcanvasTheme();
        offEl.addEventListener("shown.bs.offcanvas", applyOffcanvasTheme);
        offEl.addEventListener("show.bs.offcanvas", applyOffcanvasTheme);
        offEl.addEventListener("hidden.bs.offcanvas", () => {
          // cleanup inline styles so CSS can take over next time
          const header = offEl.querySelector(".offcanvas-header");
          const body = offEl.querySelector(".offcanvas-body");
          const closeBtn = offEl.querySelector(".btn-close");
          if (header) {
            header.style.removeProperty("background-color");
            header.style.removeProperty("color");
          }
          if (body) {
            body.style.removeProperty("background-color");
            body.style.removeProperty("color");
          }
          if (closeBtn) {
            closeBtn.style.removeProperty("filter");
          }
          // remove local CSS variables
          offEl.style.removeProperty("--offcanvas-header-bg");
          offEl.style.removeProperty("--offcanvas-header-color");
          offEl.style.removeProperty("--offcanvas-body-bg");
          offEl.style.removeProperty("--offcanvas-body-color");
        });
      }
    })
    .catch((err) => {
      console.error("Failed to fetch user stats:", err);
      alert("Could not load user details.");
    });
}

function populateDrawer(data) {
  if (!data) return;
  const nameEl = document.getElementById("drawerUserName");
  const kpiEl = document.getElementById("drawerKpis");
  const recentEl = document.getElementById("drawerRecentActivity");
  const roleEl = document.getElementById("drawerRoleDetails");

  nameEl.textContent = `${data.user.firstname} ${data.user.lastname}`;

  // compute accent hue per role or group name
  function stringToHue(s) {
    if (!s) return 220;
    let h = 0;
    for (let i = 0; i < s.length; i++) h = (h << 5) - h + s.charCodeAt(i);
    h = Math.abs(h) % 360;
    return h;
  }

  function roleDefaultHue(role) {
    switch (role) {
      case "student":
        return 200;
      case "adviser":
        return 140;
      case "admin":
        return 260;
      default:
        return 220;
    }
  }

  const groupName =
    data.extra && data.extra.student && data.extra.student.group
      ? data.extra.student.group.name
      : null;

  const kpis = [
    {
      label: "Last Active",
      value: data.user.lastLogin || "--",
      key: "lastActive",
    },
    { label: "Submissions", value: data.kpis.submissions, key: "submissions" },
    {
      label: "Milestones Done",
      value: data.kpis.milestonesCompleted,
      key: "milestones",
    },
    {
      label: "Completion %",
      value: data.kpis.completionPercent + "%",
      key: "completion",
    },
  ];

  kpiEl.innerHTML = kpis
    .map((k) => {
      const hue = groupName
        ? stringToHue(groupName)
        : roleDefaultHue(data.user.role);
      return `<div class="kpi-card" style="--accent-h:${hue}"><div class="kpi-value"><strong>${k.value}</strong></div><div class="kpi-label">${k.label}</div></div>`;
    })
    .join("");

  if (!data.recent || data.recent.length === 0) {
    recentEl.innerHTML = '<li class="empty-state">No recent activity.</li>';
  } else {
    recentEl.innerHTML = data.recent
      .map(
        (r) =>
          `<li><div class="activity-time">${new Date(r.created_at).toLocaleString()}</div><div class="activity-msg">${escapeHtml(r.message)}</div></li>`,
      )
      .join("");
  }

  // role-specific details
  if (roleEl) {
    roleEl.innerHTML = "";
    const extra = data.extra || {};
    if (data.user.role === "student" && extra.student) {
      const s = extra.student;
      if (s.group) {
        const days =
          s.daysUntilDefense === null
            ? "—"
            : s.daysUntilDefense >= 0
              ? `${s.daysUntilDefense} days`
              : `${Math.abs(s.daysUntilDefense)} days ago`;
        const hue = groupName
          ? stringToHue(groupName)
          : roleDefaultHue(data.user.role);
        roleEl.innerHTML = `
          <div class="mb-2"><strong>Thesis Group:</strong> <span style="color:hsl(${hue} 70% 45%);">${escapeHtml(s.group.name)}</span></div>
          <div class="mb-2"><strong>Defense:</strong> ${s.group.defense_date ? new Date(s.group.defense_date).toLocaleDateString() : "TBD"} (${days})</div>
          <div class="mb-2"><strong>Thesis Progress:</strong>
            <div class="progress" style="height:14px;"><div class="progress-bar" role="progressbar" style="width:${s.thesisProgress}%; background-color: hsl(${hue} 70% 45%);" aria-valuenow="${s.thesisProgress}" aria-valuemin="0" aria-valuemax="100">${s.thesisProgress}%</div></div>
          </div>
          <div class="mt-3"><strong>Milestones</strong>
            <ul class="recent-activity">${s.milestones.length === 0 ? '<li class="empty-state">No milestones.</li>' : s.milestones.map((m) => `<li><div><strong>${escapeHtml(m.name)}</strong> — ${m.progress}% — <em>${escapeHtml(m.status)}</em></div></li>`).join("")}</ul>
          </div>`;
      } else {
        roleEl.innerHTML =
          '<div class="empty-state">No thesis group assigned.</div>';
      }
    } else if (data.user.role === "adviser" && extra.adviser) {
      const a = extra.adviser;
      roleEl.innerHTML = `
        <div class="mb-2"><strong>Assigned Groups</strong>
          <ul class="recent-activity">${a.assignedGroups.length === 0 ? '<li class="empty-state">No assigned groups.</li>' : a.assignedGroups.map((g) => `<li><div><strong style="color:hsl(${stringToHue(g.name)} 70% 45%);">${escapeHtml(g.name)}</strong> — <em>${escapeHtml(g.progress_status)}</em></div></li>`).join("")}</ul>
        </div>
        <div class="mt-3"><strong>Upcoming Consultations</strong>
          <ul class="recent-activity">${a.consultations.length === 0 ? '<li class="empty-state">No consultations.</li>' : a.consultations.map((c) => `<li><div>${c.proposed_schedule ? new Date(c.proposed_schedule).toLocaleString() : "TBD"} — ${escapeHtml(c.topic)} — <em>${escapeHtml(c.status)}</em></div></li>`).join("")}</ul>
        </div>`;
    } else {
      roleEl.innerHTML = "";
    }
  }
}
