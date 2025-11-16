const navLinks = document.querySelectorAll('.nav-link');
const sections = document.querySelectorAll('.content');
const logout = document.getElementById('logout');
const defaultSection = "home";

// Show a section and save it in localStorage
function showSection(sectionId) {
    sections.forEach(s => s.classList.add('hidden'));
    const active = document.getElementById(sectionId);
    if (active) active.classList.remove('hidden');

    navLinks.forEach(link => link.classList.toggle('active', link.dataset.section === sectionId));

    localStorage.setItem("lastSection", sectionId);
}

// Click handler for nav links
navLinks.forEach(link => {
    link.addEventListener("click", e => {
        e.preventDefault();
        const target = link.dataset.section;
        showSection(target);
        // Clear habit state when leaving habits
        if(target !== 'habits') localStorage.removeItem('habitState');
    });
});

// On page load: restore last section, or home by default
window.addEventListener("load", () => {
    const last = localStorage.getItem("lastSection") || defaultSection;
    showSection(last);
});

// Logout
logout.addEventListener("click", e => {
    e.preventDefault();
    localStorage.clear();
    window.location.href = "../logout.php";
});
