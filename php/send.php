<?php
define('SECURE_ACCESS', true);
require_once 'config.php';
require_once 'Mailer.php';

header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/mail_errors.log');

// Создаем директорию для логов если её нет
$log_dir = __DIR__ . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Логируем начало обработки запроса
error_log("Processing form submission. Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

// Проверяем CSRF токен
if (!isset($_POST['csrf_token'])) {
    error_log("CSRF token is missing");
    echo json_encode(['success' => false, 'message' => 'Ошибка безопасности: отсутствует токен']);
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    error_log("CSRF validation failed for token: " . $_POST['csrf_token']);
    echo json_encode(['success' => false, 'message' => 'Ошибка безопасности: неверный токен']);
    exit;
}

try {
    // Получаем данные из формы
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $complex_id = isset($_POST['complex_id']) ? (int)$_POST['complex_id'] : null;

    // Проверяем обязательные поля
    if (empty($name) || empty($email) || empty($message)) {
        error_log("Required fields missing. Name: $name, Email: $email, Message length: " . strlen($message));
        echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все обязательные поля']);
        exit;
    }

    // Проверяем формат email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: $email");
        echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
        exit;
    }

    // Проверяем длину сообщения
    if (strlen($message) > MAX_MESSAGE_LENGTH) {
        error_log("Message too long: " . strlen($message) . " characters");
        echo json_encode(['success' => false, 'message' => 'Сообщение слишком длинное']);
        exit;
    }

    // Сохраняем сообщение в базу данных
    $db = Database::getInstance();
    $sql = "INSERT INTO contact_messages (name, phone, email, message, complex_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Ошибка подготовки запроса: " . $db->error);
    }
    
    $stmt->bind_param("ssssi", $name, $phone, $email, $message, $complex_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Ошибка при сохранении сообщения: " . $stmt->error);
    }

    // Отправляем письмо
    $mailer = new Mailer();
    
    // Логируем попытку отправки
    error_log("Attempting to send email to " . ADMIN_EMAIL . " from " . $email);
    
    $result = $mailer->sendContactForm([
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'message' => $message,
        'complex_id' => $complex_id
    ]);

    // Получаем отладочную информацию
    $debug = $mailer->getDebugInfo();
    error_log("Email sending debug info: " . print_r($debug, true));

    if (!$result) {
        throw new Exception("Ошибка при отправке email");
    }

    // Закрываем statement
    $stmt->close();

    // Генерируем новый CSRF токен для следующего запроса
    $newToken = generateCSRFToken();

    echo json_encode([
        'success' => true, 
        'message' => 'Спасибо! Ваше сообщение отправлено.',
        'newToken' => $newToken,
        'debug' => $debug
    ]);

} catch (Exception $e) {
    // Логируем ошибку
    error_log("Error in send.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $debug = isset($mailer) ? $mailer->getDebugInfo() : [];
    $debug[] = "Exception: " . $e->getMessage();
    
    // Генерируем новый CSRF токен даже в случае ошибки
    $newToken = generateCSRFToken();
    
    echo json_encode([
        'success' => false, 
        'message' => 'Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте позже.',
        'newToken' => $newToken,
        'debug' => $debug
    ]);

} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
} 