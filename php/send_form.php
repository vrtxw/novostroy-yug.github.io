<?php
define('SECURE_ACCESS', true);
require_once 'config.php';

header('Content-Type: application/json');

// Настройка SMTP для PHP mail()
ini_set('SMTP', SMTP_HOST);
ini_set('smtp_port', SMTP_PORT);
ini_set('sendmail_from', MAIL_FROM);

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
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

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
    $db = Database::getInstance();
    $sql = "INSERT INTO contact_messages (name, phone, email, message, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Ошибка подготовки запроса");
    }
    
    $stmt->bind_param("ssss", $name, $phone, $email, $message);
    
    if (!$stmt->execute()) {
        throw new Exception("Ошибка при сохранении сообщения: " . $stmt->error);
    }

    // Подготовка письма
    $subject = "=?UTF-8?B?" . base64_encode("Новое сообщение с сайта " . SITE_NAME) . "?=";
    
    // HTML версия письма
    $mailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Новое сообщение с сайта</title>
    </head>
    <body>
        <h2>Новое сообщение с сайта</h2>
        <p><strong>Имя:</strong> " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</p>
        <p><strong>Телефон:</strong> " . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</p>
        <p><strong>Сообщение:</strong><br>" . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "</p>
    </body>
    </html>";

    // Заголовки письма
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">";
    $headers[] = "Reply-To: " . $email;
    $headers[] = "X-Mailer: PHP/" . phpversion();
    $headers[] = "X-Priority: 1";

    // Отправляем письмо
    if (!mail(ADMIN_EMAIL, $subject, $mailBody, implode("\r\n", $headers))) {
        error_log("Ошибка при отправке письма на " . ADMIN_EMAIL);
        throw new Exception("Ошибка при отправке email");
    }

    // Логируем успешную отправку
    error_log("Письмо успешно отправлено на " . ADMIN_EMAIL);

    // Закрываем statement
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Спасибо! Ваше сообщение отправлено.']);
} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при отправке сообщения']);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?> 