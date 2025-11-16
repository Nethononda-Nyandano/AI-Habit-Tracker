//notification dropdown
const notificationBtn = document.getElementById('notificationBtn');
const notificationDropdown = document.getElementById('notificationDropdown');
notificationBtn.addEventListener('click', () => {
notificationDropdown.classList.toggle('hidden');
});


// Optional: Hide dropdown when clicking outside
document.addEventListener('click', (e) => {
if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
    notificationDropdown.classList.add('hidden');
}
});