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
<html lang="<?= \App\Locale::getLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __("login_title") ?></title>
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

        .lang-switcher {
            display: flex;
            gap: 4px;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 4px;
            border: 1px solid #e9ecef;
        }

        .lang-btn {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            color: #6c757d;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            background: transparent;
        }

        .lang-btn:hover {
            color: #667eea;
            background: white;
        }

        .lang-btn.active {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">🔐</div>
            <h1><?= __("login_h1") ?></h1>
            <p>Telegram Channel Postback</p>
            <div class="lang-switcher" style="margin-top: 15px;">
                <a href="?lang=ru" class="lang-btn <?= \App\Locale::getLang() === 'ru' ? 'active' : '' ?>" data-lang="ru"><?= __("lang_ru") ?></a>
                <a href="?lang=en" class="lang-btn <?= \App\Locale::getLang() === 'en' ? 'active' : '' ?>" data-lang="en"><?= __("lang_en") ?></a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="password"><?= __("login_password") ?></label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autofocus
                       placeholder="<?= __("login_placeholder") ?>">
            </div>

            <button type="submit" class="btn-login">
                <?= __("login_btn") ?>
            </button>
        </form>

        <div class="info-box">
            <strong>🛡️ <?= __("login_info_title") ?></strong><br>
            • <?= __("login_info_3") ?><br>
            • <?= __("login_info_5") ?><br>
            • <?= __("login_info_7") ?>
        </div>
    </div>
</body>
</html>
