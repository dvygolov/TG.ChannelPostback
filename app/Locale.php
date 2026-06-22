<?php

namespace App;

class Locale
{
    private static string $lang = 'ru';

    private static array $translations = [
        'ru' => [
            'title' => 'Telegram Channel Postback - Админ панель',
            'login_title' => 'Вход - Telegram Channel Postback',
            'h1' => 'Telegram Channel Postback',
            'subtitle' => 'Система отслеживания подписчиков и постбэков',
            'logout' => 'Выход',
            'lang_en' => 'EN',
            'lang_ru' => 'RU',
            'stat_total' => 'Всего инвайтов',
            'stat_pending' => 'Ожидают',
            'stat_subscribed' => 'Подписались',
            'tab_bots' => 'Боты',
            'tab_channels' => 'Каналы',
            'tab_logs' => 'Логи',
            'section_bots' => 'Управление ботами',
            'add_bot' => '+ Добавить бота',
            'section_channels' => 'Управление каналами',
            'add_channel' => '+ Добавить канал',
            'section_logs' => 'Логи',
            'table_id' => 'ID',
            'table_name' => 'Название',
            'table_status' => 'Статус',
            'table_created' => 'Создан',
            'table_actions' => 'Действия',
            'table_channel' => 'Канал',
            'table_bot' => 'Бот',
            'loading' => 'Загрузка...',
            'no_bots' => 'Нет ботов. Добавьте первого!',
            'no_channels' => 'Нет каналов. Добавьте первый!',
            'status_active' => 'Активен',
            'status_inactive' => 'Неактивен',
            'logs_dir' => 'Логи хранятся в директории',
            'logs_app' => 'основные события',
            'logs_webhook' => 'входящие webhook\'и от Telegram',
            'logs_postback' => 'отправленные постбэки',
            'logs_errors' => 'ошибки',
            'logs_view' => 'Для просмотра используйте:',
            'modal_add_bot_title' => 'Добавить бота',
            'modal_edit_bot_title' => 'Редактировать бота',
            'modal_add_channel_title' => 'Добавить канал',
            'modal_edit_channel_title' => 'Редактировать канал',
            'label_bot_name' => 'Название бота *',
            'placeholder_bot_name' => 'Мой бот',
            'label_bot_token' => 'Токен бота *',
            'placeholder_bot_token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz',
            'hint_bot_token' => 'Получите токен у',
            'label_select_bot' => 'Выберите бота *',
            'option_select_bot' => '-- Выберите бота --',
            'label_channel_id' => 'ID канала *',
            'placeholder_channel_id' => '@mychannel или -1001234567890',
            'hint_channel_id' => 'Бот должен быть добавлен администратором канала с правом создания инвайт-ссылок',
            'label_channel_name' => 'Название канала (опционально)',
            'placeholder_channel_name' => 'Автоматически из Telegram',
            'label_postback_url' => 'Postback URL *',
            'placeholder_postback_url' => 'https://tracker.com/postback?clickid={clickid}&status={status}',
            'hint_postback_url' => 'Доступные макросы:',
            'label_channel_id_disabled' => 'ID канала',
            'hint_channel_id_disabled' => 'ID канала нельзя изменить',
            'btn_cancel' => 'Отмена',
            'btn_add' => 'Добавить',
            'btn_save' => 'Сохранить',
            'login_h1' => 'Вход в систему',
            'login_password' => 'Пароль',
            'login_placeholder' => 'Введите пароль',
            'login_btn' => 'Войти',
            'login_info_title' => 'Защита от перебора:',
            'login_info_3' => '3 попытки — таймаут 5 минут',
            'login_info_5' => '5 попыток — таймаут 15 минут',
            'login_info_7' => '7 попыток — таймаут 1 час',
            'error_wrong_password' => 'Неверный пароль',
            'error_too_many_attempts' => 'Слишком много попыток. Подождите',
            'error_seconds' => 'секунд',
            'title_edit' => 'Редактировать',
            'title_deactivate' => 'Деактивировать',
            'title_activate' => 'Активировать',
            'title_delete' => 'Удалить',
            'title_copy_link' => 'Скопировать ссылку для лендинга',
            'confirm_toggle_bot' => 'Изменить статус бота?',
            'confirm_delete_bot' => 'Будут удалены все связанные каналы и инвайты!',
            'confirm_toggle_channel' => 'Изменить статус канала?',
            'confirm_delete_channel' => 'Будут удалены все связанные инвайты!',
            'success_bot_added' => 'Бот успешно добавлен!',
            'success_bot_updated' => 'Бот успешно обновлён!',
            'success_bot_status' => 'Статус бота обновлён',
            'success_bot_deleted' => 'Бот удалён',
            'success_channel_added' => 'Канал успешно добавлен!',
            'success_channel_updated' => 'Канал успешно обновлён!',
            'success_channel_status' => 'Статус канала обновлён',
            'success_channel_deleted' => 'Канал удалён',
            'success_link_copied' => 'Ссылка скопирована в буфер обмена!',
            'error_load_bots' => 'Ошибка загрузки ботов',
            'error_load_channels' => 'Ошибка загрузки каналов',
            'error_add_bot' => 'Ошибка при добавлении бота',
            'error_update_bot' => 'Ошибка при обновлении бота',
            'error_add_channel' => 'Ошибка при добавлении канала',
            'error_update_channel' => 'Ошибка при обновлении канала',
            'error_generic' => 'Ошибка при обновлении',
            'error_delete' => 'Ошибка при удалении',
            'error_link_copy' => 'Не удалось скопировать ссылку',
            'error_add_bot_first' => 'Сначала добавьте хотя бы одного бота!',
            'check' => 'Проверка...',
            'unknown' => 'Unknown',
        ],
        'en' => [
            'title' => 'Telegram Channel Postback - Admin Panel',
            'login_title' => 'Login - Telegram Channel Postback',
            'h1' => 'Telegram Channel Postback',
            'subtitle' => 'Subscriber tracking & postback system',
            'logout' => 'Logout',
            'lang_en' => 'EN',
            'lang_ru' => 'RU',
            'stat_total' => 'Total invites',
            'stat_pending' => 'Pending',
            'stat_subscribed' => 'Subscribed',
            'tab_bots' => 'Bots',
            'tab_channels' => 'Channels',
            'tab_logs' => 'Logs',
            'section_bots' => 'Bot Management',
            'add_bot' => '+ Add Bot',
            'section_channels' => 'Channel Management',
            'add_channel' => '+ Add Channel',
            'section_logs' => 'Logs',
            'table_id' => 'ID',
            'table_name' => 'Name',
            'table_status' => 'Status',
            'table_created' => 'Created',
            'table_actions' => 'Actions',
            'table_channel' => 'Channel',
            'table_bot' => 'Bot',
            'loading' => 'Loading...',
            'no_bots' => 'No bots. Add the first one!',
            'no_channels' => 'No channels. Add the first one!',
            'status_active' => 'Active',
            'status_inactive' => 'Inactive',
            'logs_dir' => 'Logs are stored in directory',
            'logs_app' => 'main events',
            'logs_webhook' => 'incoming Telegram webhooks',
            'logs_postback' => 'sent postbacks',
            'logs_errors' => 'errors',
            'logs_view' => 'To view use:',
            'modal_add_bot_title' => 'Add Bot',
            'modal_edit_bot_title' => 'Edit Bot',
            'modal_add_channel_title' => 'Add Channel',
            'modal_edit_channel_title' => 'Edit Channel',
            'label_bot_name' => 'Bot Name *',
            'placeholder_bot_name' => 'My Bot',
            'label_bot_token' => 'Bot Token *',
            'placeholder_bot_token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz',
            'hint_bot_token' => 'Get token from',
            'label_select_bot' => 'Select Bot *',
            'option_select_bot' => '-- Select Bot --',
            'label_channel_id' => 'Channel ID *',
            'placeholder_channel_id' => '@mychannel or -1001234567890',
            'hint_channel_id' => 'Bot must be admin with invite link creation permission',
            'label_channel_name' => 'Channel Name (optional)',
            'placeholder_channel_name' => 'Auto from Telegram',
            'label_postback_url' => 'Postback URL *',
            'placeholder_postback_url' => 'https://tracker.com/postback?clickid={clickid}&status={status}',
            'hint_postback_url' => 'Available macros:',
            'label_channel_id_disabled' => 'Channel ID',
            'hint_channel_id_disabled' => 'Channel ID cannot be changed',
            'btn_cancel' => 'Cancel',
            'btn_add' => 'Add',
            'btn_save' => 'Save',
            'login_h1' => 'System Login',
            'login_password' => 'Password',
            'login_placeholder' => 'Enter password',
            'login_btn' => 'Login',
            'login_info_title' => 'Brute-force protection:',
            'login_info_3' => '3 attempts — 5 min timeout',
            'login_info_5' => '5 attempts — 15 min timeout',
            'login_info_7' => '7 attempts — 1 hour timeout',
            'error_wrong_password' => 'Wrong password',
            'error_too_many_attempts' => 'Too many attempts. Wait',
            'error_seconds' => 'seconds',
            'title_edit' => 'Edit',
            'title_deactivate' => 'Deactivate',
            'title_activate' => 'Activate',
            'title_delete' => 'Delete',
            'title_copy_link' => 'Copy landing page link',
            'confirm_toggle_bot' => 'Change bot status?',
            'confirm_delete_bot' => 'All related channels and invites will be deleted!',
            'confirm_toggle_channel' => 'Change channel status?',
            'confirm_delete_channel' => 'All related invites will be deleted!',
            'success_bot_added' => 'Bot added successfully!',
            'success_bot_updated' => 'Bot updated successfully!',
            'success_bot_status' => 'Bot status updated',
            'success_bot_deleted' => 'Bot deleted',
            'success_channel_added' => 'Channel added successfully!',
            'success_channel_updated' => 'Channel updated successfully!',
            'success_channel_status' => 'Channel status updated',
            'success_channel_deleted' => 'Channel deleted',
            'success_link_copied' => 'Link copied to clipboard!',
            'error_load_bots' => 'Failed to load bots',
            'error_load_channels' => 'Failed to load channels',
            'error_add_bot' => 'Error adding bot',
            'error_update_bot' => 'Error updating bot',
            'error_add_channel' => 'Error adding channel',
            'error_update_channel' => 'Error updating channel',
            'error_generic' => 'Update error',
            'error_delete' => 'Delete error',
            'error_link_copy' => 'Failed to copy link',
            'error_add_bot_first' => 'Add at least one bot first!',
            'check' => 'Checking...',
            'unknown' => 'Unknown',
        ],
    ];

    public static function init(): void
    {
        session_start();
        
        // Check GET parameter
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
            self::$lang = $_GET['lang'];
            $_SESSION['lang'] = self::$lang;
            return;
        }
        
        // Check session
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['ru', 'en'])) {
            self::$lang = $_SESSION['lang'];
            return;
        }
        
        // Default
        self::$lang = 'ru';
    }

    public static function getLang(): string
    {
        return self::$lang;
    }

    public static function setLang(string $lang): void
    {
        if (in_array($lang, ['ru', 'en'])) {
            self::$lang = $lang;
            $_SESSION['lang'] = $lang;
        }
    }

    public static function t(string $key, string $default = ''): string
    {
        return self::$translations[self::$lang][$key] ?? $default;
    }

    public static function all(): array
    {
        return self::$translations[self::$lang] ?? self::$translations['ru'];
    }
}

