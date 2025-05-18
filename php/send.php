<!-- send.php -->
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    $to = "vrtxw@list.ru";
    $subject = "Новое сообщение с сайта";
    
    $body = "Имя: $name\n";
    $body .= "Email: $email\n";
    $body .= "Сообщение: $message\n";
    
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    mail($to, $subject, $body, $headers);
}
?>