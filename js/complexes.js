class ComplexesAPI {
    static async getAll() {
        const response = await fetch('/api/complexes.php?action=list');
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error);
        }
        return data.data;
    }
    
    static async getById(id) {
        const response = await fetch(`/api/complexes.php?action=get&id=${id}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error);
        }
        return data.data;
    }
    
    static async getApartments(id) {
        const response = await fetch(`/api/complexes.php?action=apartments&id=${id}`);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error);
        }
        return data.data;
    }
}

class ComplexesUI {
    constructor() {
        this.complexesContainer = document.getElementById('complexes-container');
        this.apartmentsContainer = document.getElementById('apartments-container');
        this.loadingIndicator = document.getElementById('loading-indicator');
    }
    
    showLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'block';
        }
    }
    
    hideLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'none';
        }
    }
    
    async init() {
        try {
            this.showLoading();
            const complexes = await ComplexesAPI.getAll();
            this.renderComplexes(complexes);
        } catch (error) {
            console.error('Ошибка при загрузке данных:', error);
            this.showError(error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    renderComplexes(complexes) {
        if (!this.complexesContainer) return;
        
        this.complexesContainer.innerHTML = complexes.map(complex => `
            <div class="complex-card" data-id="${complex.id}">
                <h2>${complex.name}</h2>
                <div class="complex-info">
                    <p><strong>Класс:</strong> ${complex.class}</p>
                    <p><strong>Материал стен:</strong> ${complex.wall_material}</p>
                    <p><strong>Отделка:</strong> ${complex.finishing_type}</p>
                    <p><strong>Этажность:</strong> ${complex.floors_count}</p>
                    <p><strong>Адрес:</strong> ${complex.address}</p>
                </div>
                
                <div class="complex-features">
                    ${this.renderFeatures(complex.features)}
                </div>
                
                <button class="btn btn-primary show-apartments" 
                        onclick="complexesUI.loadApartments(${complex.id})">
                    Показать квартиры
                </button>
            </div>
        `).join('');
    }
    
    renderFeatures(features) {
        if (!features) return '';
        
        return Object.entries(features).map(([category, items]) => `
            <div class="feature-category">
                <h3>${category}</h3>
                <ul>
                    ${items.map(item => `
                        <li><strong>${item.name}:</strong> ${item.value}</li>
                    `).join('')}
                </ul>
            </div>
        `).join('');
    }
    
    async loadApartments(complexId) {
        if (!this.apartmentsContainer) return;
        
        try {
            this.showLoading();
            const apartments = await ComplexesAPI.getApartments(complexId);
            this.renderApartments(apartments);
        } catch (error) {
            console.error('Ошибка при загрузке квартир:', error);
            this.showError(error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    renderApartments(apartments) {
        if (!this.apartmentsContainer) return;
        
        this.apartmentsContainer.innerHTML = apartments.map(apartment => `
            <div class="apartment-card">
                <div class="apartment-images">
                    ${this.renderApartmentImages(apartment.images)}
                </div>
                <div class="apartment-info">
                    <h3>${apartment.rooms_count}-комнатная, ${apartment.area} м²</h3>
                    <p><strong>Этаж:</strong> ${apartment.floor} из ${apartment.max_floor}</p>
                    <p><strong>Цена:</strong> ${this.formatPrice(apartment.price)} ₽</p>
                    ${apartment.description ? `<p>${apartment.description}</p>` : ''}
                </div>
            </div>
        `).join('');
    }
    
    renderApartmentImages(images) {
        if (!images || !images.length) {
            return '<div class="no-image">Нет изображений</div>';
        }
        
        return `
            <div class="apartment-slider">
                ${images.map(image => `
                    <div class="apartment-slide">
                        <img src="${image}" alt="Планировка квартиры">
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    formatPrice(price) {
        return new Intl.NumberFormat('ru-RU').format(price);
    }
    
    showError(message) {
        // Можно реализовать красивый вывод ошибок
        alert(message);
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.complexesUI = new ComplexesUI();
    complexesUI.init();
}); 