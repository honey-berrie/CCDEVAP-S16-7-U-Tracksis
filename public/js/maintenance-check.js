(function () {
  // This file lives at project-root/js/maintenance-check.js.
  // It is included from pages that are exactly two folders deep from the
  // project root (pages/student/*.html, phase2-page-based-adviser/adviserhtml/*.php,
  // coordinator/coordinator-html/*.html), so "../../" reaches the root from all of them.
  const STATUS_URL = '/maintenance-status';
  const MAINTENANCE_PAGE = '/maintenance';

  async function checkMaintenance() {
    try {
      const res = await fetch(STATUS_URL, {
        method: 'GET',
        credentials: 'include',
      });
      const data = await res.json();

      if (data.maintenance && !data.exempt) {
        window.location.href = MAINTENANCE_PAGE;
      }
    } catch (err) {
      // If the check itself fails (e.g. offline), fail open rather than
      // locking everyone out on a network hiccup.
      console.error('Maintenance check failed:', err);
    }
  }

  checkMaintenance();
})();
