
let currentGroupId = null;
let stagedFile = null;

// click and drag to upload
function setupUpload() {
    const dropZone = document.getElementById('uploadDropzone');
    const fileInput = document.getElementById('fileInput');
    const submitBtn = document.getElementById('submitBtn');

    dropZone.addEventListener('click', (e) => {

        if (e.target.closest('.btn-upload')) return;

        if (stagedFile){
            const confirmed = confirm(`Replace "${stagedFile.name}" with a new file?`);
            if (!confirmed) return;
            stagedFile = null;
            updateUploadUI();
        }
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length === 0) return;
        stageFile(fileInput.files[0]);
        fileInput.value = '';
    });

    ['dragenter', 'dragover'].forEach(ev =>
        dropZone.addEventListener(ev, e => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    }));

    ['dragleave', 'drop'].forEach(ev =>
        dropZone.addEventListener(ev, e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    }));

    dropZone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) stageFile(e.dataTransfer.files[0]);
    });

    submitBtn.addEventListener('click', () => {
        if (!stagedFile) 
            return;

        uploadFile(stagedFile);
        stagedFile = null;
        updateUploadUI();
    });

}

function stageFile(file) {

    if (file.type !== 'application/pdf'){
        alert(`"${file.name}" is not a PDF.`);
        return;
    }

    if (file.size > 25 * 1024 * 1024){
        alert(`"${file.name}" exceeds 25MB.`);
        return;
    }

    stagedFile = file;
    updateUploadUI();
}

function updateUploadUI() {
    const title = document.getElementById('uploadTitle');
    const helper = document.getElementById('uploadHelper');
    const submitBtn = document.getElementById('submitBtn');

    if (!stagedFile){
        title.textContent = 'Drop PDF or click to choose';
        helper.textContent = 'PDF up to 25MB';
        submitBtn.disabled = true;
    } else{
        title.textContent = stagedFile.name;
        helper.textContent = formatSize(stagedFile.size) + ' • PDF';
        submitBtn.disabled = false;
    }
}

function formatSize(bytes) {
    return (bytes / 1048576).toFixed(1) + ' MB';
}


setupUpload();

async function uploadFile(file) {

    const docType = document.getElementById('docType');
    if (!docType.value) {
        alert('Please select a document type first.');
        return;
    }

    // get the current group ID from the server
    if (!currentGroupId) {
        try {
            const res = await fetch(`../../api/get_user_group.php`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
            });
            const data = await res.json();

            if (!res.ok) {
                alert('Failed to fetch group data: ' + res.statusText);
                return;
            }

            if (!data.group) {
                alert('You are not assigned to any group yet.');
                return;
            }

            currentGroupId = data.group.id;
        } catch (err) {
            alert('Network error: ' + err.message);
            return;
        }
    }

    const form = new FormData();
    form.append('group_id', currentGroupId);
    form.append('document_type', docType.value);
    form.append('title', getTitle(docType.value));
    form.append('file', file);

    const submitBtn = document.getElementById('submitBtn');
    const submitLabel = document.getElementById('submitBtnLabel');
    submitBtn.disabled = true;
    submitLabel.textContent = 'Uploading…';

    try {
        const res = await fetch(`../../api/submissions.php`, {
            method: 'POST',
            body: form,
            credentials: 'include', 
        });
        const data = await res.json();

        if (!res.ok) {
            alert(data.error || 'Upload failed');
            return; 
        }

        console.log('Upload successful:', data);

        // await loadSubmissions();

        alert(`Uploaded: ${file.name}`);

        await loadSubmissions();

    } catch (err) {

        alert('Network error: ' + err.message);

    } finally {

        submitLabel.textContent = 'Submit';
        submitBtn.disabled = !stagedFile;
        
    }
}

function getTitle(v) {
    return {
        'title-proposal': 'Title Proposal',
        'chapter-1': 'Chapter 1', 'chapter-2': 'Chapter 2', 'chapter-3': 'Chapter 3',
        'chapter-4': 'Chapter 4', 'chapter-5': 'Chapter 5',
        'final-thesis': 'Final Thesis', 'revision': 'Revision',
    }[v] || v;
}

function statusBadgeClass(s) {
    return {
        in_review: 'badge-review',
        approved: 'badge-approved',
        rejected: 'badge-rejected',
        revision_requested: 'badge-progress',
    }[s] || '';
}

function prettyStatus(s) {
    return {
        in_review: 'In Review',
        approved: 'Approved',
        rejected: 'Rejected',
        revision_requested: 'Revision Requested',
    }[s] || s;
}

function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}

async function loadSubmissions() {

    const recentSubmissionsBox = document.getElementById('recentSubmissionsBox');
    const historyList = document.getElementById('historyList');

    try {
        const res = await fetch(`../../api/submissions.php`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
        });
        const data = await res.json();

        if (!res.ok) {
            alert('Failed to fetch submissions: ' + res.statusText);
            return;
        }

        const submissions = Array.isArray(data.submissions) ? data.submissions : [];
        const recent = submissions.slice(0, 3);
        const history = submissions.slice(0, 10);

        // clear recent submissions list
        recentSubmissionsBox.querySelectorAll('.submission-item').forEach(item => item.remove());

        // display recent submissions
        if (recent.length === 0) {
            const msg = document.createElement('p');
            msg.className = 'text-muted small';
            msg.textContent = 'No submissions yet.';
            recentSubmissionsBox.appendChild(msg);
        } else {
            recent.forEach(s => recentSubmissionsBox.appendChild(buildRecentItem(s)));
        }

        // clear history list
        historyList.querySelectorAll('.history-row').forEach(item => item.remove());

        // display history
        if (history.length === 0) {
            const msg = document.createElement('p');
            msg.className = 'text-muted small';
            msg.textContent = 'No submission history.';
            historyList.appendChild(msg);
        } else {
            history.forEach(s => historyList.appendChild(buildHistoryRow(s)));
        }

        bindPreviewButtons();

    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

function buildRecentItem(s) {

    const div = document.createElement('div');
    div.className = 'submission-item';
    div.dataset.id = s.id;
    div.dataset.title = getTitle(s.document_type);
    div.dataset.date = formatDate(s.uploaded_at);
    div.dataset.size = formatSize(s.file_size);
    div.dataset.status = prettyStatus(s.status);
    div.dataset.statusClass = statusBadgeClass(s.status);
    div.dataset.uploader = s.uploader_name || '';
    div.dataset.fileUrl = '/' + (s.file_path || '');

    div.innerHTML = `
        <div class="submission-thumb">
            <img src="../../assets/icons/file-earmark-text.svg" alt="" class="bi">
        </div>
        <div class="submission-info">
            <p class="submission-name">${esc(getTitle(s.document_type))}</p>
            <p class="submission-meta">${esc(formatDate(s.uploaded_at))} . ${esc(formatSize(s.file_size))}</p>
        </div>
        <div class="submission-side">
            <span class="badge-status ${statusBadgeClass(s.status)}">${esc(prettyStatus(s.status))}</span>
            <button type="button" class="submission-view btn-preview">View</button>
        </div>
    `;

    return div;
}

function buildHistoryRow(s) {
    const div = document.createElement('div');
    div.className = 'history-row';
    div.dataset.id = s.id;
    div.dataset.title = getTitle(s.document_type);
    div.dataset.date = formatDate(s.uploaded_at);
    div.dataset.size = formatSize(s.file_size);
    div.dataset.status = prettyStatus(s.status);
    div.dataset.statusClass = statusBadgeClass(s.status);
    div.dataset.uploader = s.uploader_name || '';
    div.dataset.fileUrl = '/' + (s.file_path || '');

    div.innerHTML = `
        <div class="history-bullet"></div>
        <div class="history-info">
            <p class="history-name">${esc(getTitle(s.document_type))}</p>
            <p class="history-date">${esc(formatDate(s.uploaded_at))}</p>
        </div>
        <div class="history-side">
            <span class="badge-status ${statusBadgeClass(s.status)}">${esc(prettyStatus(s.status))}</span>
            <button type="button" class="history-download btn-preview">View</button>
        </div>
    `;

    return div;
}

function formatDate(d) {
    if (!d) return '--';
    const date = new Date(d);
    if (isNaN(date.getTime())) return '--';
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function bindPreviewButtons() {
    document.querySelectorAll('.btn-preview').forEach(btn => {
        btn.onclick = (e) => {
            const item = e.target.closest('.submission-item, .history-row');
            if (item) openPreview(item);
        };
    });
}

function openPreview(item) {
    const title = item.dataset.title;
    const date = item.dataset.date;
    const size = item.dataset.size;
    const uploader = item.dataset.uploader;
    const status = item.dataset.status;
    const statusClass = item.dataset.statusClass;

    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewDate').textContent = date;
    document.getElementById('previewSize').textContent = size;
    document.getElementById('previewUploader').textContent = uploader;
    const statusEl = document.getElementById('previewStatus');
    statusEl.textContent = status;
    statusEl.className = 'badge-status ' + statusClass;
    document.getElementById('previewFileName').textContent =
        title.toLowerCase().replace(/\s+/g, '-') + '.pdf';
    document.getElementById('previewFileMeta').textContent = 'PDF Document • ' + size;

    // open the actual PDF in a new tab
    const fileUrl = item.dataset.fileUrl;
    const closeBtn = document.getElementById('previewCloseBtn');
    const downBtn = document.getElementById('previewDownload');
    const id = item.dataset.id;
    if (id) {
        // close modal on click of close button, do not open new tab
        closeBtn.onclick = () => {
            document.getElementById('previewOverlay').classList.remove('show');
            document.body.style.overflow = '';
        }
        downBtn.onclick = () => window.location.href = `../../api/submissions_download.php?id=${id}&download=1`;
    }

    document.getElementById('previewOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
}

document.addEventListener('DOMContentLoaded', loadSubmissions);