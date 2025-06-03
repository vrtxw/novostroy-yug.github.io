<?php
define('SECURE_ACCESS', true);
require_once 'php/config.php';
require_once 'php/classes/ResidentialComplex.php';

// Получаем ID комплекса из GET-параметра
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем данные о комплексе
$complex = new ResidentialComplex($id);
$complexData = $complex->getData();

// Если комплекс не найден или неактивен, редиректим на главную
if (!$complexData || !$complexData['is_active']) {
    header('Location: /');
    exit;
}

// Получаем характеристики комплекса
$features = $complex->getFeatures();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($complexData['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
                <ul>
                    <li><a href="/">Главная</a></li>
                    <li><a href="/about.php">О компании</a></li>
                    <li><a href="/contact.php">Контакты</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <article class="complex-details">
            <div class="container">
                <h2><?php echo htmlspecialchars($complexData['name']); ?></h2>
                
                <div class="complex-images">
                    <?php if ($complexData['main_image']): ?>
                        <div class="main-image">
                            <img src="<?php echo htmlspecialchars($complexData['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($complexData['name']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($complexData['layout_image']): ?>
                        <div class="layout-image">
                            <h3>План комплекса</h3>
                            <img src="<?php echo htmlspecialchars($complexData['layout_image']); ?>" 
                                 alt="План <?php echo htmlspecialchars($complexData['name']); ?>">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="complex-info">
                    <div class="info-block">
                        <h3>Описание</h3>
                        <div class="description">
                            <?php echo nl2br(htmlspecialchars($complexData['description'])); ?>
                        </div>
                    </div>

                    <div class="info-block">
                        <h3>Расположение</h3>
                        <p class="address"><?php echo htmlspecialchars($complexData['address']); ?></p>
                    </div>

                    <div class="info-block">
                        <h3>Стоимость</h3>
                        <?php if ($complexData['price_from'] || $complexData['price_to']): ?>
                            <p class="price">
                                <?php if ($complexData['price_from']): ?>
                                    От <?php echo number_format($complexData['price_from'], 0, ',', ' '); ?> ₽
                                <?php endif; ?>
                                <?php if ($complexData['price_to']): ?>
                                    до <?php echo number_format($complexData['price_to'], 0, ',', ' '); ?> ₽
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($complexData['completion_date']): ?>
                        <div class="info-block">
                            <h3>Срок сдачи</h3>
                            <p class="completion-date">
                                <?php echo date('d.m.Y', strtotime($complexData['completion_date'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($complexData['status']): ?>
                        <div class="info-block">
                            <h3>Статус</h3>
                            <p class="status"><?php echo htmlspecialchars($complexData['status']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($features)): ?>
                        <div class="info-block">
                            <h3>Характеристики комплекса</h3>
                            <div class="features">
                                <ul>
                                    <?php foreach ($features as $feature): ?>
                                        <li>
                                            <strong><?php echo htmlspecialchars($feature['feature_name']); ?>:</strong>
                                            <?php echo htmlspecialchars($feature['feature_value']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="contact-form">
                    <h3>Оставить заявку</h3>
                    <form action="/php/handlers/feedback.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="complex_id" value="<?php echo $id; ?>">
                        
                        <div class="form-group">
                            <label for="name">Ваше имя</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Телефон</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Сообщение</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">Отправить заявку</button>
                    </form>
                </div>
            </div>
        </article>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Все права защищены.</p>
        </div>
    </footer>
</body>
</html> 