/* dark mode toggle */
const themeToggle = document.getElementById("themeToggle");
const themeIcon = document.getElementById("themeIcon");
const html = document.documentElement;

const ICON_MOON = "../../assets/icons/moon-stars-fill.svg";
const ICON_SUN = "../../assets/icons/sun-fill.svg";

function applyTheme(theme) {
  if (theme === "dark") {
    html.setAttribute("data-theme", "dark");
    if (themeIcon) themeIcon.src = ICON_SUN;
  } else {
    html.removeAttribute("data-theme");
    if (themeIcon) themeIcon.src = ICON_MOON;
  }
  localStorage.setItem("theme", theme);

  // Let other scripts (e.g. chart widgets) react to theme changes without
  // having to guess via MutationObserver.
  window.dispatchEvent(new CustomEvent("themechange", { detail: { theme } }));
}

function initTheme() {
  const saved = localStorage.getItem("theme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const theme = saved || (prefersDark ? "dark" : "light");
  if (theme === "dark") {
    if (themeIcon) themeIcon.src = ICON_SUN;
  } else {
    if (themeIcon) themeIcon.src = ICON_MOON;
  }

  // Announce the resolved theme on load too, so listeners registered before
  // this point (e.g. chart init code) can sync to the correct colors right away.
  window.dispatchEvent(new CustomEvent("themechange", { detail: { theme } }));
}

if (themeToggle) {
  themeToggle.addEventListener("click", () => {
    const current = html.getAttribute("data-theme");
    applyTheme(current === "dark" ? "light" : "dark");
  });
}

initTheme();
