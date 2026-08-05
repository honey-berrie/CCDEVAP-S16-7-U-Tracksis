const html = document.documentElement;

const ICON_MOON = '../../../assets/icons/moon-stars-fill.svg';
const ICON_SUN = '../../../assets/icons/sun-fill.svg';

function loadAdmSideBar() {
    fetch("../../Components/Admin/adm-sidebar.php")
        .then(code => code.text())
        .then(data => {
            document.getElementById("sidebar_placeholder").innerHTML = data;

            initTheme();
            setupThemeToggle();
            setActiveSidebarLink();
            getTime();
            setupSidebarCloseButton();
        });
}

function renderAdminTopBar() {
    const mainContainer = document.querySelector(".main-container");
    if (!mainContainer) return;

    const topBar = document.createElement("div");
    topBar.className = "top-bar";
    topBar.innerHTML = `
        <button class="mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="Open menu">
            <img src="../../../assets/icons/list.svg" alt="" class="bi">
        </button>
        <div class="topbar-actions">
            <button class="btn-logout" type="button" id="adminLogoutBtn">
                <img src="../../../assets/icons/box-arrow-left.svg" alt="" class="bi">
                <span>Logout</span>
            </button>
        </div>
    `;

    mainContainer.prepend(topBar);

    document.getElementById("adminLogoutBtn").addEventListener("click", openLogoutModal);
    setupMobileSidebarToggle();
    renderLogoutModal();
}

function renderLogoutModal() {
    if (document.getElementById("logoutModalOverlay")) return;

    const modal = document.createElement("div");
    modal.className = "modal-overlay";
    modal.id = "logoutModalOverlay";
    modal.innerHTML = `
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Log Out</h3>
                <button type="button" class="modal-close" id="logoutModalCloseBtn">&times;</button>
            </div>
            <p>Are you sure you want to log out?</p>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-admin" id="logoutCancelBtn">Cancel</button>
                <button type="button" class="btn-danger-admin" id="logoutConfirmBtn">Logout</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    document.getElementById("logoutModalCloseBtn").addEventListener("click", closeLogoutModal);
    document.getElementById("logoutCancelBtn").addEventListener("click", closeLogoutModal);
    document.getElementById("logoutConfirmBtn").addEventListener("click", logoutAdmin);
}

function openLogoutModal() {
    const modal = document.getElementById("logoutModalOverlay");
    if (modal) modal.classList.add("show");
}

function closeLogoutModal() {
    const modal = document.getElementById("logoutModalOverlay");
    if (modal) modal.classList.remove("show");
}

function openMobileSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    if (!sidebar || !overlay) return;

    sidebar.classList.add("show");
    overlay.classList.add("show");
    document.body.classList.add("sidebar-open");
}

function closeMobileSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    if (sidebar) sidebar.classList.remove("show");
    if (overlay) overlay.classList.remove("show");
    document.body.classList.remove("sidebar-open");
}

function setupMobileSidebarToggle() {
    const menuBtn = document.getElementById("mobileMenuBtn");
    const overlay = document.getElementById("sidebarOverlay");

    if (menuBtn) {
        menuBtn.addEventListener("click", openMobileSidebar);
    }

    if (overlay) {
        overlay.addEventListener("click", closeMobileSidebar);
    }

    window.addEventListener("resize", () => {
        if (window.innerWidth > 991) {
            closeMobileSidebar();
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeMobileSidebar();
        }
    });
}

function setupSidebarCloseButton() {
    const closeBtn = document.getElementById("sidebarCloseBtn");

    if (closeBtn) {
        closeBtn.addEventListener("click", closeMobileSidebar);
    }

    document.querySelectorAll(".sidebar-nav .nav-link").forEach(link => {
        link.addEventListener("click", closeMobileSidebar);
    });
}

async function logoutAdmin() {
    try {
        await fetch("../../../api/auth/logout.php", {
            method: "POST",
            credentials: "include",
        });
    } catch (err) {
        console.error("Logout request failed:", err);
    }

    redirectToLogin();
}

function redirectToLogin() {
    window.location.replace("../../../pages/auth/auth.html");
}

renderAdminTopBar();

function applyTheme(theme) {
    const themeIcon = document.getElementById("themeIcon");

    if (theme === "dark") {
        html.setAttribute("data-theme", "dark");

        if (themeIcon) {
            themeIcon.src = ICON_SUN;
        }
    } else {
        html.removeAttribute("data-theme");

        if (themeIcon) {
            themeIcon.src = ICON_MOON;
        }
    }

    localStorage.setItem("theme", theme);
}

function initTheme() {
    const saved = localStorage.getItem("theme");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

    const theme = saved || (prefersDark ? "dark" : "light");

    applyTheme(theme);
}

function setupThemeToggle() {
    const themeToggle = document.getElementById("themeToggle");

    if (!themeToggle) return;

    themeToggle.addEventListener("click", () => {
        const currentTheme =
            html.getAttribute("data-theme") === "dark" ? "dark" : "light";

        applyTheme(currentTheme === "dark" ? "light" : "dark");
    });
}

function setActiveSidebarLink() {
    const currentPage = window.location.pathname.split("/").pop();

    document.querySelectorAll(".sidebar-nav .nav-link").forEach(link => {
        const href = link.getAttribute("href");

        if (href === currentPage) {
            link.classList.add("active");
        }
    });
}

function getTime (){
    const now = new Date();
    const currentDay = document.getElementById("currentDay");
    const currentDate = document.getElementById("currentDate");

    if (currentDay) {
        currentDay.textContent = now.toLocaleDateString("en-US", {
            weekday: "long"
        });
    }

    if (currentDate) {
        currentDate.textContent = now.toLocaleDateString("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric"
        });
    }
}

loadAdmSideBar();
