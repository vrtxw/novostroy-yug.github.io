<?php
// Определяем константу до подключения конфига
define('SECURE_ACCESS', true);

// Подключаем конфигурацию
require_once '../php/config.php';

// Если уже авторизован, перенаправляем на главную
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Проверка логина и пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Добавляем отладочную информацию
    error_log("Login attempt - Provided username: " . $username);
    error_log("Expected username: " . ADMIN_USERNAME);
    error_log("Username match: " . ($username === ADMIN_USERNAME ? 'true' : 'false'));
    
    if ($username === ADMIN_USERNAME) {
        error_log("Password provided: " . substr($password, 0, 3) . "***");
        error_log("Stored hash: " . ADMIN_PASSWORD_HASH);
        
        // Проверяем пароль
        $password_verify = password_verify($password, ADMIN_PASSWORD_HASH);
        error_log("Password verification result: " . ($password_verify ? 'true' : 'false'));
        
        if ($password_verify) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['last_activity'] = time();
            $_SESSION['csrf_token'] = generateCSRFToken();
            
            error_log("Login successful - Session data: " . print_r($_SESSION, true));
            header('Location: index.php');
            exit;
        }
    }
    
    error_log("Login failed - Final session data: " . print_r($_SESSION, true));
    $error = 'Неверное имя пользователя или пароль';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <img src="../images/logo.png" alt="<?php echo SITE_NAME; ?>">
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo sanitizeOutput($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           value="<?php echo sanitizeOutput($username ?? ''); ?>"
                           autocomplete="off" autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required 
                           autocomplete="off">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Войти</button>
            </form>
        </div>
    </div>

    <script>
        // Предотвращаем отправку формы при обновлении страницы
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html> 