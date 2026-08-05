/* sidebar toggle */
const sidebar = document.getElementById('sidebar');
const menuBtn = document.getElementById('menuBtn');
const overlay = document.getElementById('sidebarOverlay');

function toggleSidebar() {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

menuBtn.addEventListener('click', toggleSidebar);
overlay.addEventListener('click', toggleSidebar);

/* close sidebar on link click for mobile */
document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
    link.addEventListener('click', () => {
    if (window.innerWidth < 992) toggleSidebar();
    });
});