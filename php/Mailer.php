<?php
require_once __DIR__ . '/phpmailer/PHPMailer-6.8.0/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/PHPMailer-6.8.0/src/SMTP.php';
require_once __DIR__ . '/phpmailer/PHPMailer-6.8.0/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    private $debug = [];

    public function __construct() {
        try {
            $this->log("Инициализация почтового сервиса...");
            
            $this->mailer = new PHPMailer(true);
            
            // Включаем подробное логирование
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mailer->Debugoutput = function($str, $level) {
                $this->debug[] = "Debug [$level]: $str";
                error_log("PHPMailer Debug [$level]: $str");
            };
            
            // Настройка SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = SMTP_AUTH;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            
            // Устанавливаем таймаут
            $this->mailer->Timeout = 30;
            
            // Общие настройки
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $this->mailer->isHTML(true);
            
            // Логируем настройки
            $this->log("Настройки почты:");
            $this->log("Host: " . SMTP_HOST);
            $this->log("Port: " . SMTP_PORT);
            $this->log("Username: " . SMTP_USERNAME);
            $this->log("From: " . MAIL_FROM);
            $this->log("From Name: " . MAIL_FROM_NAME);
            $this->log("Инициализация завершена");
            
        } catch (Exception $e) {
            $this->log("Ошибка инициализации PHPMailer: " . $e->getMessage());
            throw $e;
        }
    }

    private function log($message) {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] $message";
        $this->debug[] = $logMessage;
        error_log($logMessage);
    }

    public function getDebugInfo() {
        return $this->debug;
    }

    public function sendContactForm($data) {
        try {
            $this->log("Начинаем отправку письма для " . $data['email']);
            
            // Очищаем все адреса получателей
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();
            
            // Добавляем получателя
            $this->mailer->addAddress(ADMIN_EMAIL);
            $this->log("Добавлен получатель: " . ADMIN_EMAIL);
            
            // Добавляем Reply-To
            $this->mailer->addReplyTo($data['email'], $data['name']);
            
            // Устанавливаем тему
            $subject = "Новое сообщение с сайта " . SITE_NAME;
            $this->mailer->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $this->log("Установлена тема письма: " . $subject);
            
            // Формируем тело письма
            $body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Новое сообщение с сайта</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                    .content { background: #fff; padding: 20px; border-radius: 5px; }
                    .field { margin-bottom: 15px; }
                    .label { font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Новое сообщение с сайта</h2>
                    </div>
                    <div class='content'>
                        <div class='field'>
                            <span class='label'>Имя:</span> " . htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8') . "
                        </div>
                        <div class='field'>
                            <span class='label'>Email:</span> " . htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8') . "
                        </div>";
            
            if (!empty($data['phone'])) {
                $body .= "
                        <div class='field'>
                            <span class='label'>Телефон:</span> " . htmlspecialchars($data['phone'], ENT_QUOTES, 'UTF-8') . "
                        </div>";
            }
            
            $body .= "
                        <div class='field'>
                            <span class='label'>Сообщение:</span><br>
                            " . nl2br(htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8')) . "
                        </div>";
            
            if (!empty($data['complex_id'])) {
                $body .= "
                        <div class='field'>
                            <span class='label'>ID комплекса:</span> " . htmlspecialchars($data['complex_id'], ENT_QUOTES, 'UTF-8') . "
                        </div>";
            }
            
            $body .= "
                    </div>
                </div>
            </body>
            </html>";
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</div>'], ["\n", "\n"], $body));
            
            $this->log("Подготовлено тело письма");
            
            // Пытаемся отправить письмо
            if (!$this->mailer->send()) {
                $this->log("Ошибка при отправке: " . $this->mailer->ErrorInfo);
                return false;
            }
            
            $this->log("Письмо успешно отправлено");
            return true;
            
        } catch (Exception $e) {
            $this->log("Ошибка при отправке письма: " . $e->getMessage());
            $this->log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
} 