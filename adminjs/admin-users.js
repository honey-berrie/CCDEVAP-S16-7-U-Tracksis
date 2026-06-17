let userList = [...allUsers];
let userTable;
let editingUserId = null;

document.addEventListener("DOMContentLoaded", () => {
  userTable = new DataTable(userList, renderUserRow, {
    tableBodySelector: "#userTableBody",
    emptyStateSelector: "#userEmptyState",
    resultCountSelector: "#userResultCount",
    paginationSelector: "#userPagination",
    pageSize: 8,
    searchKeys: ["firstName", "lastName", "email"],
    defaultSortBy: "lastName",
  });

  // initial loading state, then render
  document.getElementById("userTableBody").innerHTML =
    `<tr><td colspan="6" class="loading-row"><span class="spinner-brand"></span></td></tr>`;

  simulateRequest(() => userTable.render(), { delay: 350 });

  document.getElementById("userSearch").addEventListener("input", e => userTable.setSearch(e.target.value));
  document.getElementById("roleFilter").addEventListener("change", e => userTable.setFilter("role", e.target.value));
  document.getElementById("statusFilter").addEventListener("change", e => userTable.setFilter("status", e.target.value));

  document.querySelectorAll(".data-table thead th.sortable").forEach(th => {
    th.addEventListener("click", () => userTable.setSort(th.dataset.sort));
  });

  document.getElementById("addUserBtn").addEventListener("click", () => openUserModal());

  document.getElementById("userForm").addEventListener("submit", onSaveUser);

  document.getElementById("userModal").addEventListener("hidden.bs.modal", resetUserForm);
});

function renderUserRow(user) {
  return `
    <tr>
      <td>${user.firstName} ${user.lastName}</td>
      <td>${user.email}</td>
      <td>${roleBadge(user.role)}</td>
      <td>${statusBadge(user.status)}</td>
      <td>${formatDate(user.dateJoined)}</td>
      <td>
        <div class="row-actions">
          <button type="button" class="btn-icon-action" title="Edit" onclick="openUserModal('${user.id}')">&#9998;</button>
          <button type="button" class="btn-icon-action danger" title="Delete" onclick="deleteUser('${user.id}')">&#128465;</button>
        </div>
      </td>
    </tr>
  `;
}

function openUserModal(userId = null) {
  editingUserId = userId;
  const modalTitle = document.getElementById("userModalTitle");

  if (userId) {
    const user = userList.find(user => user.id === userId);
    modalTitle.textContent = "Edit User";
    document.getElementById("userId").value = user.id;
    document.getElementById("userFirstName").value = user.firstName;
    document.getElementById("userLastName").value = user.lastName;
    document.getElementById("userEmail").value = user.email;
    document.getElementById("userRole").value = user.role;
    document.getElementById("userStatus").value = user.status;
  } else {
    modalTitle.textContent = "Add User";
  }

  bootstrap.Modal.getOrCreateInstance(document.getElementById("userModal")).show();
}

function resetUserForm() {
  document.getElementById("userForm").reset();
  document.getElementById("userId").value = "";
  editingUserId = null;
}

function onSaveUser(e) {
  e.preventDefault();

  const formData = {
    firstName: document.getElementById("userFirstName").value.trim(),
    lastName: document.getElementById("userLastName").value.trim(),
    email: document.getElementById("userEmail").value.trim(),
    role: document.getElementById("userRole").value,
    status: document.getElementById("userStatus").value,
  };

  const saveBtn = e.target.querySelector('button[type="submit"]');
  saveBtn.disabled = true;
  saveBtn.textContent = "Saving...";

  simulateRequest(() => {
    if (editingUserId) {
      const userIndex = userList.findIndex(user => user.id === editingUserId);
      userList[userIndex] = { ...userList[userIndex], ...formData };
      showToast("User updated successfully.", "success");
    } else {
      const newUser = {
        id: "U-" + Math.floor(1000 + Math.random() * 9000),
        dateJoined: new Date().toISOString().slice(0, 10),
        group: null,
        ...formData,
      };
      userList.unshift(newUser);
      showToast("User added successfully.", "success");
    }

    saveBtn.disabled = false;
    saveBtn.textContent = "Save User";
    userTable.setData(userList);
    bootstrap.Modal.getInstance(document.getElementById("userModal")).hide();
  }, { delay: 500 });
}

function deleteUser(userId) {
  const user = userList.find(user => user.id === userId);
  confirmAction(
    "Delete User",
    `Remove ${user.firstName} ${user.lastName} (${user.email}) from the system? This cannot be undone.`,
    () => {
      simulateRequest(() => {
        userList = userList.filter(user => user.id !== userId);
        userTable.setData(userList);
        showToast("User deleted.", "success");
      }, { delay: 400 });
    },
    "Delete"
  );
}
