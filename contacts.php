<?php
define('SECURE_ACCESS', true);
require_once 'php/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Контакты</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="contact-info mb-4">
                    <h3>Наш адрес</h3>
                    <p>г. Краснодар, ул. Красная, 123</p>
                    
                    <h3>Телефоны</h3>
                    <p>
                        <a href="tel:+78612345678">+7 (861) 234-56-78</a><br>
                        <a href="tel:+79181234567">+7 (918) 123-45-67</a>
                    </p>
                    
                    <h3>Email</h3>
                    <p><a href="mailto:info@sz-novostroi-yug.ru">info@sz-novostroi-yug.ru</a></p>
                    
                    <h3>Режим работы</h3>
                    <p>
                        Пн-Пт: 9:00 - 18:00<br>
                        Сб: 10:00 - 15:00<br>
                        Вс: выходной
                    </p>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="contact-form">
                    <h3>Обратная связь</h3>
                    <form id="contactForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label for="name">Ваше имя *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   pattern="[\+]?[0-9]{1}[\s\-]?[\(]?[0-9]{3}[\)]?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Сообщение *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="map mt-5">
            <h3>Как нас найти</h3>
            <div id="map" style="height: 400px;"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=de661755-c9d1-4c0a-8ddb-8ff183ea4ffd&lang=ru_RU"></script>
    <script>
        $(document).ready(function() {
            // Инициализация маски для телефона
            $('#phone').mask('+7 (999) 999-99-99');
            
            // Обработка отправки формы
            $('#contactForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'php/send_form.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#contactForm')[0].reset();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при отправке сообщения');
                    }
                });
            });
            
            // Инициализация карты
            ymaps.ready(function() {
                var myMap = new ymaps.Map('map', {
                    center: [45.035470, 38.975313], // Координаты центра Краснодара
                    zoom: 12
                });
                
                var myPlacemark = new ymaps.Placemark([45.035470, 38.975313], {
                    hintContent: '<?php echo SITE_NAME; ?>',
                    balloonContent: 'г. Краснодар, ул. Красная, 123'
                });
                
                myMap.geoObjects.add(myPlacemark);
            });
        });
    </script>
</body>
</html> 