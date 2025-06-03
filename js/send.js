document.addEventListener('DOMContentLoaded', function() {
    let csrfToken = null;

    // Функция для получения CSRF токена
    function fetchCSRFToken() {
        return fetch('php/get_token.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.token) {
                    csrfToken = data.token;
                    const tokenInput = document.querySelector('input[name="csrf_token"]');
                    if (tokenInput) {
                        tokenInput.value = csrfToken;
                    }
                    return true;
                } else {
                    throw new Error(data.error || 'Failed to get CSRF token');
                }
            })
            .catch(error => {
                console.error('Error fetching CSRF token:', error);
                showNotification('Ошибка инициализации формы. Пожалуйста, перезагрузите страницу.', 'error');
                return false;
            });
    }

    // Получаем начальный CSRF токен
    fetchCSRFToken();

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

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Проверяем валидность формы
            if (!validateForm(this)) {
                showNotification('Пожалуйста, проверьте правильность заполнения формы', 'error');
                return;
            }

            // Проверяем наличие CSRF токена
            if (!csrfToken) {
                const tokenFetched = await fetchCSRFToken();
                if (!tokenFetched) {
                    showNotification('Ошибка безопасности. Пожалуйста, перезагрузите страницу.', 'error');
                    return;
                }
            }
            
            // Отключаем кнопку и показываем спиннер
            const submitButton = this.querySelector('button[type="submit"]');
            const buttonText = submitButton.querySelector('.button-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            buttonText.style.display = 'none';
            spinner.classList.remove('d-none');
            
            // Получаем данные формы
            const formData = new FormData(this);
            
            try {
                // Отправляем запрос
                const response = await fetch('php/send.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    form.reset();
                    
                    // Обновляем CSRF токен
                    if (data.newToken) {
                        csrfToken = data.newToken;
                        const tokenInput = document.querySelector('input[name="csrf_token"]');
                        if (tokenInput) {
                            tokenInput.value = csrfToken;
                        }
                    }
                    
                    // Логируем отладочную информацию
                    if (data.debug && data.debug.length > 0) {
                        console.log('Debug info:', data.debug);
                    }
                } else {
                    // Если ошибка связана с CSRF токеном, пробуем получить новый
                    if (data.message.includes('Ошибка безопасности')) {
                        await fetchCSRFToken();
                    }
                    throw new Error(data.message || 'Произошла ошибка при отправке');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification(error.message || 'Произошла ошибка при отправке. Пожалуйста, попробуйте позже.', 'error');
            } finally {
                // Возвращаем кнопку в исходное состояние
                submitButton.disabled = false;
                buttonText.style.display = '';
                spinner.classList.add('d-none');
            }
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
    
    // Проверяем обязательные поля
    if (input.required && !input.value.trim()) {
        isValid = false;
        input.classList.add('is-invalid');
        if (errorMessage) {
            errorMessage.textContent = 'Это поле обязательно для заполнения';
        }
        return false;
    }
    
    // Проверяем email
    if (input.type === 'email' && input.value) {
        if (!isValidEmail(input.value)) {
            isValid = false;
            input.classList.add('is-invalid');
            if (errorMessage) {
                errorMessage.textContent = 'Введите корректный email адрес';
            }
            return false;
        }
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
        const minLength = parseInt(input.getAttribute('minlength')) || 0;
        const maxLength = parseInt(input.getAttribute('maxlength')) || 1000;
        const length = input.value.trim().length;
        
        if (length < minLength) {
            isValid = false;
            input.classList.add('is-invalid');
            if (errorMessage) {
                errorMessage.textContent = `Минимальная длина сообщения ${minLength} символов`;
            }
            return false;
        }
        if (length > maxLength) {
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
    
    // Добавляем стили для уведомления
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '15px 25px';
    notification.style.borderRadius = '5px';
    notification.style.color = '#fff';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease-in-out';
    
    // Устанавливаем цвет фона в зависимости от типа
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    }
    
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