

document.addEventListener('DOMContentLoaded', () => {
    setupAnnouncementModal();
    loadAnnouncements();
});

function setupAnnouncementModal(){
    const overlay = document.getElementById('annOverlay');

    document.getElementById('annClose').addEventListener('click', closeAnnouncement);

    document.getElementById('annCloseBtn').addEventListener('click', closeAnnouncement);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeAnnouncement();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('show')) closeAnnouncement();
    });
}

function closeAnnouncement() {
    var overlay = document.getElementById('annOverlay');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
}

async function loadAnnouncements() {
    var list = document.getElementById('notifList');

    try {
        var res = await fetch('../../api/announcements.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        });
        var data = await res.json();

        if (!res.ok) {
            console.error('Failed to load announcements:', data);
            list.innerHTML = '<p class="text-muted small">Failed to load announcements.</p>';
            return;
        }

        // Remove any existing cards
        var cards = list.querySelectorAll('.notif-card');
        for (var i = 0; i < cards.length; i++){
            cards[i].remove();
        }
        
        console.log(data.items)

        if (!data.items || data.items.length === 0) {
            var msg = document.createElement('p');
            msg.className = 'text-muted small';
            msg.textContent = 'No announcements yet.';
            list.appendChild(msg);
            return;
        }

        data.items.forEach(function(item) {
            list.appendChild(buildNotifCard(item));
        });

    } catch (err) {

        alert('Network error: ' + err.message);

    }
}


function buildNotifCard(item) {
    var card = document.createElement('div');
    card.className = 'notif-card';
    if (!item.is_read) {
        card.classList.add('unread');
    }
    card._data = item;

    var iconWrap = document.createElement('div');
    iconWrap.className = 'notif-icon';
    var iconImg = document.createElement('img');
    iconImg.src = '../../assets/icons/megaphone-fill.svg';
    iconImg.alt = '';
    iconImg.className = 'bi';
    iconWrap.appendChild(iconImg);

    var body = document.createElement('div');
    body.className = 'notif-body';

    var titleP = document.createElement('p');
    titleP.className = 'notif-title';
    titleP.textContent = item.title;

    var metaP = document.createElement('p');
    metaP.className = 'notif-meta';
    metaP.appendChild(document.createTextNode('From '));
    var nameSpan = document.createElement('span');
    nameSpan.textContent = item.sender_name;
    metaP.appendChild(nameSpan);
    metaP.appendChild(document.createTextNode(' . ' + item.sender_role));

    var timeP = document.createElement('p');
    timeP.className = 'notif-time';
    timeP.textContent = item.time_ago;

    body.appendChild(titleP);
    body.appendChild(metaP);
    body.appendChild(timeP);

    var mark = document.createElement('div');
    mark.className = 'notif-mark';

    card.appendChild(iconWrap);
    card.appendChild(body);
    card.appendChild(mark);

    card.addEventListener('click', function() { openAnnouncement(card); });
    return card;
}

function openAnnouncement(card) {
    var item = card._data;
    if (!item) return;

    document.getElementById('annTitle').textContent = item.title;
    document.getElementById('annAvatar').textContent = item.sender_initials || 'SY';
    document.getElementById('annSender').textContent = item.sender_name;
    document.getElementById('annRole').textContent = item.sender_role;
    document.getElementById('annMessage').textContent = item.message;

    var dt = new Date(item.created_at);

    document.getElementById('annDate').textContent = dt.toLocaleDateString('en-US', {
        month: 'long', day: 'numeric', year: 'numeric'
    });

    document.getElementById('annTime').textContent = dt.toLocaleTimeString('en-US', {
        hour: 'numeric', minute: '2-digit', hour12: true
    });

    if (card.classList.contains('unread')){
        // mark as read in db
        fetch('../../api/announcements.php?action=read&id=' + item.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        }).catch(function() {});
    }

    card.classList.remove('unread');
    item.is_read = true;

    var overlay = document.getElementById('annOverlay');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';

    
}