<?php
// Устанавливаем кодировку
header('Content-Type: text/html; charset=utf-8');

// Получаем данные из формы
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Проверяем обязательные поля
$errors = [];
if (empty($name)) {
    $errors[] = 'Поле "Имя" обязательно для заполнения.';
}

if (empty($email)) {
    $errors[] = 'Поле "Email" обязательно для заполнения.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Укажите корректный email адрес.';
}

if (empty($phone)) {
    $errors[] = 'Поле "Телефон" обязательно для заполнения.';
}

if (empty($message)) {
    $errors[] = 'Поле "Сообщение" обязательно для заполнения.';
}

// Если есть ошибки - выводим их
if (!empty($errors)) {
    echo '<div style="max-width: 500px; margin: 20px auto; padding: 20px; background: #ffebee; border: 1px solid #ef9a9a; border-radius: 4px;">';
    echo '<h3 style="color: #c62828;">Ошибки при заполнении формы:</h3>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '<a href="contact.html" style="display: inline-block; margin-top: 15px; color: #1565c0;">Вернуться к форме</a>';
    echo '</div>';
    exit;
}

// Настройки для отправки письма
$to = 'noreply@sz-novostroi-yug.ru'; // Замените на ваш email с Reg.ru
$subject = 'Новое сообщение с формы обратной связи';
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

// Формируем тело письма
$email_body = "Вы получили новое сообщение с формы обратной связи.\n\n";
$email_body .= "Имя: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Телефон: $phone\n\n";
$email_body .= "Сообщение:\n$message\n";

// Пытаемся отправить письмо
if (mail($to, $subject, $email_body, $headers)) {
    // Письмо отправлено успешно
    echo '<div style="max-width: 500px; margin: 20px auto; padding: 20px; background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 4px; text-align: center;">';
    echo '<h3 style="color: #2e7d32;">Спасибо за ваше сообщение!</h3>';
    echo '<p>Мы получили ваше сообщение и свяжемся с вами в ближайшее время.</p>';
    echo '<a href="contact.html" style="display: inline-block; margin-top: 15px; padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Вернуться на сайт</a>';
    echo '</div>';
} else {
    // Ошибка при отправке
    echo '<div style="max-width: 500px; margin: 20px auto; padding: 20px; background: #ffebee; border: 1px solid #ef9a9a; border-radius: 4px; text-align: center;">';
    echo '<h3 style="color: #c62828;">Ошибка при отправке сообщения</h3>';
    echo '<p>При отправке вашего сообщения произошла ошибка. Пожалуйста, попробуйте позже.</p>';
    echo '<a href="contact.html" style="display: inline-block; margin-top: 15px; padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Вернуться к форме</a>';
    echo '</div>';
}
?>