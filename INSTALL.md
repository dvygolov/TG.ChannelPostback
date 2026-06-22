# Инструкция по установке

## 1. Требования

- VPS/сервер с Ubuntu 20.04+ (или другой Linux)
- PHP 8.0 или выше
- Nginx или Apache
- Домен с HTTPS (для Telegram webhook)

## 2. Установка на сервер

### Шаг 1: Подготовка сервера

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка PHP и расширений
sudo apt install -y php8.0-fpm php8.0-cli php8.0-sqlite3 php8.0-curl php8.0-mbstring php8.0-xml

# Установка Nginx
sudo apt install -y nginx

```

### Шаг 2: Клонирование проекта

```bash
# Переходим в директорию веб-сервера
cd /var/www

# Клонируем проект
git clone <your-repo-url> tg-postback
cd tg-postback

# Устанавливаем права
sudo chown -R www-data:www-data /var/www/tg-postback
sudo chmod -R 755 /var/www/tg-postback
sudo chmod -R 775 logs/
```

### Шаг 3: Настройка окружения

```bash
# Копируем .env файл
cp .env.example .env

# Редактируем настройки
nano .env
```

Измените следующие параметры:
```
APP_URL=https://yourdomain.com
ADMIN_USERNAME=admin
ADMIN_PASSWORD=your_secure_password
```

### Шаг 4: Инициализация базы данных

```bash
php setup.php
```

### Шаг 5: Настройка Nginx

```bash
# Копируем конфиг
sudo cp nginx.conf.example /etc/nginx/sites-available/tg-postback

# Редактируем конфиг
sudo nano /etc/nginx/sites-available/tg-postback
```

Измените:
- `server_name` на ваш домен
- `root` на `/var/www/tg-postback/public`
- Пути к SSL сертификатам

```bash
# Активируем конфиг
sudo ln -s /etc/nginx/sites-available/tg-postback /etc/nginx/sites-enabled/

# Удаляем дефолтный конфиг (опционально)
sudo rm /etc/nginx/sites-enabled/default

# Проверяем конфиг
sudo nginx -t

# Перезапускаем Nginx
sudo systemctl restart nginx
```

### Шаг 6: Настройка SSL (Let's Encrypt)

```bash
# Установка Certbot
sudo apt install -y certbot python3-certbot-nginx

# Получение сертификата
sudo certbot --nginx -d yourdomain.com

# Авто-обновление сертификата настроится автоматически
```

## 3. Настройка Telegram ботов

### Шаг 1: Создание бота

1. Откройте Telegram и найдите [@BotFather](https://t.me/BotFather)
2. Отправьте команду `/newbot`
3. Следуйте инструкциям и получите токен
4. Сохраните токен в безопасном месте

### Шаг 2: Добавление бота в канал

1. Откройте ваш Telegram канал
2. Перейдите в настройки → Администраторы
3. Нажмите "Добавить администратора"
4. Найдите и добавьте вашего бота
5. Дайте права:
   - ✅ Invite users via link (обязательно!)
   - Остальные права по желанию

### Шаг 3: Добавление в систему

1. Откройте админ-панель: `https://yourdomain.com/`
2. Перейдите на вкладку "Боты"
3. Нажмите "Добавить бота"
4. Введите название и токен
5. Нажмите "Добавить"

Система автоматически:
- Проверит токен
- Зарегистрирует webhook

### Шаг 4: Добавление канала

1. На вкладке "Каналы" нажмите "Добавить канал"
2. Выберите бота из списка
3. Введите ID канала:
   - Публичный: `@yourchannel`
   - Приватный: используйте числовой ID (можно получить через [@userinfobot](https://t.me/userinfobot))
4. Введите Postback URL трекера:
   ```
   https://your-tracker.com/postback?subid={subid}&status={status}
   ```
5. Нажмите "Добавить"

Система автоматически:
- Проверит права бота
- Получит название канала
- Настроит webhook

## 4. Интеграция с лендингом

На вашем лендинге добавьте кнопку:

```html
<a href="https://yourdomain.com/subscribe.php?channel=@yourchannel&subid=<?php echo $subid; ?>" 
   class="btn btn-primary">
   Подписаться на канал
</a>
```

Где:
- `channel` - короткое имя канала (без @) или числовой ID
- `subid` - уникальный идентификатор из трекера (click_id, subid и т.д.)

## 5. Проверка работы

### Тест генерации инвайта:

```bash
curl "https://yourdomain.com/subscribe.php?channel=@yourchannel&subid=test123"
```

Должен вернуть redirect на Telegram инвайт-ссылку.

### Проверка webhook:

```bash
# Получить информацию о webhook
curl "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo"
```

Должно быть:
```json
{
  "ok": true,
  "result": {
    "url": "https://yourdomain.com/webhook.php?token=YOUR_BOT_TOKEN",
    "has_custom_certificate": false,
    "pending_update_count": 0
  }
}
```

### Проверка логов:

```bash
# Просмотр логов в реальном времени
tail -f /var/www/tg-postback/logs/app.log
tail -f /var/www/tg-postback/logs/webhook.log
tail -f /var/www/tg-postback/logs/postback.log
```

## 6. Настройка мониторинга (опционально)

### Logrotate для ротации логов:

```bash
sudo nano /etc/logrotate.d/tg-postback
```

Содержимое:
```
/var/www/tg-postback/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    missingok
    create 0644 www-data www-data
}
```

## Troubleshooting

### Ошибка: "Failed to create invite link"

**Причина:** Бот не имеет прав в канале.

**Решение:**
1. Убедитесь что бот добавлен администратором
2. Проверьте право "Invite users via link"
3. Проверьте ID канала (для приватных каналов)

### Ошибка: "Webhook not working"

**Причина:** HTTPS не настроен или недоступен.

**Решение:**
1. Проверьте SSL сертификат: `curl -I https://yourdomain.com`
2. Проверьте webhook: `/getWebhookInfo`
3. Убедитесь что порт 443 открыт

### Постбэки не отправляются

**Причина:** Неверный формат URL или трекер недоступен.

**Решение:**
1. Проверьте логи: `tail -f logs/postback.log`
2. Проверьте формат URL (должны быть плейсхолдеры `{subid}` и `{status}`)
3. Проверьте доступность трекера: `curl "https://tracker.com/postback?subid=test&status=lead"`

## Поддержка

При возникновении проблем:
1. Проверьте логи в `logs/`
2. Проверьте права доступа к файлам
3. Убедитесь что все требования выполнены
