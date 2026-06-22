const i18n = {
    ru: {
        title_edit: 'Редактировать',
        title_deactivate: 'Деактивировать',
        title_activate: 'Активировать',
        title_delete: 'Удалить',
        title_copy_link: 'Скопировать ссылку для лендинга',
        confirm_toggle_bot: 'Изменить статус бота?',
        confirm_delete_bot: 'Будут удалены все связанные каналы и инвайты!',
        confirm_toggle_channel: 'Изменить статус канала?',
        confirm_delete_channel: 'Будут удалены все связанные инвайты!',
        success_bot_added: 'Бот успешно добавлен!',
        success_bot_updated: 'Бот успешно обновлён!',
        success_bot_status: 'Статус бота обновлён',
        success_bot_deleted: 'Бот удалён',
        success_channel_added: 'Канал успешно добавлен!',
        success_channel_updated: 'Канал успешно обновлён!',
        success_channel_status: 'Статус канала обновлён',
        success_channel_deleted: 'Канал удалён',
        success_link_copied: 'Ссылка скопирована в буфер обмена!',
        error_load_bots: 'Ошибка загрузки ботов',
        error_load_channels: 'Ошибка загрузки каналов',
        error_add_bot: 'Ошибка при добавлении бота',
        error_update_bot: 'Ошибка при обновлении бота',
        error_add_channel: 'Ошибка при добавлении канала',
        error_update_channel: 'Ошибка при обновлении канала',
        error_generic: 'Ошибка при обновлении',
        error_delete: 'Ошибка при удалении',
        error_link_copy: 'Не удалось скопировать ссылку',
        error_add_bot_first: 'Сначала добавьте хотя бы одного бота!',
        check: 'Проверка...',
        unknown: 'Unknown',
        status_active: 'Активен',
        status_inactive: 'Неактивен',
        no_bots: 'Нет ботов. Добавьте первого!',
        no_channels: 'Нет каналов. Добавьте первый!',
        loading: 'Загрузка...',
    },
    en: {
        title_edit: 'Edit',
        title_deactivate: 'Deactivate',
        title_activate: 'Activate',
        title_delete: 'Delete',
        title_copy_link: 'Copy landing page link',
        confirm_toggle_bot: 'Change bot status?',
        confirm_delete_bot: 'All related channels and invites will be deleted!',
        confirm_toggle_channel: 'Change channel status?',
        confirm_delete_channel: 'All related invites will be deleted!',
        success_bot_added: 'Bot added successfully!',
        success_bot_updated: 'Bot updated successfully!',
        success_bot_status: 'Bot status updated',
        success_bot_deleted: 'Bot deleted',
        success_channel_added: 'Channel added successfully!',
        success_channel_updated: 'Channel updated successfully!',
        success_channel_status: 'Channel status updated',
        success_channel_deleted: 'Channel deleted',
        success_link_copied: 'Link copied to clipboard!',
        error_load_bots: 'Failed to load bots',
        error_load_channels: 'Failed to load channels',
        error_add_bot: 'Error adding bot',
        error_update_bot: 'Error updating bot',
        error_add_channel: 'Error adding channel',
        error_update_channel: 'Error updating channel',
        error_generic: 'Update error',
        error_delete: 'Delete error',
        error_link_copy: 'Failed to copy link',
        error_add_bot_first: 'Add at least one bot first!',
        check: 'Checking...',
        unknown: 'Unknown',
        status_active: 'Active',
        status_inactive: 'Inactive',
        no_bots: 'No bots. Add the first one!',
        no_channels: 'No channels. Add the first one!',
        loading: 'Loading...',
    }
};

let currentLang = localStorage.getItem('tgchan_lang') || document.documentElement.lang || 'ru';
if (!['ru', 'en'].includes(currentLang)) currentLang = 'ru';

function t(key, fallback = '') {
    return i18n[currentLang]?.[key] ?? fallback;
}

function setLang(lang) {
    if (!['ru', 'en'].includes(lang)) return;
    currentLang = lang;
    localStorage.setItem('tgchan_lang', lang);
    // Update PHP session via URL param
    const url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    document.documentElement.lang = currentLang;
    // Update lang switcher UI
    const switcher = document.querySelector('.lang-switcher');
    if (switcher) {
        switcher.querySelectorAll('.lang-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.lang === currentLang);
        });
    }
});
