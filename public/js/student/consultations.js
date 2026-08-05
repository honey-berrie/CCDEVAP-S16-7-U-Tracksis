function buildHistoryItem(item) {
    var div = document.createElement('div');
    div.className = 'history-item';

    // date column
    var dateDiv = document.createElement('div');
    dateDiv.className = 'history-date';

    var dateMain = document.createElement('p');
    dateMain.className = 'history-date-main';
    dateMain.textContent = item.date_formatted || '--';

    var dateSub = document.createElement('p');
    dateSub.className = 'history-date-sub';
    dateSub.textContent = item.time_formatted || 'Unscheduled';
    dateDiv.appendChild(dateMain);
    dateDiv.appendChild(dateSub);

    // body
    var body = document.createElement('div');
    body.className = 'history-body';

    var topicP = document.createElement('p');
    topicP.className = 'history-chapter';
    topicP.textContent = item.topic + ' ';

    var recSpan = document.createElement('span');
    recSpan.textContent = item.recipient_name;
    topicP.appendChild(recSpan);

    var msgP = document.createElement('p');
    msgP.className = 'history-comments';
    msgP.textContent = '"' + (item.agenda || 'No agenda provided') + '"';

    body.appendChild(topicP);
    body.appendChild(msgP);

    // status badge
    var side = document.createElement('div');
    side.className = 'history-side';

    var badge = document.createElement('span');
    badge.className = 'badge-status ' + item.status_class;
    badge.textContent = item.status_label;
    side.appendChild(badge);

    div.appendChild(dateDiv);
    div.appendChild(body);
    div.appendChild(side);

    return div;
}

function renderHistory(items) {
    var list = document.getElementById('historyList');
    list.innerHTML = '';

    if (!items || items.length === 0) {
        var empty = document.createElement('p');
        empty.className = 'history-empty';
        empty.textContent = 'No consultation requests yet.';
        list.appendChild(empty);
        return;
    }

    items.forEach(function(item) {
        list.appendChild(buildHistoryItem(item));
    });
}

function showNotice(message, type) {
    var overlay = document.getElementById('noticeOverlay');
    var eyebrow = document.getElementById('noticeEyebrow');
    var title = document.getElementById('noticeTitle');

    document.getElementById('noticeMessage').textContent = message;

    if (type === 'error') {
        eyebrow.textContent = 'ERROR';
        eyebrow.classList.add('is-error');
        title.textContent = 'Something went wrong';
    } else {
        eyebrow.textContent = 'SUCCESS';
        eyebrow.classList.remove('is-error');
        title.textContent = 'Request sent';
    }

    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeNotice() {
    var overlay = document.getElementById('noticeOverlay');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
}

function setupNoticeModal() {
    var overlay = document.getElementById('noticeOverlay');

    document.getElementById('noticeCloseBtn').addEventListener('click', closeNotice);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeNotice();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('show')) closeNotice();
    });
}

function prependHistoryItem(item) {
    var list = document.getElementById('historyList');
    var emptyState = list.querySelector('.history-empty');
    if (emptyState) emptyState.remove();

    list.insertBefore(buildHistoryItem(item), list.firstChild);
}

function setupConsultationForm() {
    var form = document.getElementById('consultationForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var recipientId = document.getElementById('recipientSelect').value;
        var topic = document.getElementById('topicInput').value.trim();
        var agenda = document.getElementById('agendaInput').value.trim();
        var date = document.getElementById('scheduledDate').value;
        var time = document.getElementById('scheduledTime').value;

        if (!recipientId || !topic) {
            showNotice('Please select a recipient and enter a topic.', 'error');
            return;
        }

        var submitBtn = form.querySelector('button[type="submit"]');
        var originalLabel = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';

        // we using MVC pattern, so we send the request to the controller and let it hit the model
        fetch(BASE_URL + '/student/consultations-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                recipient_id: recipientId,
                topic: topic,
                agenda: agenda,
                date: date,
                time: time
            })
        })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(result) {
                if (!result.ok || !result.data.success) {
                    showNotice((result.data && result.data.error) || 'Failed to send the consultation request.', 'error');
                    return;
                }

                prependHistoryItem(result.data.consultation);
                form.reset();
                showNotice('Your consultation request has been sent.', 'success');
            })
            .catch(function() {
                showNotice('Something went wrong while sending your request. Please try again.', 'error');
            })
            .finally(function() {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel;
            });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    renderHistory(typeof consultationsData !== 'undefined' ? consultationsData : []);
    setupConsultationForm();
    setupNoticeModal();
});
