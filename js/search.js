document.addEventListener('DOMContentLoaded', function() {
    // Получаем форму поиска
    const searchForm = document.getElementById('searchForm');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Получаем значения полей
            const location = document.getElementById('location').value;
            const propertyClass = document.getElementById('propertyClass').value;
            
            // Отправляем AJAX запрос
            fetch(`php/search.php?location=${encodeURIComponent(location)}&class=${encodeURIComponent(propertyClass)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Если найден подходящий ЖК, перенаправляем на его страницу
                        window.location.href = data.redirect;
                    } else {
                        // Если ЖК не найден, показываем сообщение
                        alert(data.message || 'Произошла ошибка при поиске');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при выполнении поиска');
                });
        });
    }
}); 