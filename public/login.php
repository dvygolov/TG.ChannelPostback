<?php

require_once __DIR__ . '/../autoload.php';

use App\Auth;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $result = Auth::login($password);
    
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['error'];
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Telegram Channel Postback</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .login-header h1 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 500;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .info-box {
            margin-top: 20px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .info-box strong {
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">🔐</div>
            <h1>Вход в систему</h1>
            <p>Telegram Channel Postback</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autofocus
                       placeholder="Введите пароль">
            </div>

            <button type="submit" class="btn-login">
                Войти
            </button>
        </form>

        <div class="info-box">
            <strong>🛡️ Защита от перебора:</strong><br>
            • 3 попытки — таймаут 5 минут<br>
            • 5 попыток — таймаут 15 минут<br>
            • 7 попыток — таймаут 1 час
        </div>
    </div>
</body>
</html>
