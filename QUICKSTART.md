# Быстрый старт

## Минимальная установка (5 минут)

### 1. Скачай проект
```bash
git clone <repo-url>
cd YWB.TGChannelPostback
```

### 2. Настрой окружение
```bash
cp .env.example .env
nano .env
```

Измени:
```
APP_URL=https://yourdomain.com
```

### 3. Создай базу данных
```bash
php setup.php
```

### 4. Настрой веб-сервер

**Nginx:** Укажи `document root = /path/to/YWB.TGChannelPostback/public`

**Apache:** То же самое

### 5. Открой админ-панель

Перейди на `https://yourdomain.com/`

**Первый вход:**
- Логин: не требуется
- Пароль: тот, что указан в `.env` (`ADMIN_PASSWORD`)
- По умолчанию: `admin` (⚠️ измените обязательно!)

---

## Добавление первого бота

### 1. Создай бота в Telegram

1. Найди [@BotFather](https://t.me/BotFather)
2. Отправь `/newbot`
3. Скопируй токен

### 2. Добавь бота в канал

1. Открой свой Telegram канал
2. Зайди в Администраторы
3. Добавь бота
4. **Важно:** Дай право "Invite users via link"

### 3. Добавь в систему

1. Открой админ-панель
2. Вкладка "Боты" → "Добавить бота"
3. Введи название и токен

### 4. Добавь канал

1. Вкладка "Каналы" → "Добавить канал"
2. Выбери бота
3. Введи ID канала (`@mychannel`)
4. Укажи Postback URL:
   ```
   https://tracker.com/postback?subid={subid}&status={status}
   ```

---

## Использование на лендинге

Добавь кнопку:
```html
<a href="https://yourdomain.com/subscribe.php?channel=@mychannel&subid=<?= $click_id ?>">
    Подписаться на канал
</a>
```

---

## Проверка работы

### Генерация инвайта:
```bash
curl "https://yourdomain.com/subscribe.php?channel=@mychannel&subid=test123"
```

### Просмотр логов:
```bash
tail -f logs/app.log
tail -f logs/webhook.log
tail -f logs/postback.log
```

---

## Что происходит под капотом?

1. **Пользователь кликает** → `subscribe.php`
2. **Создаётся инвайт-линк** → сохраняется в БД с привязкой к `subid`
3. **Редирект** на Telegram
4. **Пользователь подписывается** → Telegram отправляет webhook
5. **Система находит** `subid` по инвайт-ссылке
6. **Отправляется постбэк** в трекер

---

## Требования

- ✅ PHP 8.0+
- ✅ Extensions: sqlite3, curl, json
- ✅ HTTPS домен (обязательно!)
- ✅ Веб-сервер (Nginx/Apache)

## Никаких зависимостей!

Проект работает на чистом PHP без Composer и внешних библиотек.

---

## Troubleshooting

**Проблема:** Бот не создаёт инвайты  
**Решение:** Проверь что бот админ канала и имеет право "Invite users via link"

**Проблема:** Webhook не работает  
**Решение:** Убедись что домен доступен по HTTPS

**Проблема:** Постбэки не отправляются  
**Решение:** Проверь формат URL и логи `logs/postback.log`

---

Подробная документация: [INSTALL.md](INSTALL.md)
