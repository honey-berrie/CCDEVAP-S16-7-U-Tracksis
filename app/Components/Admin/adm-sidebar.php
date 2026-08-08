<aside class="sidebar">
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="U-Tracksis Logo" class="sidebar-logo"/>
        <div>
            <p class="sidebar-title">U-Tracksis</p>
            <p class="sidebar-subtitle">ADMIN</p>
        </div>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" type="button" aria-label="Close menu">
          <img src="<?= BASE_URL ?>/assets/icons/x-lg.svg" alt="" class="bi">
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/admin/adm-dashboard" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/grid-1x2-fill.svg" alt="" class="nav-icon">
        Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-users" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/people-fill.svg" alt="" class="nav-icon">
        User Management
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-analytics" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/bar-chart-fill.svg" alt="" class="nav-icon">
        Analytics & Reports
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-records" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/card-text.svg" alt="" class="nav-icon">
        Thesis Records
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-files" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/file-earmark-text-fill.svg" alt="" class="nav-icon">
        Uploaded Files
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-feedback" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/chat-left-text-fill.svg" alt="" class="nav-icon">
        Feedback History
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-archive" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/archive-fill.svg" alt="" class="nav-icon">
        Archive
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-announcements" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/megaphone-fill.svg" alt="" class="nav-icon">
        Announcements
        </a>
        <a href="<?= BASE_URL ?>/admin/adm-settings" class="nav-link">
        <img src="<?= BASE_URL ?>/assets/icons/sliders.svg" alt="" class="nav-icon">
        Settings
        </a>
    </nav>

    <div class="sidebar-footer">
        <span>Theme</span>
        <button class="theme-toggle" id="themeToggle" type="button" title="Toggle theme">
          <img src="<?= BASE_URL ?>/assets/icons/moon-stars-fill.svg" alt="" class="bi" id="themeIcon">
        </button>
    </div>
</aside>