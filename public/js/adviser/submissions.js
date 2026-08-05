/* submission filter chips */
document.addEventListener('DOMContentLoaded', function () {
    var filterChips = document.querySelectorAll('.filter-chip');
    var submissionCards = document.querySelectorAll('.submission-card');
    var modal = document.getElementById('actionModal');
    var modalTitle = document.getElementById('modalTitle');
    var modalBody = document.getElementById('modalBody');
    var modalFooter = document.getElementById('modalFooter');
    var modalCloseBtn = document.getElementById('modalCloseBtn');
    var pendingAction = null;

    // filter chips
    filterChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var filter = this.dataset.filter;
            filterChips.forEach(function (c) { c.classList.remove('active'); });
            this.classList.add('active');

            var groupBlocks = document.querySelectorAll('.submission-group-block');
            groupBlocks.forEach(function (block) {
                var cards = block.querySelectorAll('.submission-card');
                var visibleCount = 0;
                cards.forEach(function (card) {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.classList.remove('is-hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('is-hidden');
                    }
                });
                // hide the whole group block if no cards match
                if (visibleCount === 0) {
                    block.classList.add('is-hidden');
                } else {
                    block.classList.remove('is-hidden');
                }
            });
        });
    });

    // group toggle (expand/collapse)
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('.group-toggle');
        if (!toggle) return;

        var groupName = toggle.dataset.group;
        var container = document.getElementById('group-' + groupName);
        if (!container) return;

        var isOpen = container.classList.contains('open');
        if (isOpen) {
            container.classList.remove('open');
            toggle.classList.remove('open');
        } else {
            container.classList.add('open');
            toggle.classList.add('open');
        }
    });

    // modal open / close
    function openModal(title, bodyHtml, footerHtml) {
        modalTitle.textContent = title;
        modalBody.innerHTML = bodyHtml;
        modalFooter.innerHTML = footerHtml;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        pendingAction = null;
    }

    modalCloseBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
    });

    // modal footer button clicks
    modalFooter.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-modal-action]');
        if (!btn) return;
        var action = btn.dataset.modalAction;
        if (action === 'cancel') {
            closeModal();
        } else if (action === 'confirm-approve') {
            var subId = pendingAction ? pendingAction.submissionId : null;
            closeModal();
            if (subId) updateSubmission(subId, 'approved', 'Approved by adviser.');
        } else if (action === 'confirm-revise') {
            var subId = pendingAction ? pendingAction.submissionId : null;
            var feedback = document.getElementById('modalFeedbackInput').value.trim();
            if (!feedback) {
                showInlineError('modalFeedbackInput', 'Please enter revision feedback.');
                return;
            }
            closeModal();
            if (subId) updateSubmission(subId, 'revision-requested', feedback);
        } else if (action === 'confirm-reject') {
            var subId = pendingAction ? pendingAction.submissionId : null;
            var reason = document.getElementById('modalFeedbackInput').value.trim();
            if (!reason) {
                showInlineError('modalFeedbackInput', 'Please enter a rejection reason.');
                return;
            }
            closeModal();
            if (subId) updateSubmission(subId, 'rejected', reason);
        }
    });

    function showInlineError(inputId, message) {
        var input = document.getElementById(inputId);
        if (!input) return;
        var existing = input.parentNode.querySelector('.modal-field-error');
        if (existing) existing.remove();
        var err = document.createElement('p');
        err.className = 'modal-field-error';
        err.textContent = message;
        input.parentNode.appendChild(err);
        input.classList.add('is-invalid');
    }

    // submission action buttons
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) return;

        var action = btn.dataset.action;
        var submissionId = btn.dataset.submissionId;

        if (action === 'approve') {
            pendingAction = { submissionId: submissionId };
            openModal(
                'Approve Submission',
                '<p class="modal-text">Are you sure you want to approve this submission?</p>',
                '<button class="btn-action-secondary" data-modal-action="cancel">Cancel</button>' +
                '<button class="btn-action-primary" data-modal-action="confirm-approve">Approve</button>'
            );
        } else if (action === 'reject') {
            pendingAction = { submissionId: submissionId };
            openModal(
                'Reject Submission',
                '<div class="modal-field">' +
                  '<label class="modal-label">Rejection Reason</label>' +
                  '<textarea id="modalFeedbackInput" class="modal-textarea" rows="3" placeholder="Explain why this submission is being rejected..."></textarea>' +
                '</div>',
                '<button class="btn-action-secondary" data-modal-action="cancel">Cancel</button>' +
                '<button class="btn-action-danger" data-modal-action="confirm-reject">Reject</button>'
            );
        } else if (action === 'revise') {
            pendingAction = { submissionId: submissionId };
            openModal(
                'Request Revision',
                '<div class="modal-field">' +
                  '<label class="modal-label">Revision Feedback</label>' +
                  '<textarea id="modalFeedbackInput" class="modal-textarea" rows="4" placeholder="Explain what the group needs to revise..."></textarea>' +
                '</div>',
                '<button class="btn-action-secondary" data-modal-action="cancel">Cancel</button>' +
                '<button class="btn-action-primary" data-modal-action="confirm-revise">Send Revision Request</button>'
            );
        } else if (action === 'preview') {
            openPreviewModal(submissionId);
        }
    });

    // preview modal with view/download
    function openPreviewModal(submissionId) {
        var card = document.querySelector('.submission-card[data-submission-id="' + submissionId + '"]');
        if (!card) return;

        var title = card.querySelector('h3').textContent;
        var group = card.querySelector('.submission-group').textContent;
        var badge = card.querySelector('.badge-status').textContent;
        var feedback = card.querySelector('.submission-feedback').textContent.replace('Feedback:', '').trim();
        var fileName = card.dataset.fileName || 'document.pdf';
        var uploader = card.dataset.uploader || '--';
        var date = card.dataset.date || '--';
        var size = card.dataset.size || '--';

        openModal(
            'Submission Preview',
            '<div class="preview-meta">' +
              '<div class="preview-meta-item"><span class="preview-meta-label">Date</span><span class="preview-meta-value">' + escHtml(date) + '</span></div>' +
              '<div class="preview-meta-item"><span class="preview-meta-label">Size</span><span class="preview-meta-value">' + escHtml(size) + '</span></div>' +
              '<div class="preview-meta-item"><span class="preview-meta-label">Uploaded by</span><span class="preview-meta-value">' + escHtml(uploader) + '</span></div>' +
              '<div class="preview-meta-item"><span class="preview-meta-label">Status</span><span class="badge-status ' + (card.querySelector('.badge-status').className.replace('badge-status', '').trim()) + '">' + escHtml(badge) + '</span></div>' +
            '</div>' +
            '<div class="preview-section">' +
              '<p class="preview-section-label">Document Preview</p>' +
              '<div class="preview-file">' +
                '<div class="preview-file-icon"><img src="' + BASE_URL + '/assets/icons/file-earmark-text.svg" alt="" class="bi"></div>' +
                '<div class="preview-file-info">' +
                  '<p class="preview-file-name">' + escHtml(fileName) + '</p>' +
                  '<p class="preview-file-meta">PDF Document &middot; ' + escHtml(size) + '</p>' +
                '</div>' +
              '</div>' +
            '</div>',
            '<button class="btn-action-secondary" data-modal-action="preview-view" data-id="' + submissionId + '">' +
              '<img src="' + BASE_URL + '/assets/icons/eye.svg" alt="" class="bi" style="width:14px;height:14px;margin-right:4px;"> Preview' +
            '</button>' +
            '<button class="btn-action-secondary" data-modal-action="preview-download" data-id="' + submissionId + '">' +
              '<img src="' + BASE_URL + '/assets/icons/download.svg" alt="" class="bi" style="width:14px;height:14px;margin-right:4px;"> Download' +
            '</button>' +
            '<button class="btn-action-primary" data-modal-action="cancel">Close</button>'
        );
    }

    // handle preview view/download buttons
    modalFooter.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-modal-action]');
        if (!btn) return;
        var action = btn.dataset.modalAction;
        if (action === 'preview-view') {
            var id = btn.dataset.id;
            window.open(BASE_URL + '/adviser/submissions-file?id=' + id, '_blank');
        } else if (action === 'preview-download') {
            var id = btn.dataset.id;
            window.open(BASE_URL + '/adviser/submissions-file?id=' + id + '&download=1', '_blank');
        }
    });

    // update submission via AJAX
    function updateSubmission(submissionId, status, feedback) {
        var card = document.querySelector('.submission-card[data-submission-id="' + submissionId + '"]');
        if (!card) return;

        var buttons = card.querySelectorAll('button');
        buttons.forEach(function (b) { b.disabled = true; });

        fetch(BASE_URL + '/adviser/submissions-update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                submission_id: submissionId,
                status: status,
                feedback: feedback
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                card.dataset.status = status;
                var badge = card.querySelector('.badge-status');
                if (badge) {
                    var labels = {
                        'approved': 'Approved',
                        'rejected': 'Rejected',
                        'revision-requested': 'Revision Requested',
                        'in-review': 'In Review'
                    };
                    var classes = {
                        'approved': 'badge-approved',
                        'rejected': 'badge-rejected',
                        'revision-requested': 'badge-review',
                        'in-review': 'badge-progress'
                    };
                    badge.textContent = labels[status] || status;
                    badge.className = 'badge-status ' + (classes[status] || 'badge-pending');
                }
                var feedbackEl = card.querySelector('.submission-feedback');
                if (feedbackEl) {
                    feedbackEl.innerHTML = '';
                    var strong = document.createElement('strong');
                    strong.textContent = 'Feedback:';
                    feedbackEl.appendChild(strong);
                    feedbackEl.appendChild(document.createTextNode(' ' + feedback));
                }
            } else {
                showNoticeModal(data.error || 'Failed to update submission.');
            }
        })
        .catch(function () {
            showNoticeModal('Something went wrong. Please try again.');
        })
        .finally(function () {
            buttons.forEach(function (b) { b.disabled = false; });
        });
    }

    // notice modal for errors
    function showNoticeModal(message) {
        openModal(
            'Notice',
            '<p class="modal-text"></p>',
            '<button class="btn-action-primary" data-modal-action="cancel">OK</button>'
        );
        var textEl = modalBody.querySelector('.modal-text');
        if (textEl) textEl.textContent = message;
    }

    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
});