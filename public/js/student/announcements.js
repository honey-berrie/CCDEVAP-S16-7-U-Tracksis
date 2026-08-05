

function setupAnnouncementCards() {
    const cards = document.querySelectorAll('.notif-card');
    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            openAnnouncementCard(card);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupAnnouncementModal();
    setupAnnouncementCards();
});


function openAnnouncementCard(card){
    var data = JSON.parse(card.dataset.announcement);

    document.getElementById('annTitle').textContent = data.title;
    document.getElementById('annAvatar').textContent = data.author_initials || 'SY';
    document.getElementById('annSender').textContent = data.author;
    document.getElementById('annRole').textContent = data.author_role;
    document.getElementById('annMessage').textContent = data.message;

    var dt = new Date(data.created_at);

    document.getElementById('annDate').textContent = dt.toLocaleDateString('en-US', {
        month: 'long', day: 'numeric', year: 'numeric'
    });

    document.getElementById('annTime').textContent = dt.toLocaleTimeString('en-US', {
        hour: 'numeric', minute: '2-digit', hour12: true
    });

    card.classList.remove('unread');

    // mark as read on the server (we using MVC pattern, so we will send a request to the controller)
    fetch(BASE_URL + '/student/announcements-mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: data.id })
    });

    var overlay = document.getElementById('annOverlay');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
}

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