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
<html lang="<?= \App\Locale::getLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __("title") ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📱 Telegram Channel Postback</h1>
            <p class="subtitle"><?= __("subtitle") ?></p>
            <div class="header-actions">
                <div class="lang-switcher">
                    <a href="?lang=ru" class="lang-btn <?= \App\Locale::getLang() === 'ru' ? 'active' : '' ?>" data-lang="ru"><?= __("lang_ru") ?></a>
                    <a href="?lang=en" class="lang-btn <?= \App\Locale::getLang() === 'en' ? 'active' : '' ?>" data-lang="en"><?= __("lang_en") ?></a>
                </div>
                <a href="?logout" class="btn-logout" title="<?= __("logout") ?>">🚪 <?= __("logout") ?></a>
            </div>
        </header>

        <div class="stats-grid" id="stats">
            <div class="stat-card">
                <div class="stat-value" id="stat-total">-</div>
                <div class="stat-label"><?= __("stat_total") ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-pending">-</div>
                <div class="stat-label"><?= __("stat_pending") ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-subscribed">-</div>
                <div class="stat-label"><?= __("stat_subscribed") ?></div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('bots')"><?= __("tab_bots") ?></button>
            <button class="tab-btn" onclick="switchTab('channels')"><?= __("tab_channels") ?></button>
            <button class="tab-btn" onclick="switchTab('logs')"><?= __("tab_logs") ?></button>
        </div>

        <!-- BOTS TAB -->
        <div id="tab-bots" class="tab-content active">
            <div class="section-header">
                <h2><?= __("section_bots") ?></h2>
                <button class="btn btn-primary" onclick="showAddBotModal()"><?= __("add_bot") ?></button>
            </div>

            <div class="table-container">
                <table id="bots-table">
                    <thead>
                        <tr>
                            <th><?= __("table_id") ?></th>
                            <th><?= __("table_name") ?></th>
                            <th><?= __("table_status") ?></th>
                            <th><?= __("table_created") ?></th>
                            <th><?= __("table_actions") ?></th>
                        </tr>
                    </thead>
                    <tbody id="bots-tbody">
                        <tr><td colspan="5" class="loading"><?= __("loading") ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CHANNELS TAB -->
        <div id="tab-channels" class="tab-content">
            <div class="section-header">
                <h2><?= __("section_channels") ?></h2>
                <button class="btn btn-primary" onclick="showAddChannelModal()"><?= __("add_channel") ?></button>
            </div>

            <div class="table-container">
                <table id="channels-table">
                    <thead>
                        <tr>
                            <th><?= __("table_id") ?></th>
                            <th><?= __("table_channel") ?></th>
                            <th><?= __("table_bot") ?></th>
                            <th><?= __("table_status") ?></th>
                            <th><?= __("table_actions") ?></th>
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
            <h2><?= __("section_logs") ?></h2>
            <div class="log-info">
                <p>📁 <?= __("logs_dir") ?> <code>logs/</code></p>
                <ul>
                    <li><strong>app.log</strong> - <?= __("logs_app") ?></li>
                    <li><strong>webhook.log</strong> - <?= __("logs_webhook") ?></li>
                    <li><strong>postback.log</strong> - <?= __("logs_postback") ?></li>
                    <li><strong>errors.log</strong> - <?= __("logs_errors") ?></li>
                </ul>
                <p><?= __("logs_view") ?> <code>tail -f logs/app.log</code></p>
            </div>
        </div>
    </div>

    <!-- Add Bot Modal -->
    <div id="modal-add-bot" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-add-bot')">&times;</span>
            <h2><?= __("modal_add_bot_title") ?></h2>
            <form id="form-add-bot" onsubmit="addBot(event)">
                <div class="form-group">
                    <label><?= __("label_bot_name") ?></label>
                    <input type="text" name="name" required placeholder="<?= __("placeholder_bot_name") ?>">
                </div>
                <div class="form-group">
                    <label><?= __("label_bot_token") ?></label>
                    <input type="text" name="token" required placeholder="<?= __("placeholder_bot_token") ?>">
                    <small><?= __("hint_bot_token") ?> <a href="https://t.me/BotFather" target="_blank">@BotFather</a></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-bot')"><?= __("btn_cancel") ?></button>
                    <button type="submit" class="btn btn-primary"><?= __("btn_add") ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Channel Modal -->
    <div id="modal-add-channel" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-add-channel')">&times;</span>
            <h2><?= __("modal_add_channel_title") ?></h2>
            <form id="form-add-channel" onsubmit="addChannel(event)">
                <div class="form-group">
                    <label><?= __("label_select_bot") ?></label>
                    <select name="bot_id" id="channel-bot-select" required>
                        <option value=""><?= __("option_select_bot") ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= __("label_channel_id") ?></label>
                    <input type="text" name="channel_id" required placeholder="<?= __("placeholder_channel_id") ?>">
                    <small><?= __("hint_channel_id") ?></small>
                </div>
                <div class="form-group">
                    <label><?= __("label_channel_name") ?></label>
                    <input type="text" name="channel_name" placeholder="<?= __("placeholder_channel_name") ?>">
                </div>
                <div class="form-group">
                    <label><?= __("label_postback_url") ?></label>
                    <input type="text" name="postback_url" required placeholder="<?= __("placeholder_postback_url") ?>">
                    <small><?= __("hint_postback_url") ?> <code>{clickid}</code>, <code>{status}</code>, <code>{user_id}</code>, <code>{first_name}</code>, <code>{last_name}</code>, <code>{username}</code>, <code>{is_premium}</code>, <code>{language_code}</code></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-channel')"><?= __("btn_cancel") ?></button>
                    <button type="submit" class="btn btn-primary"><?= __("btn_add") ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Bot Modal -->
    <div id="modal-edit-bot" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-edit-bot')">&times;</span>
            <h2><?= __("modal_edit_bot_title") ?></h2>
            <form id="form-edit-bot" onsubmit="updateBot(event)">
                <input type="hidden" name="id" id="edit-bot-id">
                <div class="form-group">
                    <label><?= __("label_bot_name") ?></label>
                    <input type="text" name="name" id="edit-bot-name" required placeholder="<?= __("placeholder_bot_name") ?>">
                </div>
                <div class="form-group">
                    <label><?= __("label_bot_token") ?></label>
                    <input type="text" name="token" id="edit-bot-token" required placeholder="<?= __("placeholder_bot_token") ?>">
                    <small><?= __("hint_bot_token") ?> <a href="https://t.me/BotFather" target="_blank">@BotFather</a></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-bot')"><?= __("btn_cancel") ?></button>
                    <button type="submit" class="btn btn-primary"><?= __("btn_save") ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Channel Modal -->
    <div id="modal-edit-channel" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-edit-channel')">&times;</span>
            <h2><?= __("modal_edit_channel_title") ?></h2>
            <form id="form-edit-channel" onsubmit="updateChannel(event)">
                <input type="hidden" name="id" id="edit-channel-id">
                <div class="form-group">
                    <label><?= __("label_channel_id_disabled") ?></label>
                    <input type="text" id="edit-channel-channel-id" disabled>
                    <small><?= __("hint_channel_id_disabled") ?></small>
                </div>
                <div class="form-group">
                    <label><?= __("label_channel_name") ?></label>
                    <input type="text" name="channel_name" id="edit-channel-name" placeholder="<?= __("placeholder_channel_name") ?>">
                </div>
                <div class="form-group">
                    <label><?= __("label_postback_url") ?></label>
                    <input type="text" name="postback_url" id="edit-channel-postback" required placeholder="<?= __("placeholder_postback_url") ?>">
                    <small><?= __("hint_postback_url") ?> <code>{clickid}</code>, <code>{status}</code>, <code>{user_id}</code>, <code>{first_name}</code>, <code>{last_name}</code>, <code>{username}</code>, <code>{is_premium}</code>, <code>{language_code}</code></small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-channel')"><?= __("btn_cancel") ?></button>
                    <button type="submit" class="btn btn-primary"><?= __("btn_save") ?></button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/lang.js"></script>
    <script src="assets/script.js"></script>
</body>
</html>
