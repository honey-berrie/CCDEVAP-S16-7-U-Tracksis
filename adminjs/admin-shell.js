document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".adm-sidebar");
  const toggleBtn = document.querySelector(".adm-sidebar-toggle");
  const backdrop = document.querySelector(".adm-sidebar-backdrop");

  if (!sidebar || !toggleBtn || !backdrop) return;

  const openSidebar = () => {
    sidebar.classList.add("open");
    backdrop.classList.add("show");
  };
  const closeSidebar = () => {
    sidebar.classList.remove("open");
    backdrop.classList.remove("show");
  };

  toggleBtn.addEventListener("click", () => {
    sidebar.classList.contains("open") ? closeSidebar() : openSidebar();
  });
  backdrop.addEventListener("click", closeSidebar);
});
