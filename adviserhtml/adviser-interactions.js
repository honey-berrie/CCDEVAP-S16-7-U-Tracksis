

document.addEventListener("DOMContentLoaded", function () {
  createModal();
  connectButtons();
  connectSearchBars();
});

function connectButtons() {
  document.addEventListener("click", function (event) {
    var button = event.target.closest("[data-action]");

    if (!button) {
      return;
    }

    var action = button.dataset.action;

    if (action === "review-submissions") {
      window.location.href = "thesis-adviser-submissions.html";
    }

    if (action === "schedule-consultation") {
      openModal("Schedule consultation", getScheduleForm());
    }

    if (action === "reschedule-consultation") {
      openModal("Reschedule consultation", getRescheduleForm(button));
    }

    if (action === "join-consultation") {
      alert("Opening Zoom meeting for this consultation. In a real system, this would open the actual Zoom link.");
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

    if (action === "download-submission") {
      alert("Downloading selected submission. In the real system, this would download the uploaded document.");
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
  return (
    '<form class="demo-form">' +
      '<label>Group</label>' +
      '<select required>' +
        '<option value="">Choose a group</option>' +
        '<option>Group 1</option>' +
        '<option>Group 2</option>' +
        '<option>Group 3</option>' +
        '<option>Group 4</option>' +
      '</select>' +
      '<label>Date</label>' +
      '<input type="date" required>' +
      '<label>Time</label>' +
      '<input type="time" required>' +
      '<label>Agenda</label>' +
      '<textarea rows="4" placeholder="Example: Review chapter 4 results"></textarea>' +
      '<button class="btn-outline" type="submit">Save consultation</button>' +
    '</form>'
  );
}

function getRescheduleForm(button) {
  var card = button.closest(".consultation-card");
  var title = card ? card.querySelector("h4").textContent : "Selected consultation";

  return (
    '<form class="demo-form">' +
      '<p class="modal-note">' + title + '</p>' +
      '<label>New date</label>' +
      '<input type="date" required>' +
      '<label>New time</label>' +
      '<input type="time" required>' +
      '<label>Reason</label>' +
      '<textarea rows="4" placeholder="Reason for rescheduling"></textarea>' +
      '<button class="btn-outline" type="submit">Save new schedule</button>' +
    '</form>'
  );
}

function getCommentForm(button) {
  var card = button.closest(".history-card");
  var comment = "";

  if (card) {
    comment = card.querySelector(".comment-box p").textContent;
  }

  return (
    '<form class="demo-form">' +
      '<label>Comment</label>' +
      '<textarea rows="5">' + comment + '</textarea>' +
      '<button class="btn-outline" type="submit">Save comment</button>' +
    '</form>'
  );
}

function getRevisionForm(button) {
  var card = button.closest(".submission-card");
  var title = card ? card.querySelector("h3").textContent : "Selected submission";

  return (
    '<form class="demo-form">' +
      '<p class="modal-note">' + title + '</p>' +
      '<label>Revision comment</label>' +
      '<textarea rows="5" placeholder="Explain what the group needs to revise" required></textarea>' +
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
