
document.addEventListener('DOMContentLoaded', function() {
    loadConsultations();
    setupConsultationForm();
});

async function loadConsultations(){
    var historyBox = document.getElementById('historyBox');
    var recipientInput = document.getElementById('recipientInput');

    if (!historyBox) return;

    try {
        var res = await fetch('../../api/consultations.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        });
        var data = await res.json();

        if (!res.ok) {
            console.error('Failed to load consultations:', data);
            return;
        }

        if (recipientInput && data.recipients && data.recipients.length > 0){
            var parent = recipientInput.parentNode;
            var select = document.createElement('select');
            select.className = 'form-control';
            select.id = 'recipientSelect';
            var defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.disabled = true;
            defaultOpt.selected = true;
            defaultOpt.textContent = 'Select adviser or panel member';
            select.appendChild(defaultOpt);

            data.recipients.forEach(function(r) {
                var opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.name + ' (' + r.role + ')';
                select.appendChild(opt);
            });

            recipientInput.replaceWith(select);
        }

        
        var existingItems = historyBox.querySelectorAll('.history-item');
        for (var x = 0; x < existingItems.length; x++) {
            existingItems[x].remove();
        }

        if (!data.items || data.items.length === 0){
            var msg = document.createElement('p');
            msg.className = 'text-muted small';
            msg.textContent = 'No consultation history yet.';
            historyBox.appendChild(msg);
        } else {
            data.items.forEach(function(item) {
                historyBox.appendChild(buildHistoryItem(item));
            });
        }
    } catch (err){
        alert('Network error: ' + err.message);
    }
}

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

function setupConsultationForm(){

    var form = document.getElementById('consultationForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var select = document.getElementById('recipientSelect');
        var topicInput = document.getElementById('topicInput');
        var agendaInput = document.getElementById('agendaInput');
        var dateInput = document.getElementById('scheduledDate');
        var timeInput = document.getElementById('scheduledTime');

        var recipientId = select ? select.value : null;
        var topic = topicInput ? topicInput.value.trim() : '';
        var agenda = agendaInput ? agendaInput.value.trim() : '';
        var dateVal = dateInput ? dateInput.value : '';
        var timeVal = timeInput ? timeInput.value : '';

        if (!recipientId) {
            alert('Please select a recipient.');
            return;
        }

        if (!topic) {
            alert('Please enter a topic.');
            return;
        }

        if (!dateInput){
            alert('Please select a date.');
            return;
        }

        if (!timeInput){
            alert('Please select a time.');
            return;
        }

        var scheduledSlot = null;
        if (dateVal && timeVal) {
            scheduledSlot = dateVal + ' ' + timeVal + ':00';
        } else if (dateVal) {
            scheduledSlot = dateVal + ' 00:00:00';
        }

        var submitBtn = form.querySelector('button[type="submit"]');
        var originalLabel = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';

        var body = {
            recipient_id: parseInt(recipientId),
            topic: topic,
            agenda: agenda
        };
        if (scheduledSlot) {
            body.proposed_schedule = scheduledSlot;
        }

        fetch('../../api/consultations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(body)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {

            if (data.error) {
                alert(data.error);
                return;
            }

            alert('Consultation request sent!');
            
            // clear the inputs again
            topicInput.value = '';
            agendaInput.value = '';
            dateInput.value = '';
            timeInput.value = '';

            loadConsultations();
        })
        .catch(function(err) {
            alert('Network error: ' + err.message);
        })
        .finally(function() {
            submitBtn.disabled = false;
            submitBtn.textContent = originalLabel;
        });
    });
}