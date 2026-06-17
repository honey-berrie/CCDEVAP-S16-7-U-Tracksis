/* dark mode toggle */
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const html = document.documentElement;

const ICON_MOON = '../../assets/icons/moon-stars-fill.svg';
const ICON_SUN = '../../assets/icons/sun-fill.svg';

function applyTheme(theme) {
  if (theme === 'dark') {
    html.setAttribute('data-theme', 'dark');
    if (themeIcon) themeIcon.src = ICON_SUN;
  } else {
    html.removeAttribute('data-theme');
    if (themeIcon) themeIcon.src = ICON_MOON;
  }
  localStorage.setItem('theme', theme);
}

function initTheme() {
  const saved = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = saved || (prefersDark ? 'dark' : 'light');
  if (theme === 'dark') {
    if (themeIcon) themeIcon.src = ICON_SUN;
  } else {
    if (themeIcon) themeIcon.src = ICON_MOON;
  }
}

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const current = html.getAttribute('data-theme');
    applyTheme(current === 'dark' ? 'light' : 'dark');
  });
}

initTheme();
