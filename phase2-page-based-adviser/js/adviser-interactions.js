

document.addEventListener("DOMContentLoaded", function () {
  createModal();
  connectButtons();
  connectSearchBars();
  initTheme();
});

function connectButtons() {
  document.addEventListener("click", function (event) {
    var button = event.target.closest("[data-action]");

    if (!button) {
      return;
    }

    var action = button.dataset.action;

    if (action === "review-submissions") {
      window.location.href = "thesis-adviser-submissions.php";
    }

    if (action === "logout") {
      logoutUser();
    }

    if (action === "schedule-consultation") {
      openModal("Schedule consultation", getScheduleForm());
    }

    if (action === "reschedule-consultation") {
      openModal("Reschedule consultation", getRescheduleForm(button));
    }

    if (action === "join-consultation") {
      if (button.dataset.meetingLink) {
        window.open(button.dataset.meetingLink, "_blank");
      } else {
        alert("No meeting link is available for this consultation.");
      }
    }

    if (action === "edit-comment") {
      openModal("Edit comment", getCommentForm(button));
    }

    if (action === "view-milestone") {
      openModal("Milestone Details", getMilestoneDetails(button));
    }

    if (action === "filter-submissions") {
      filterSubmissions(button);
    }

    if (action === "preview-submission") {
      openModal("Submission Preview", getSubmissionPreview(button));
    }

    if (action === "revise-submission") {
      openModal("Request revision", getRevisionForm(button));
    }

    if (action === "approve-submission") {
      updateSubmissionStatus(button, "approved", "Approved");
      alert("Submission marked as approved for this front-end demo.");
    }

    if (action === "reject-submission") {
      updateSubmissionStatus(button, "rejected", "Rejected");
      alert("Submission marked as rejected for this front-end demo.");
    }

    if (action === "close-modal") {
      closeModal();
    }
  });

  document.addEventListener("submit", function (event) {
    if (!event.target.classList.contains("demo-form")) {
      return;
    }

    event.preventDefault();
    alert("Saved successfully. This is a front-end demo, so the data is not stored in a database yet.");
    closeModal();
  });
}

async function logoutUser() {
  var confirmed = confirm("Are you sure you want to log out?");

  if (!confirmed) {
    return;
  }

  var logoutPaths = [
    "../api/auth/logout.php",
    "../../api/auth/logout.php",
    "api/auth/logout.php"
  ];

  for (var i = 0; i < logoutPaths.length; i += 1) {
    try {
      var response = await fetch(logoutPaths[i], {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include"
      });

      if (response.ok) {
        redirectAfterLogout();
        return;
      }
    } catch (error) {
      // The local MVC test folder may not have the shared API yet.
    }
  }

  redirectAfterLogout();
}

function redirectAfterLogout() {
  window.location.href = "../../pages/auth/auth.html";
}

function connectSearchBars() {
  var searchInputs = document.querySelectorAll(".top-bar input, .search-bar-mobile input");

  searchInputs.forEach(function (input) {
    input.addEventListener("input", function () {
      filterPage(input.value);
    });
  });
}

function filterPage(searchText) {
  var keyword = searchText.toLowerCase().trim();
  var searchableItems = document.querySelectorAll(
    ".stat-card, .panel, .group-card, .group-milestone-panel, .milestone-card, .notification-card, .consultation-card, .history-card, .submission-card"
  );

  searchableItems.forEach(function (item) {
    var itemText = item.textContent.toLowerCase();
    item.classList.toggle("is-hidden", keyword !== "" && !itemText.includes(keyword));
  });
}

function filterSubmissions(button) {
  var filter = button.dataset.filter;
  var chips = document.querySelectorAll(".filter-chip");
  var submissions = document.querySelectorAll(".submission-card");

  chips.forEach(function (chip) {
    chip.classList.remove("active");
  });

  button.classList.add("active");

  submissions.forEach(function (submission) {
    var status = submission.dataset.status;
    submission.classList.toggle("is-hidden", filter !== "all" && status !== filter);
  });
}

function getSubmissionPreview(button) {
  var card = button.closest(".submission-card");
  var title = escapeHtml(card ? card.querySelector("h3").textContent : "Selected submission");
  var group = escapeHtml(card ? card.querySelector(".submission-main p").textContent : "Group");
  var badge = escapeHtml(card ? card.querySelector(".status-badge").textContent : "Pending");
  var fileName = escapeHtml(card && card.dataset.fileName ? card.dataset.fileName : "Uploaded thesis file");
  var feedback = escapeHtml(card && card.dataset.feedback ? card.dataset.feedback : "No adviser feedback yet.");

  return (
    '<div class="submission-preview">' +
      '<div class="preview-meta">' +
        '<p><strong>Group:</strong> ' + group + '</p>' +
        '<p><strong>Submission:</strong> ' + title + '</p>' +
        '<p><strong>Status:</strong> ' + badge + '</p>' +
        '<p><strong>Feedback:</strong> ' + feedback + '</p>' +
      '</div>' +
      '<div class="preview-document">' +
        '<div class="preview-page-label">Document Preview</div>' +
        '<h4>' + title + '</h4>' +
        '<p class="preview-file-name">' + fileName + '</p>' +
        '<p class="preview-line long"></p>' +
        '<p class="preview-line"></p>' +
        '<p class="preview-line medium"></p>' +
        '<p class="preview-paragraph">This preview represents the uploaded thesis file. In the full system, the actual PDF or document would be displayed here instead of downloading immediately.</p>' +
        '<p class="preview-line long"></p>' +
        '<p class="preview-line short"></p>' +
      '</div>' +
      '<div class="preview-actions-note">Use Revise, Approve, or Reject after reviewing the preview.</div>' +
    '</div>'
  );
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, function (character) {
    return {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;"
    }[character];
  });
}

function updateSubmissionStatus(button, status, label) {
  var card = button.closest(".submission-card");

  if (!card) {
    return;
  }

  var badge = card.querySelector(".status-badge");
  card.dataset.status = status;
  badge.className = "status-badge " + status;
  badge.textContent = label;
}

function createModal() {
  var modal = document.createElement("div");
  modal.className = "modal-backdrop is-hidden";
  modal.innerHTML =
    '<div class="modal-box">' +
      '<div class="modal-header">' +
        '<h3 id="modal-title">Modal title</h3>' +
        '<button class="modal-close" data-action="close-modal" aria-label="Close modal">&times;</button>' +
      '</div>' +
      '<div id="modal-body"></div>' +
    '</div>';

  document.body.appendChild(modal);

  modal.addEventListener("click", function (event) {
    if (event.target === modal) {
      closeModal();
    }
  });
}

function openModal(title, content) {
  document.getElementById("modal-title").textContent = title;
  document.getElementById("modal-body").innerHTML = content;
  document.querySelector(".modal-backdrop").classList.remove("is-hidden");
}

function closeModal() {
  document.querySelector(".modal-backdrop").classList.add("is-hidden");
}

function getScheduleForm() {
  var groups = Array.isArray(window.thesisGroups) ? window.thesisGroups : [];
  var groupOptions = groups.map(function (group) {
    return '<option value="' + group.group_id + '">' + group.group_name + '</option>';
  }).join("");

  if (groupOptions === "") {
    groupOptions =
      '<option value="1">Group A</option>' +
      '<option value="2">Group B</option>' +
      '<option value="3">Group C</option>' +
      '<option value="4">Group D</option>' +
      '<option value="5">Group E</option>';
  }

  return (
    '<form class="db-schedule-form" method="post" action="thesis-adviser-consultations.php">' +
      '<input type="hidden" name="action" value="create-consultation">' +
      '<label>Group</label>' +
      '<select name="group_id" required>' +
        '<option value="">Choose a group</option>' +
        groupOptions +
      '</select>' +
      '<label>Date</label>' +
      '<input type="date" name="consultation_date" required>' +
      '<label>Time</label>' +
      '<input type="time" name="consultation_time" required>' +
      '<label>End time</label>' +
      '<input type="time" name="consultation_end_time" required>' +
      '<label>Meeting link</label>' +
      '<input type="url" name="meeting_link" placeholder="https://meet.example/group-a">' +
      '<label>Agenda</label>' +
      '<textarea rows="4" name="agenda" placeholder="Example: Review chapter 4 results" required></textarea>' +
      '<button class="btn-outline" type="submit">Save consultation</button>' +
    '</form>'
  );
}

function getRescheduleForm(button) {
  var card = button.closest(".consultation-card");
  var title = card ? card.querySelector("h4").textContent : "Selected consultation";
  var consultationId = button.dataset.consultationId || "";
  var currentDate = button.dataset.currentDate || "";
  var currentTime = button.dataset.currentTime || "";
  var currentEndTime = button.dataset.currentEndTime || "";

  return (
    '<form class="db-schedule-form" method="post" action="thesis-adviser-consultations.php">' +
      '<input type="hidden" name="action" value="update-consultation-schedule">' +
      '<input type="hidden" name="consultation_id" value="' + consultationId + '">' +
      '<p class="modal-note">' + title + '</p>' +
      '<label>New date</label>' +
      '<input type="date" name="consultation_date" value="' + currentDate + '" required>' +
      '<label>New time</label>' +
      '<input type="time" name="consultation_time" value="' + currentTime + '" required>' +
      '<label>New end time</label>' +
      '<input type="time" name="consultation_end_time" value="' + currentEndTime + '" required>' +
      '<label>Reason</label>' +
      '<textarea rows="4" name="reason" placeholder="Reason for rescheduling"></textarea>' +
      '<button class="btn-outline" type="submit">Save new schedule</button>' +
    '</form>'
  );
}

function getCommentForm(button) {
  var card = button.closest(".history-card");
  var consultationId = button.dataset.consultationId || "";
  var comment = "";

  if (card) {
    comment = card.querySelector(".comment-box p").textContent;
  }

  return (
    '<form class="db-comment-form" method="post" action="thesis-adviser-consultations.php">' +
      '<input type="hidden" name="action" value="update-consultation-comment">' +
      '<input type="hidden" name="consultation_id" value="' + consultationId + '">' +
      '<label>Comment</label>' +
      '<textarea rows="5" name="comment">' + comment + '</textarea>' +
      '<button class="btn-outline" type="submit">Save comment</button>' +
    '</form>'
  );
}

function getRevisionForm(button) {
  var card = button.closest(".submission-card");
  var submissionId = button.dataset.submissionId || "";
  var title = card ? card.querySelector("h3").textContent : "Selected submission";

  return (
    '<form class="db-submission-form" method="post" action="thesis-adviser-submissions.php">' +
      '<input type="hidden" name="action" value="update-submission-status">' +
      '<input type="hidden" name="submission_id" value="' + submissionId + '">' +
      '<input type="hidden" name="status" value="Revision">' +
      '<p class="modal-note">' + title + '</p>' +
      '<label>Revision comment</label>' +
      '<textarea rows="5" name="feedback" placeholder="Explain what the group needs to revise" required></textarea>' +
      '<button class="btn-outline" type="submit">Send revision request</button>' +
    '</form>'
  );
}

function getMilestoneDetails(card) {
  return (
    '<div class="details-list">' +
      '<div><strong>Group:</strong><span>' + card.dataset.group + '</span></div>' +
      '<div><strong>Milestone:</strong><span>' + card.dataset.milestone + '</span></div>' +
      '<div><strong>Status:</strong><span>' + card.dataset.status + '</span></div>' +
      '<div><strong>Due Date:</strong><span>' + card.dataset.dueDate + '</span></div>' +
      '<div><strong>Submitted:</strong><span>' + card.dataset.submitted + '</span></div>' +
      '<div class="details-comments"><strong>Comments:</strong><p>' + card.dataset.comments + '</p></div>' +
    '</div>'
  );
}

function initTheme() {
  var themeToggle = document.getElementById("themeToggle");
  var themeIcon = document.getElementById("themeIcon");
  var html = document.documentElement;
  var iconBasePath = window.location.pathname.includes("/adviserhtml/") ? "../assets/icons/" : "assets/icons/";
  var moonIcon = iconBasePath + "moon-stars-fill.svg";
  var sunIcon = iconBasePath + "sun-fill.svg";

  function setTheme(theme) {
    if (theme === "dark") {
      html.setAttribute("data-theme", "dark");
      if (themeIcon) themeIcon.src = sunIcon;
    } else {
      html.removeAttribute("data-theme");
      if (themeIcon) themeIcon.src = moonIcon;
    }

    localStorage.setItem("theme", theme);
  }

  var savedTheme = localStorage.getItem("theme");
  var prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  var startingTheme = savedTheme || (prefersDark ? "dark" : "light");
  setTheme(startingTheme);

  if (themeToggle) {
    themeToggle.addEventListener("click", function () {
      var currentTheme = html.getAttribute("data-theme") === "dark" ? "dark" : "light";
      setTheme(currentTheme === "dark" ? "light" : "dark");
    });
  }
}
