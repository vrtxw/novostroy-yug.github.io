<!-- send.php -->
<?php
header('Content-Type: application/json');

function sendJsonResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Проверка метода запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    sendJsonResponse(false, 'Неверный метод запроса');
}

// Проверка наличия всех полей
if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['message'])) {
    sendJsonResponse(false, 'Пожалуйста, заполните все поля');
}

// Получение и очистка данных
$name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

// Валидация email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(false, 'Пожалуйста, введите корректный email');
}

// Проверка длины полей
if (strlen($name) < 2 || strlen($name) > 50) {
    sendJsonResponse(false, 'Имя должно быть от 2 до 50 символов');
}

if (strlen($message) < 10 || strlen($message) > 1000) {
    sendJsonResponse(false, 'Сообщение должно быть от 10 до 1000 символов');
}

// Настройка письма
$to = "vrtxw@list.ru";
$subject = "Новое сообщение с сайта Deep Real Estate";

$body = "Получено новое сообщение:\n\n";
$body .= "Имя: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Сообщение:\n" . $message . "\n";

// Настройка заголовков
$headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

// Попытка отправки письма
if (mail($to, $subject, $body, $headers)) {
    sendJsonResponse(true, 'Спасибо! Ваше сообщение успешно отправлено');
} else {
    sendJsonResponse(false, 'Извините, произошла ошибка при отправке сообщения');
}
?>