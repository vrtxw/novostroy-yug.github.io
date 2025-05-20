class FormHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.submitButton = this.form.querySelector('button[type="submit"]');
        this.buttonText = this.submitButton.querySelector('.button-text');
        this.spinner = this.submitButton.querySelector('.spinner-border');
        
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            return;
        }
        
        this.setLoading(true);
        
        try {
            const formData = new FormData(this.form);
            const response = await fetch('php/send.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Ошибка сети при отправке формы');
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Ошибка сервера: неверный формат ответа');
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message);
                this.form.reset();
            } else {
                this.showError(result.message || 'Произошла ошибка при отправке');
            }
        } catch (error) {
            console.error('Ошибка:', error);
            this.showError('Произошла ошибка при отправке сообщения');
        } finally {
            this.setLoading(false);
        }
    }
    
    validateForm() {
        let isValid = true;
        this.form.querySelectorAll('.error-message').forEach(error => error.textContent = '');
        
        // Проверка имени
        const nameInput = this.form.querySelector('[name="name"]');
        if (!nameInput.value.trim()) {
            this.showFieldError(nameInput, 'Пожалуйста, введите ваше имя');
            isValid = false;
        }
        
        // Проверка email
        const emailInput = this.form.querySelector('[name="email"]');
        if (!emailInput.value.trim()) {
            this.showFieldError(emailInput, 'Пожалуйста, введите email');
            isValid = false;
        } else if (!this.isValidEmail(emailInput.value)) {
            this.showFieldError(emailInput, 'Пожалуйста, введите корректный email');
            isValid = false;
        }
        
        // Проверка сообщения
        const messageInput = this.form.querySelector('[name="message"]');
        if (!messageInput.value.trim()) {
            this.showFieldError(messageInput, 'Пожалуйста, введите ваше сообщение');
            isValid = false;
        }
        
        // Проверка согласия
        const checkbox = this.form.querySelector('.check-box');
        if (!checkbox.checked) {
            this.showFieldError(checkbox, 'Необходимо принять условия');
            isValid = false;
        }
        
        return isValid;
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    showFieldError(field, message) {
        const errorElement = field.parentElement.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.color = 'red';
            errorElement.style.fontSize = '12px';
            errorElement.style.marginTop = '5px';
            errorElement.style.display = 'block';
        }
    }
    
    setLoading(isLoading) {
        this.submitButton.disabled = isLoading;
        this.buttonText.style.display = isLoading ? 'none' : 'inline';
        this.spinner.classList.toggle('d-none', !isLoading);
    }
    
    showSuccess(message = 'Сообщение успешно отправлено!') {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success mt-3';
        alert.textContent = message;
        this.form.insertBefore(alert, this.form.firstChild);
        setTimeout(() => alert.remove(), 5000);
    }
    
    showError(message = 'Произошла ошибка при отправке сообщения.') {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger mt-3';
        alert.textContent = message;
        this.form.insertBefore(alert, this.form.firstChild);
        setTimeout(() => alert.remove(), 5000);
    }
}

// Инициализация формы после загрузки DOM
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contactForm');
    if (form) {
        new FormHandler('contactForm');
    }
});