<?php
namespace PHPMailer\PHPMailer;

class PHPMailer {
    public $SMTPDebug = 0;
    public $Host = 'localhost';
    public $Port = 25;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $CharSet = 'utf-8';
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    private $to = [];
    private $replyTo = [];

    public function isSMTP() {
        return true;
    }

    public function setFrom($address, $name = '') {
        $this->From = $address;
        $this->FromName = $name;
    }

    public function addAddress($address) {
        $this->to[] = $address;
    }

    public function addReplyTo($address, $name = '') {
        $this->replyTo = ['address' => $address, 'name' => $name];
    }

    public function isHTML($isHtml = true) {
        return true;
    }

    public function send() {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=' . $this->CharSet,
            'From: ' . $this->FromName . ' <' . $this->From . '>',
        ];

        if (!empty($this->replyTo)) {
            $headers[] = 'Reply-To: ' . $this->replyTo['name'] . ' <' . $this->replyTo['address'] . '>';
        }

        foreach ($this->to as $recipient) {
            if (!mail($recipient, $this->Subject, $this->Body, implode("\r\n", $headers))) {
                throw new Exception('Mailer Error: ' . error_get_last()['message']);
            }
        }

        return true;
    }
} 