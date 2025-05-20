<!-- send.php -->
<?php
header('Content-Type: application/json');

// Проверка метода запроса
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

// Проверка наличия всех полей
if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все поля']);
    exit;
}

// Получение и очистка данных
$name = strip_tags(trim($_POST['name']));
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$message = strip_tags(trim($_POST['message']));

// Валидация email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Пожалуйста, введите корректный email']);
    exit;
}

// Настройка письма
$to = "vrtxw@list.ru";
$subject = "Новое сообщение с сайта";

$body = "Имя: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Сообщение:\n" . $message . "\n";

// Настройка заголовков для кодировки UTF-8
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Отправка письма
if (mail($to, $subject, $body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Спасибо! Ваше сообщение успешно отправлено']);
} else {
    echo json_encode(['success' => false, 'message' => 'Извините, произошла ошибка при отправке сообщения']);
}
?>