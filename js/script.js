
const menuToggle = document.getElementById("menu-toggle");
const siteNav = document.getElementById("site-nav");

menuToggle.addEventListener("click", function() {
    siteNav.classList.toggle("open");

    const isOpen = siteNav.classList.contains("open");
    menuToggle.setAttribute("aria-expanded", isOpen);
});
