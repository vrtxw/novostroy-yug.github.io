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