// to get current year
function getYear() {
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    document.querySelector("#displayYear").innerHTML = currentYear;
}
getYear();

// nav menu 
function openNav() {
    const nav = document.getElementById("myNav");
    const menuBtn = document.querySelector(".custom_menu-btn");
    
    nav.classList.toggle("menu_width");
    menuBtn.classList.toggle("menu_btn-style");
    
    // Update ARIA attributes
    const isExpanded = nav.classList.contains("menu_width");
    menuBtn.setAttribute("aria-expanded", isExpanded);
    
    // Handle escape key to close menu
    if (isExpanded) {
        document.addEventListener("keydown", closeOnEscape);
        document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
    } else {
        document.removeEventListener("keydown", closeOnEscape);
        document.body.style.overflow = ''; // Restore scrolling
    }
}

function closeOnEscape(e) {
    if (e.key === "Escape") {
        openNav();
    }
}

// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const nav = document.getElementById("myNav");
    const menuBtn = document.querySelector(".custom_menu-btn");
    
    if (nav.classList.contains("menu_width") && 
        !nav.contains(e.target) && 
        !menuBtn.contains(e.target)) {
        openNav();
    }
});

// Initialize menu button accessibility
document.addEventListener("DOMContentLoaded", function() {
    const menuBtn = document.querySelector(".custom_menu-btn button");
    if (menuBtn) {
        menuBtn.setAttribute("aria-label", "Открыть меню");
        menuBtn.setAttribute("aria-expanded", "false");
        menuBtn.setAttribute("aria-controls", "myNav");
    }
    
    // Add touch event handling
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    document.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);
    
    function handleSwipe() {
        const nav = document.getElementById("myNav");
        const swipeLength = Math.abs(touchEndX - touchStartX);
        const isSwipeLeft = touchEndX < touchStartX;
        
        if (swipeLength > 50) { // Minimum swipe distance
            if (isSwipeLeft && nav.classList.contains("menu_width")) {
                openNav(); // Close menu on swipe left
            } else if (!isSwipeLeft && !nav.classList.contains("menu_width")) {
                openNav(); // Open menu on swipe right
            }
        }
    }
});

// Smooth scrolling for all section links
document.addEventListener('DOMContentLoaded', function() {
  // Handle all section links
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href');
      // Skip if it's not a section link
      if (targetId === '#' || !targetId.startsWith('#')) return;
      
      const targetSection = document.querySelector(targetId);
      if (targetSection) {
        e.preventDefault();
        targetSection.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
});
