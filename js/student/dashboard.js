const API = '../../api';


async function fetchUserData() {
  const userNameElement = document.getElementById('topbar-user');

  try {

    const res = await fetch(`${API}/auth/user.php`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    });
    const data = await res.json();

    if (!res.ok) {
      alert('Failed to fetch user data: ' + res.statusText);
      window.location.href = '../../pages/auth/auth.html';
      return;
    }

    const user = data.user;
    if (user && user.firstname && user.lastname) {
      userNameElement.textContent = `${user.firstname} ${user.lastname}`;
    }

  } catch (err) {
    alert('Network error: ' + err.message);
  }
}

document.addEventListener('DOMContentLoaded', fetchUserData);

async function logout() {

  const confirmed = confirm('Are you sure you want to log out?');
  if (!confirmed) return;

  try {
    const res = await fetch(`${API}/auth/logout.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    });
    const data = await res.json();

    if (!res.ok) {
      alert('Logout failed: ' + (data.error || 'Unknown error'));
      return;
    }

    window.location.href = '../../pages/auth/auth.html';
  } catch (err) {
    alert('Network error: ' + err.message);
  }
  
}

async function loadRecentActivities() {

    const box = document.getElementById('recentActivitiesBox');
    if (!box) return;

    try {
        const res = await fetch('../../api/activities.php', {
            method: 'GET',
            credentials: 'include',
        });
        const data = await res.json();

        if (!res.ok) {
            console.error('Failed to load activities:', data);
            return;
        }

        // remove existing items
        box.querySelectorAll('.activity-item').forEach(el => el.remove());

        if (!data.items || data.items.length === 0) {
            const msg = document.createElement('p');
            msg.className = 'text-muted small';
            msg.textContent = 'No recent activity.';
            box.appendChild(msg);
            return;
        }

        data.items.forEach(a => {
            const div = document.createElement('div');
            div.className = 'activity-item';
            div.innerHTML = `
                <img src="../../assets/icons/${a.icon}.svg" alt="" class="activity-avatar">
                <span>${a.description}</span>
            `;
            box.appendChild(div);
        });

    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', loadRecentActivities);
document.addEventListener('DOMContentLoaded', loadDashboardData);

async function loadDashboardData() {
    var box = document.querySelector('.box');
    if (!box) return;

    try {

        var res = await fetch('../../api/dashboard.php', {
            method: 'GET',
            credentials: 'include',
        });
        var data = await res.json();

        if (!res.ok) {
            console.error('Failed to load dashboard data:', data);
            return;
        }

        // overall progress
        var progressPercent = document.getElementById('dashProgressPercent');
        var progressBar = document.getElementById('dashProgressBar');
        var progressSummary = document.getElementById('dashProgressSummary');

        if (progressPercent) progressPercent.textContent = data.overall_progress + '%';

        if (progressBar) progressBar.style.width = data.overall_progress + '%';

        if (progressSummary) {
            var nextText = data.next_deadline_days !== null
                ? ' next deadline in ' + data.next_deadline_days + ' day' + (data.next_deadline_days !== 1 ? 's' : '')
                : '';
            progressSummary.textContent = data.done_count + ' of ' + data.total_count + ' milestones done' + nextText;
        }

        // days to defense
        var defenseDays = document.getElementById('dashDefenseDays');
        var defenseDate = document.getElementById('dashDefenseDate');

        if (defenseDays) defenseDays.textContent = data.days_to_defense !== null ? String(data.days_to_defense) : '--';
        
        if (defenseDate) defenseDate.textContent = data.defense_date || 'No defense scheduled';

        // expected turnaround
        var turnaroundEl = document.getElementById('dashTurnaround');
        if (turnaroundEl) {
            turnaroundEl.textContent = data.next_deadline_days !== null
                ? data.next_deadline_days + ' day' + (data.next_deadline_days !== 1 ? 's' : '')
                : '--';
        }

        // milestones
        var milestoneBox = document.getElementById('dashMilestoneList');
        if (milestoneBox) {

            // remove existing items
            var existing = milestoneBox.querySelectorAll('.milestone-item');
            for (var i = 0; i < existing.length; i++) {
                existing[i].remove();
            }

            if (!data.milestones || data.milestones.length === 0) {
                var msg = document.createElement('p');
                msg.className = 'text-muted small';
                msg.textContent = 'No milestones yet.';
                milestoneBox.appendChild(msg);
            } else {
                data.milestones.forEach(function(m){
                    var item = document.createElement('div');
                    item.className = 'milestone-item';

                    var nameP = document.createElement('p');
                    nameP.className = 'milestone-name';
                    nameP.textContent = m.name;

                    var row = document.createElement('div');
                    row.className = 'd-flex justify-content-between align-items-center';

                    var dateP = document.createElement('p');
                    dateP.className = 'milestone-date';
                    dateP.textContent = m.due_date_formatted || 'No due date';

                    var badge = document.createElement('span');
                    badge.className = 'badge-status ' + m.status_class;
                    badge.textContent = m.status_label;

                    row.appendChild(dateP);
                    row.appendChild(badge);

                    item.appendChild(nameP);
                    item.appendChild(row);
                    milestoneBox.appendChild(item);
                });
            }
        }

        // latest adviser fb
        var feedbackBox = document.getElementById('dashFeedbackBox');
        if (feedbackBox){

            var existingFb = feedbackBox.querySelectorAll('.feedback-card');
            for (var j = 0; j < existingFb.length; j++){
                existingFb[j].remove();
            }

            if (!data.feedback) {
                var fbMsg = document.createElement('p');
                fbMsg.className = 'text-muted small';
                fbMsg.textContent = 'No feedback yet.';
                feedbackBox.appendChild(fbMsg);
                
            } else {
                var fb = data.feedback;
                var card = document.createElement('div');
                card.className = 'feedback-card';

                var avatar = document.createElement('div');
                avatar.textContent = fb.initials;
                avatar.className = 'feedback-avatar';

                var info = document.createElement('div');

                var meta = document.createElement('p');
                meta.className = 'feedback-meta';
                meta.textContent = fb.author;

                var dateEl = document.createElement('p');
                dateEl.className = 'feedback-date';
                dateEl.textContent = fb.date_formatted;

                var textEl = document.createElement('p');
                textEl.className = 'feedback-text';
                textEl.textContent = '"' + fb.message + '"';

                info.appendChild(meta);
                info.appendChild(dateEl);
                info.appendChild(textEl);

                card.appendChild(avatar);
                card.appendChild(info);
                feedbackBox.appendChild(card);
            }
        }
    } catch (err) {
        console.error('Dashboard load error:', err);
    }
}

