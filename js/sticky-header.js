document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что мы не на главной странице
    if (!window.location.pathname.endsWith('index.html') && !window.location.pathname.endsWith('/')) {
        const header = document.querySelector('.header_section');
        const headerHeight = header.offsetHeight;
        
        // Добавляем стили для фиксированного header
        header.style.width = '100%';
        header.style.zIndex = '1000';
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                header.classList.add('sticky-header');
                document.body.style.paddingTop = headerHeight + 'px';
            } else {
                header.classList.remove('sticky-header');
                document.body.style.paddingTop = '0';
            }
        });
    }
}); 