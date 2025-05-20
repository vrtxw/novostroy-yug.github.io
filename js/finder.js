function handleSearch() {
    const city = document.getElementById('citySelect').value;
    const tips = document.getElementById('tipsSelect').value;
    
    if(city && tips) {
        // Перенаправление с параметрами
        window.location.href = `/search-results.html?city=${city}&tips=${tips}`;
        // или на конкретную страницу
        // window.location.href = 'specific-page.html';
    } else {
        alert('Пожалуйста, выберите город и тип');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const city = searchForm.querySelector('[name="city"]').value;
            const type = searchForm.querySelector('[name="type"]').value;
            
            // Проверяем конкретную комбинацию
            if (city === 'aksay' && type === 'comfort') {
                window.location.href = 'prostor.html';
            } else {
                // Показываем сообщение об ошибке
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.textContent = 'Извините, для выбранной комбинации города и типа жилья нет доступных вариантов';
                
                // Удаляем предыдущее сообщение об ошибке, если оно есть
                const previousError = searchForm.querySelector('.alert');
                if (previousError) {
                    previousError.remove();
                }
                
                // Добавляем новое сообщение
                searchForm.appendChild(errorDiv);
                
                // Удаляем сообщение через 5 секунд
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }
        });
    }
});