
document.addEventListener('DOMContentLoaded', function() {
    loadMilestones();
});

async function loadMilestones() {
    var box = document.getElementById('timeline-box');

    try {
        var res = await fetch('../../api/milestones.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        });
        var data = await res.json();

        if (!res.ok) {
            console.error('Failed to load milestones:', data);
            box.innerHTML = '<p class="text-muted small p-3">Failed to load milestones.</p>';
            return;
        }

        var items = box.querySelectorAll('.timeline-item');
        for (var i = 0; i < items.length; i++) {
            items[i].remove();
        }

        if (!data.items || data.items.length === 0) {
            var msg = document.createElement('p');
            msg.className = 'text-muted small p-3';
            msg.textContent = 'No milestones yet.';
            box.appendChild(msg);
            return;
        }

        data.items.forEach(function(milestone) {
            box.appendChild(buildTimelineItem(milestone));
        });
    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

function buildTimelineItem(m) {
    var item = document.createElement('div');
    item.className = 'timeline-item';

    var numDiv = document.createElement('div');
    numDiv.className = 'timeline-number';
    numDiv.textContent = m.display_order;

    // ── body ──
    var body = document.createElement('div');
    body.className = 'timeline-body';

    // name line: "Chapter 1 --"
    var nameP = document.createElement('p');
    nameP.className = 'timeline-name';
    nameP.textContent = m.name + ' ';
    var descSpan = document.createElement('span');
    descSpan.textContent = m.description || '--';
    nameP.appendChild(descSpan);

    // date line
    var dateP = document.createElement('p');
    dateP.className = 'timeline-date';
    if (m.due_date_formatted) {
        dateP.textContent = 'due ' + m.due_date_formatted;
        if (m.is_overdue) {
            var overdueSpan = document.createElement('span');
            overdueSpan.className = 'text-danger';
            overdueSpan.textContent = ' (overdue)';
            dateP.appendChild(overdueSpan);
        }
    } else {
        dateP.textContent = 'No due date';
    }

    // progress bar
    var progressWrap = document.createElement('div');
    progressWrap.className = 'progress timeline-progress';

    var progressBar = document.createElement('div');
    progressBar.className = 'progress-bar';
    progressBar.style.width = m.progress + '%';

    if (m.status === 'in-review') {
        progressBar.style.background = 'var(--warning, #f59e0b)';
    } else if (m.status === 'rejected') {
        progressBar.style.background = 'var(--danger, #ef4444)';
    }

    progressWrap.appendChild(progressBar);

    body.appendChild(nameP);
    body.appendChild(dateP);
    body.appendChild(progressWrap);

    // ── side (badge + details) ──
    var side = document.createElement('div');
    side.className = 'timeline-side';

    var badge = document.createElement('span');
    badge.className = 'badge-status ' + m.status_class;
    badge.textContent = m.status_label;

    var detailsLink = document.createElement('a');
    detailsLink.href = '#';
    detailsLink.className = 'timeline-details';
    detailsLink.textContent = 'Details >';

    side.appendChild(badge);
    side.appendChild(detailsLink);

    // ── assemble ──
    item.appendChild(numDiv);
    item.appendChild(body);
    item.appendChild(side);

    return item;
}