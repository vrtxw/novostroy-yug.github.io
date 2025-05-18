<?php
// Установите сюда свою почту
$to = "vrtxw@list.ru";

// Получаем данные из формы
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$message = htmlspecialchars($_POST['message']);

// Тема письма
$subject = "Новое сообщение с сайта";

// Формируем текст письма
$body = "
<html>
<head>
  <title>Новое сообщение</title>
</head>
<body>
  <h2>Форма обратной связи</h2>
  <p><strong>Имя:</strong> {$name}</p>
  <p><strong>Email:</strong> {$email}</p>
  <p><strong>Сообщение:</strong><br>{$message}</p>
</body>
</html>
";

// Заголовки письма
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
$headers .= "From: Сайт обратной связи <no-reply@example.com>" . "\r\n";

// Отправляем
if (mail($to, $subject, $body, $headers)) {
    echo "Спасибо, сообщение отправлено!";
} else {
    echo "Ошибка при отправке.";
}
?>