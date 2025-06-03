<?php
define('SECURE_ACCESS', true);
require_once 'config.php';

header('Content-Type: application/json');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

// Проверяем CSRF токен
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Ошибка безопасности']);
    exit;
}

// Получаем данные из формы
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Проверяем обязательные поля
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все обязательные поля']);
    exit;
}

// Проверяем формат email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
    exit;
}

// Проверяем длину сообщения
if (strlen($message) > MAX_MESSAGE_LENGTH) {
    echo json_encode(['success' => false, 'message' => 'Сообщение слишком длинное']);
    exit;
}

try {
    // Сохраняем сообщение в базу данных
    $sql = "INSERT INTO contact_messages (name, phone, email, message, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $phone, $email, $message);
    
    if (!$stmt->execute()) {
        throw new Exception("Ошибка при сохранении сообщения");
    }

    // Отправляем уведомление на email
    $subject = "Новое сообщение с сайта " . SITE_NAME;
    $mailBody = "Имя: $name\n";
    $mailBody .= "Телефон: $phone\n";
    $mailBody .= "Email: $email\n\n";
    $mailBody .= "Сообщение:\n$message";

    $headers = "From: " . MAIL_FROM . "\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (!mail(ADMIN_EMAIL, $subject, $mailBody, $headers)) {
        throw new Exception("Ошибка при отправке email");
    }

    echo json_encode(['success' => true, 'message' => 'Спасибо! Ваше сообщение отправлено.']);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при отправке сообщения']);
}
?> 