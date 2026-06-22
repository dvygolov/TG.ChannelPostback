<?php
require_once __DIR__ . '/../autoload.php';

use App\Auth;

// Check authentication
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    Auth::logout();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Channel Postback - Admin Panel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📱 Telegram Channel Postback</h1>
            <p class="subtitle">Система отслеживания подписчиков и постбэков</p>
            <a href="?logout" class="btn-logout" title="Выйти">🚪 Выход</a>
        </header>

        <div class="stats-grid" id="stats">
            <div class="stat-card">
                <div class="stat-value" id="stat-total">-</div>
                <div class="stat-label">Всего инвайтов</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-pending">-</div>
                <div class="stat-label">Ожидают</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-subscribed">-</div>
                <div class="stat-label">Подписались</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('bots')">Боты</button>
            <button class="tab-btn" onclick="switchTab('channels')">Каналы</button>
            <button class="tab-btn" onclick="switchTab('logs')">Логи</button>
        </div>

        <!-- BOTS TAB -->
        <div id="tab-bots" class="tab-content active">
            <div class="section-header">
                <h2>Управление ботами</h2>
                <button class="btn btn-primary" onclick="showAddBotModal()">+ Добавить бота</button>
            </div>

            <div class="table-container">
                <table id="bots-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Статус</th>
                            <th>Создан</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody id="bots-tbody">
                        <tr><td colspan="5" class="loading">Загрузка...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CHANNELS TAB -->
        <div id="tab-channels" class="tab-content">
            <div class="section-header">
                <h2>Управление каналами</h2>
                <button class="btn btn-primary" onclick="showAddChannelModal()">+ Добавить канал</button>
            </div>

            <div class="table-container">
                <table id="channels-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Канал</th>
                            <th>Бот</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody id="channels-tbody">
                        <tr><td colspan="5" class="loading">Загрузка...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- LOGS TAB -->
        <div id="tab-logs" class="tab-content">
            <h2>Логи</h2>
            <div class="log-info">
                <p>📁 Логи хранятся в директории <code>logs/</code></p>
                <ul>
                    <li><strong>app.log</strong> - основные события</li>
                    <li><strong>webhook.log</strong> - входящие webhook'и от Telegram</li>
                    <li><strong>postback.log</strong> - отправленные постбэки</li>
                    <li><strong>errors.log</strong> - ошибки</li>
                </ul>
                <p>Для просмотра используйте: <code>tail -f logs/app.log</code></p>
            </div>
        </div>
    </div>

    <!-- Add Bot Modal -->
    <div id="modal-add-bot" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-add-bot')">&times;</span>
            <h2>Добавить бота</h2>
            <form id="form-add-bot" onsubmit="addBot(event)">
                <div class="form-group">
                    <label>Название бота *</label>
                    <input type="text" name="name" required placeholder="Мой бот">
                </div>
                <div class="form-group">
                    <label>Токен бота *</label>
                    <input type="text" name="token" required placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                    <small>Получите токен у <a href="https://t.me/BotFather" target="_blank">@BotFather</a></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-bot')">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Channel Modal -->
    <div id="modal-add-channel" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-add-channel')">&times;</span>
            <h2>Добавить канал</h2>
            <form id="form-add-channel" onsubmit="addChannel(event)">
                <div class="form-group">
                    <label>Выберите бота *</label>
                    <select name="bot_id" id="channel-bot-select" required>
                        <option value="">-- Выберите бота --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ID канала *</label>
                    <input type="text" name="channel_id" required placeholder="@mychannel или -1001234567890">
                    <small>Бот должен быть добавлен администратором канала с правом создания инвайт-ссылок</small>
                </div>
                <div class="form-group">
                    <label>Название канала (опционально)</label>
                    <input type="text" name="channel_name" placeholder="Автоматически из Telegram">
                </div>
                <div class="form-group">
                    <label>Postback URL *</label>
                    <input type="text" name="postback_url" required placeholder="https://tracker.com/postback?clickid={clickid}&status={status}">
                    <small>Доступные макросы: <code>{clickid}</code>, <code>{status}</code>, <code>{user_id}</code>, <code>{first_name}</code>, <code>{last_name}</code>, <code>{username}</code>, <code>{is_premium}</code>, <code>{language_code}</code></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-channel')">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Bot Modal -->
    <div id="modal-edit-bot" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-edit-bot')">&times;</span>
            <h2>Редактировать бота</h2>
            <form id="form-edit-bot" onsubmit="updateBot(event)">
                <input type="hidden" name="id" id="edit-bot-id">
                <div class="form-group">
                    <label>Название бота *</label>
                    <input type="text" name="name" id="edit-bot-name" required placeholder="Мой бот">
                </div>
                <div class="form-group">
                    <label>Токен бота *</label>
                    <input type="text" name="token" id="edit-bot-token" required placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                    <small>Получите токен у <a href="https://t.me/BotFather" target="_blank">@BotFather</a></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-bot')">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Channel Modal -->
    <div id="modal-edit-channel" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-edit-channel')">&times;</span>
            <h2>Редактировать канал</h2>
            <form id="form-edit-channel" onsubmit="updateChannel(event)">
                <input type="hidden" name="id" id="edit-channel-id">
                <div class="form-group">
                    <label>ID канала</label>
                    <input type="text" id="edit-channel-channel-id" disabled>
                    <small>ID канала нельзя изменить</small>
                </div>
                <div class="form-group">
                    <label>Название канала (опционально)</label>
                    <input type="text" name="channel_name" id="edit-channel-name" placeholder="Автоматически из Telegram">
                </div>
                <div class="form-group">
                    <label>Postback URL *</label>
                    <input type="text" name="postback_url" id="edit-channel-postback" required placeholder="https://tracker.com/postback?clickid={clickid}&status={status}">
                    <small>Доступные макросы: <code>{clickid}</code>, <code>{status}</code>, <code>{user_id}</code>, <code>{first_name}</code>, <code>{last_name}</code>, <code>{username}</code>, <code>{is_premium}</code>, <code>{language_code}</code></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-channel')">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
