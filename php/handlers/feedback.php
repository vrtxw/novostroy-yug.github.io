<?php
define('SECURE_ACCESS', true);
require_once '../../php/config.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// Проверяем CSRF-токен
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}

// Проверяем обязательные поля
$required_fields = ['name', 'email', 'message'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        die('Пожалуйста, заполните все обязательные поля');
    }
}

// Валидация email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    die('Пожалуйста, укажите корректный email адрес');
}

try {
    $db = Database::getInstance();
    
    // Подготавливаем данные
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = trim($_POST['message']);
    $complex_id = isset($_POST['complex_id']) ? (int)$_POST['complex_id'] : null;
    
    // Проверяем длину сообщения
    if (mb_strlen($message) > MAX_MESSAGE_LENGTH) {
        die('Сообщение слишком длинное');
    }
    
    // Сохраняем в базу данных
    $stmt = $db->prepare("
        INSERT INTO feedback (name, email, phone, message, complex_id, status) 
        VALUES (?, ?, ?, ?, ?, 'new')
    ");
    
    $stmt->bind_param("ssssi", $name, $email, $phone, $message, $complex_id);
    
    if ($stmt->execute()) {
        // Отправляем уведомление администратору
        $subject = 'Новая заявка с сайта ' . SITE_NAME;
        $mail_body = "Получена новая заявка:\n\n";
        $mail_body .= "Имя: {$name}\n";
        $mail_body .= "Email: {$email}\n";
        if ($phone) {
            $mail_body .= "Телефон: {$phone}\n";
        }
        $mail_body .= "Сообщение:\n{$message}\n";
        
        // Настройки SMTP
        ini_set('SMTP', SMTP_HOST);
        ini_set('smtp_port', SMTP_PORT);
        
        $headers = array(
            'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>',
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/plain; charset=UTF-8'
        );
        
        mail(ADMIN_EMAIL, $subject, $mail_body, implode("\r\n", $headers));
        
        // Возвращаем успешный ответ
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Спасибо! Ваша заявка принята.']);
    } else {
        throw new Exception('Ошибка при сохранении заявки');
    }
    
} catch (Exception $e) {
    error_log("Ошибка в feedback.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при отправке заявки. Пожалуйста, попробуйте позже.']);
} 