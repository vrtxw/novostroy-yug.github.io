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
            const response = await fetch('send.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            this.showSuccess();
            this.form.reset();
        } catch (error) {
            this.showError();
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
        
        return isValid;
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    showFieldError(field, message) {
        const errorElement = field.parentElement.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = message;
        }
    }
    
    setLoading(isLoading) {
        this.submitButton.disabled = isLoading;
        this.buttonText.style.display = isLoading ? 'none' : 'inline';
        this.spinner.classList.toggle('d-none', !isLoading);
    }
    
    showSuccess() {
        alert('Сообщение успешно отправлено!');
    }
    
    showError() {
        alert('Произошла ошибка при отправке сообщения.');
    }
}

new FormHandler('contactForm');