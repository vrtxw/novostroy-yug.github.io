document.querySelector('.feedback-form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Получаем данные формы
    const formData = {
        surname: document.getElementById('surname').value,
        email: document.getElementById('email').value,
        message: document.getElementById('message').value
    };

    // Простая валидация email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(formData.email)) {
        alert('Пожалуйста, введите корректный email');
        return;
    }

    // Здесь можно добавить отправку данных на сервер
    console.log('Отправка данных:', formData);

    // Очистка формы после успешной отправки
    this.reset();
    alert('Сообщение отправлено!');
});