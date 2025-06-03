// Получение текущего года
function getYear() {
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    document.querySelector("#displayYear").innerHTML = currentYear;
}
getYear();

// Навигационное меню
function openNav() {
    const nav = document.getElementById("myNav");
    const menuBtn = document.querySelector(".custom_menu-btn");
    const body = document.body;
    
    nav.classList.toggle("menu_width");
    menuBtn.classList.toggle("menu_btn-style");
    body.classList.toggle("menu-open");
    
    // Обновляем ARIA атрибуты
    const isExpanded = nav.classList.contains("menu_width");
    menuBtn.setAttribute("aria-expanded", isExpanded);
    
    // Запрещаем прокрутку при открытом меню
    if (isExpanded) {
        document.addEventListener("keydown", closeOnEscape);
        // Сохраняем текущую позицию прокрутки
        body.style.top = `-${window.scrollY}px`;
        body.style.position = 'fixed';
        body.style.width = '100%';
    } else {
        document.removeEventListener("keydown", closeOnEscape);
        // Восстанавливаем позицию прокрутки
        const scrollY = body.style.top;
        body.style.position = '';
        body.style.top = '';
        body.style.width = '';
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
    }
}

function closeOnEscape(e) {
    if (e.key === "Escape") {
        openNav();
    }
}

// Закрытие меню при клике вне его области
document.addEventListener('click', function(e) {
    const nav = document.getElementById("myNav");
    const menuBtn = document.querySelector(".custom_menu-btn");
    
    if (nav.classList.contains("menu_width") && 
        !nav.contains(e.target) && 
        !menuBtn.contains(e.target)) {
        openNav();
    }
});

// Инициализация доступности кнопки меню
document.addEventListener("DOMContentLoaded", function() {
    const menuBtn = document.querySelector(".custom_menu-btn button");
    if (menuBtn) {
        menuBtn.setAttribute("aria-label", "Открыть меню");
        menuBtn.setAttribute("aria-expanded", "false");
        menuBtn.setAttribute("aria-controls", "myNav");
    }
    
    // Добавление обработки сенсорных событий
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
        
        if (swipeLength > 50) { // Минимальное расстояние свайпа
            if (isSwipeLeft && nav.classList.contains("menu_width")) {
                openNav(); // Закрыть меню при свайпе влево
            } else if (!isSwipeLeft && !nav.classList.contains("menu_width")) {
                openNav(); // Открыть меню при свайпе вправо
            }
        }
    }
});

// Плавная прокрутка для всех ссылок на разделы
document.addEventListener('DOMContentLoaded', function() {
  // Обработка всех ссылок на разделы
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href');
      // Пропускаем, если это не ссылка на раздел
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

// Добавляем обработчик для закрытия меню при клике на ссылку
document.addEventListener('DOMContentLoaded', function() {
    const nav = document.getElementById("myNav");
    const links = nav.getElementsByTagName('a');
    
    for (let link of links) {
        link.addEventListener('click', function() {
            // Закрываем меню при клике на ссылку
            if (nav.classList.contains("menu_width")) {
                openNav();
            }
        });
    }
    
    // Исправление проблем с прокруткой на iOS
    let touchStartY = 0;
    
    nav.addEventListener('touchstart', function(e) {
        touchStartY = e.touches[0].clientY;
    }, false);
    
    nav.addEventListener('touchmove', function(e) {
        const touchY = e.touches[0].clientY;
        const scrollTop = nav.scrollTop;
        const scrollHeight = nav.scrollHeight;
        const clientHeight = nav.clientHeight;
        
        // Предотвращаем прокрутку body только если меню открыто
        if (nav.classList.contains("menu_width")) {
            // Разрешаем прокрутку только внутри меню
            if ((scrollTop === 0 && touchY > touchStartY) || 
                (scrollTop + clientHeight >= scrollHeight && touchY < touchStartY)) {
                e.preventDefault();
            }
        }
    }, false);
});
