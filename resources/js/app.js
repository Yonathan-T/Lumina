import './bootstrap';
import.meta.glob([
    '../images/**'
]);
const toggleBtn = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

let isSidebarVisible = true;

toggleBtn.addEventListener('click', () => {
  isSidebarVisible = !isSidebarVisible;

  if (isSidebarVisible) {
    sidebar.classList.remove('-translate-x-full');
    mainContent.classList.remove('pl-0');
    mainContent.classList.add('pl-[270px]');
  } else {
    sidebar.classList.add('-translate-x-full');
    mainContent.classList.remove('pl-[270px]');
    mainContent.classList.add('pl-0');
  }
});
