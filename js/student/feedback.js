
document.addEventListener('DOMContentLoaded', () => {
    setupFeedbackModal();
    loadFeedbacks();
});

function setupFeedbackModal() {
    const overlay = document.getElementById('fbOverlay');
    if (!overlay) return;

    document.getElementById('fbClose').addEventListener('click', closeFeedback);
    document.getElementById('fbCloseBtn').addEventListener('click', closeFeedback);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeFeedback();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('show')) closeFeedback();
    });
}

function closeFeedback() {
    const overlay = document.getElementById('fbOverlay');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
}

async function loadFeedbacks() {
    const list = document.querySelector('.chapter-list');
    if (!list) return;

    try {
        const res = await fetch(`../../api/feedback.php`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        });
        const data = await res.json();

        if (!res.ok) {
            console.error('Failed to load feedback:', data);
            return;
        }

        // remove current chapter cards
        list.querySelectorAll('.chapter-card').forEach(c => c.remove());

        const feedbackCount = data.total;
        if (feedbackCount === 0) {
            const msg = document.createElement('p');
            msg.className = 'text-muted small';
            msg.textContent = 'No feedback yet.';
            list.appendChild(msg);
            return;
        }

        console.log(data.grouped)

        data.grouped.forEach(g => list.appendChild(buildChapterCard(g)));
    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

function buildChapterCard(g) {
    const card = document.createElement('div');
    card.className = 'chapter-card';
    card.dataset.chapter = g.chapter;

    console.log(g);

    card.innerHTML = `
        <div class="chapter-icon">
            <img src="../../assets/icons/chat-left-text-fill.svg" alt="" class="bi">
        </div>
        <div class="chapter-info">
            <p class="chapter-title">${esc(g.chapter)}</p>
            <p class="chapter-meta"><span>${g.count}</span> feedback . Last updated ${esc(g.last_updated)}</p>
        </div>
        <div class="chapter-side">
            <span class="chapter-count">${g.count}</span>
            <img src="../../assets/icons/chevron-right.svg" alt="" class="bi chapter-chevron">
        </div>
    `;

    card.addEventListener('click', () => openFeedback(g));
    return card;
}

async function openFeedback(group) {
    const overlay = document.getElementById('fbOverlay');
    const titleEl = document.getElementById('fbChapter');
    const bodyEl  = document.getElementById('fbBody');

    titleEl.textContent = group.chapter;

    if (!group.items || group.items.length === 0) {
        bodyEl.innerHTML = '<p class="fb-empty">No feedback.</p>';
    } else {
        bodyEl.innerHTML = group.items.map(item => `
            <div class="fb-item">
                <div class="fb-item-header">
                    <div class="fb-avatar">${esc(item.initials || 'SY')}</div>
                    <div class="fb-author">
                        <p class="fb-name">${esc(item.author)} <span>. ${esc(item.author_role)}</span></p>
                        <p class="fb-time">${esc(item.time_ago)}</p>
                    </div>
                </div>
                <p class="fb-message">"${esc(item.message)}"</p>
            </div>
        `).join('');
    }

    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}
