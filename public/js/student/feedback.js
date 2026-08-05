document.addEventListener('DOMContentLoaded', () => {
    setupFeedbackModal();
    setupChapterCards();
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

function setupChapterCards() {
    const cards = document.querySelectorAll('.chapter-card');
    cards.forEach(card => {
        card.addEventListener('click', () => {
            const chapter = card.dataset.chapter;
            const feedbacks = JSON.parse(card.dataset.feedbacks || '[]');
            openFeedback(chapter, feedbacks);
        });
    });
}

function openFeedback(chapter, feedbacks) {
    const overlay = document.getElementById('fbOverlay');
    const titleEl = document.getElementById('fbChapter');
    const bodyEl  = document.getElementById('fbBody');
    const previewBtn = document.getElementById('fbPreview');

    titleEl.textContent = chapter;

    if (!feedbacks || feedbacks.length === 0) {
        bodyEl.innerHTML = '<p class="fb-empty">No feedback.</p>';
        previewBtn.style.display = 'none';
    } else {
        bodyEl.innerHTML = feedbacks.map(item => `
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
        '&': '&', '<': '<', '>': '>', '"': '"', "'": '&#39;'
    }[c]));
}