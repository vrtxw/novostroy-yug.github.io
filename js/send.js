document.addEventListener('DOMContentLoaded', function() {
    // Получаем CSRF токен при загрузке страницы
    fetch('php/get_token.php')
        .then(response => response.json())
        .then(data => {
            if (data.token) {
                document.querySelector('input[name="csrf_token"]').value = data.token;
            }
        })
        .catch(error => console.error('Ошибка получения токена:', error));

    const form = document.getElementById('contactForm');
    if (form) {
        // Добавляем время начала заполнения формы
        form.dataset.startTime = Date.now();

        // Добавляем обработчики для валидации в реальном времени
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                validateInput(this);
            });
            input.addEventListener('blur', function() {
                validateInput(this);
            });
        });

        // Обработчик отправки формы
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable button and show spinner
            const submitButton = this.querySelector('button[type="submit"]');
            const spinner = submitButton.querySelector('.spinner-border');
            submitButton.disabled = true;
            spinner.classList.remove('d-none');
            
            // Get form data
            const formData = new FormData(this);
            formData.append('project_name', 'СЗ Новострой-Юг');
            formData.append('admin_email', 'noreply@sz-novostroi-yug.ru');
            formData.append('form_subject', 'Новое сообщение с сайта');
            
            // Send request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/send.php', true);
            xhr.send(formData);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    // Enable button and hide spinner
                    submitButton.disabled = false;
                    spinner.classList.add('d-none');
                    
                    if (xhr.status === 200) {
                        // Show success message
                        showNotification('Спасибо! Ваше сообщение успешно отправлено', 'success');
                        this.reset();
                    } else {
                        // Show error message
                        showNotification('Произошла ошибка при отправке. Пожалуйста, попробуйте позже', 'error');
                    }
                }
            };
        });
    }
});

// Функция валидации формы
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });

    return isValid;
}

// Функция валидации отдельного поля
function validateInput(input) {
    const errorMessage = input.nextElementSibling;
    let isValid = true;
    
    // Очищаем предыдущие ошибки
    input.classList.remove('is-invalid');
    if (errorMessage) {
        errorMessage.textContent = '';
    }

    // Проверяем заполненность
    if (!input.value.trim()) {
        isValid = false;
        input.classList.add('is-invalid');
        if (errorMessage) {
            errorMessage.textContent = 'Это поле обязательно для заполнения';
        }
        return false;
    }

    // Проверяем паттерн
    if (input.pattern && !new RegExp(input.pattern).test(input.value)) {
        isValid = false;
        input.classList.add('is-invalid');
        if (errorMessage) {
            errorMessage.textContent = input.title || 'Неверный формат';
        }
        return false;
    }

    // Проверяем email
    if (input.type === 'email' && !isValidEmail(input.value)) {
        isValid = false;
        input.classList.add('is-invalid');
        if (errorMessage) {
            errorMessage.textContent = 'Введите корректный email адрес';
        }
        return false;
    }

    // Проверяем телефон
    if (input.type === 'tel' && input.value) {
        const phonePattern = /^[\+]?[0-9]{1}[\s\-]?[\(]?[0-9]{3}[\)]?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/;
        if (!phonePattern.test(input.value)) {
            isValid = false;
            input.classList.add('is-invalid');
            if (errorMessage) {
                errorMessage.textContent = 'Введите корректный номер телефона';
            }
            return false;
        }
    }

    // Проверяем длину сообщения
    if (input.tagName === 'TEXTAREA') {
        const minLength = input.getAttribute('minlength');
        const maxLength = input.getAttribute('maxlength');
        const length = input.value.trim().length;

        if (minLength && length < minLength) {
            isValid = false;
            input.classList.add('is-invalid');
            if (errorMessage) {
                errorMessage.textContent = `Минимальная длина сообщения ${minLength} символов`;
            }
            return false;
        }
        if (maxLength && length > maxLength) {
            isValid = false;
            input.classList.add('is-invalid');
            if (errorMessage) {
                errorMessage.textContent = `Максимальная длина сообщения ${maxLength} символов`;
            }
            return false;
        }
    }

    return isValid;
}

// Функция валидации email
function isValidEmail(email) {
    const re = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
    return re.test(String(email).toLowerCase());
}

// Функция показа уведомления
function showNotification(message, type) {
    // Удаляем предыдущее уведомление, если есть
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Создаем новое уведомление
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Добавляем на страницу
    document.body.appendChild(notification);

    // Добавляем анимацию появления
    setTimeout(() => notification.style.opacity = '1', 100);

    // Удаляем через 5 секунд
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}