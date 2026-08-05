let stagedFile = null;


/* preview modal */
const previewOverlay = document.getElementById('previewOverlay');
const previewClose = document.getElementById('previewClose');
const previewCloseBtn = document.getElementById('previewCloseBtn');
const previewTitle = document.getElementById('previewTitle');
const previewDate = document.getElementById('previewDate');
const previewSize = document.getElementById('previewSize');
const previewUploader = document.getElementById('previewUploader');
const previewStatus = document.getElementById('previewStatus');
const previewFileName = document.getElementById('previewFileName');
const previewFileMeta = document.getElementById('previewFileMeta');



function closePreview() {
    previewOverlay.classList.remove('show');
    document.body.style.overflow = '';
}

previewClose.addEventListener('click', closePreview);
previewCloseBtn.addEventListener('click', closePreview);
previewOverlay.addEventListener('click', (e) => {
    if (e.target === previewOverlay) closePreview();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && previewOverlay.classList.contains('show')) closePreview();
});


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


function bindPreviewButtons() {
    document.querySelectorAll('.btn-preview').forEach(btn => {
        btn.onclick = (e) => {
            const item = e.target.closest('.submission-item, .history-row');
            if (item) openPreview(item);
        };
    });
}

function openPreview(item) {
    const title = item.dataset.file_name || item.dataset.title || 'Document';
    const date = item.dataset.date;
    const size = item.dataset.size;
    const uploader = item.dataset.uploader;
    const status = item.dataset.status;
    const statusClass = item.dataset.statusClass;

    document.getElementById('previewTitle').textContent = item.dataset.title;
    document.getElementById('previewDate').textContent = date;
    document.getElementById('previewSize').textContent = formatSize(size);
    document.getElementById('previewUploader').textContent = uploader;
    const statusEl = document.getElementById('previewStatus');
    statusEl.textContent = status;
    statusEl.className = 'badge-status ' + statusClass;
    document.getElementById('previewFileName').textContent =
        title.toLowerCase().replace(/\s+/g, '-');
    document.getElementById('previewFileMeta').textContent = 'PDF Document • ' + formatSize(size);

    
    const fileUrl = item.dataset.fileUrl;
    const closeBtn = document.getElementById('previewCloseBtn');
    const downBtn = document.getElementById('previewDownload');
    const id = item.dataset.id;
    if (id) {
        closeBtn.onclick = () => {
            document.getElementById('previewOverlay').classList.remove('show');
            document.body.style.overflow = '';
        };
        downBtn.onclick = () => window.open(`./submissions-file?id=${id}&download=1`, '_blank');

        const viewBtn = document.getElementById('previewView');
        viewBtn.onclick = () => window.open(`./submissions-file?id=${id}`, '_blank');
    }

    document.getElementById('previewOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
}

bindPreviewButtons();