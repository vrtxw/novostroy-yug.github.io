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
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Network response was not ok');
            }
            
            if (result.success) {
                this.showSuccess(result.message);
                this.form.reset();
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            this.showError(error.message);
        } finally {
            this.setLoading(false);
        }
    }
    
    validateForm() {
        let isValid = true;
        this.form.querySelectorAll('.error-message').forEach(error => error.textContent = '');
        
        this.form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'Это поле обязательно');
                isValid = false;
            }
        });
        
        const emailInput = this.form.querySelector('[type="email"]');
        if (emailInput && !this.isValidEmail(emailInput.value)) {
            this.showFieldError(emailInput, 'Введите корректный email');
            isValid = false;
        }
        
        const checkbox = this.form.querySelector('.check-box');
        if (checkbox && !checkbox.checked) {
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
        }
    }
    
    setLoading(isLoading) {
        this.submitButton.disabled = isLoading;
        this.buttonText.style.display = isLoading ? 'none' : 'inline';
        this.spinner.classList.toggle('d-none', !isLoading);
    }
    
    showSuccess(message = 'Сообщение успешно отправлено!') {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success';
        alert.textContent = message;
        this.form.insertBefore(alert, this.form.firstChild);
        setTimeout(() => alert.remove(), 5000);
    }
    
    showError(message = 'Произошла ошибка при отправке сообщения.') {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.textContent = message;
        this.form.insertBefore(alert, this.form.firstChild);
        setTimeout(() => alert.remove(), 5000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new FormHandler('contactForm');
});