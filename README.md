```
                          TG.ChannelPostback
    _            __     __  _ _             __          __  _
   | |           \ \   / / | | |            \ \        / / | |
   | |__  _   _   \ \_/ /__| | | _____      _\ \  /\  / /__| |__
   | '_ \| | | |   \   / _ \ | |/ _ \ \ /\ / /\ \/  \/ / _ \ '_ \
   | |_) | |_| |    | |  __/ | | (_) \ V  V /  \  /\  /  __/ |_) |
   |_.__/ \__, |    |_|\___|_|_|\___/ \_/\_/    \/  \/ \___|_.__/
           __/ |
          |___/             https://yellowweb.top

If you like this script, PLEASE DONATE!
```

[Support this project](https://yellowweb.top/donate)


# Telegram Channel Postback System

Система для отслеживания подписчиков Telegram каналов и отправки постбэков в трекеры (Keitaro и др.).

## Возможности

- ✅ Генерация уникальных одноразовых инвайт-ссылок
- ✅ Отслеживание подписок через Telegram webhook
- ✅ Отправка постбэков в трекер (Keitaro, Binom, и др.)
- ✅ Макросы в постбэк: имя, фамилия, username, premium, язык
- ✅ Поддержка нескольких ботов и каналов
- ✅ Веб-панель администратора с авторизацией
- ✅ Защита от брутфорса (rate limiting)
- ✅ SQLite база данных
- ✅ Логирование всех событий

## Требования

- PHP >= 8.0
- PHP extensions: sqlite3, curl, json
- HTTPS домен (для Telegram webhook)
- Веб-сервер (Apache/Nginx)

## Установка

1. Клонируйте репозиторий:
```bash
git clone <repo-url>
cd YWB.TGChannelPostback
```

2. Настройте окружение:
```bash
cp .env.example .env
nano .env
```

3. Создайте базу данных:
```bash
php setup.php
```

4. Запустите сервер:

**Вариант A: Встроенный PHP сервер (для разработки)**
```bash
php -S localhost:8080 -t public/
```
Откройте: http://localhost:8080

**Вариант B: VS Code (если используете)**
- Нажмите `F5` или Run → "Launch Admin Panel"
- Или используйте Task: Terminal → Run Task → "Start PHP Server"

**Вариант C: Production (Nginx/Apache)**
Настройте веб-сервер (document root = `public/`)

## Структура проекта

```
├── public/              # Document root
│   ├── index.php       # Админ-панель
│   ├── subscribe.php   # Генерация инвайтов
│   └── webhook.php     # Telegram webhook
├── app/
│   ├── Models/         # Модели данных
│   ├── Services/       # Бизнес-логика
│   └── Views/          # HTML шаблоны
├── logs/               # Логи
└── database.sqlite     # БД
```

## VS Code Integration

Проект включает полную интеграцию с VS Code:

- 🚀 **Запуск одной кнопкой** - нажмите `F5`
- 📋 **Готовые Tasks** - запуск сервера, просмотр логов
- 🔧 **Настроенный редактор** - правильные отступы, валидация
- 📦 **Рекомендуемые расширения** - PHP Intelephense и др.

Подробнее: [.vscode/README.md](.vscode/README.md)

## Использование

### 1. Добавление бота

Откройте админ-панель: `https://yourdomain.com/`

1. Создайте бота через [@BotFather](https://t.me/BotFather)
2. Добавьте бота администратором канала с правом создания инвайт-ссылок
3. В админке нажмите "Добавить бота" и введите токен

### 2. Добавление канала

1. В админке выберите бота
2. Нажмите "Добавить канал"
3. Введите:
   - ID канала (например: `@mychannel` или `-1001234567890`)
   - Postback URL (например: `https://tracker.com/postback?clickid={clickid}&status={status}`)
   
**📋 Доступные макросы для Postback URL:**
- `{clickid}` - Click ID из трекера
- `{status}` - Статус (lead/sale)
- `{user_id}` - Telegram ID
- `{first_name}` - Имя
- `{last_name}` - Фамилия
- `{username}` - Username
- `{is_premium}` - Премиум статус (true/false)
- `{language_code}` - Язык (ru, en...)

Подробнее: [MACROS.md](MACROS.md)

### 3. Интеграция с лендингом

На лендинге создайте кнопку со ссылкой:
```html
<a href="https://yourdomain.com/subscribe.php?channel=mychannel&clickid={clickid}">
  Подписаться на канал
</a>
```

Где:
- `channel` - короткое имя канала (без @) или ID
- `clickid` - уникальный Click ID из трекера

### 4. Флоу работы

1. Пользователь кликает на ссылку
2. `subscribe.php` создает уникальный инвайт-линк через Telegram Bot API
3. Пользователь редиректится на инвайт-линк
4. При подписке Telegram отправляет webhook
5. Система находит clickid по инвайт-ссылке и отправляет постбэк в трекер

## API эндпоинты

### Генерация инвайта
```
GET /subscribe.php?channel={channel}&clickid={clickid}
```

**Ответ:** HTTP redirect на Telegram инвайт-ссылку

### Webhook (для Telegram)
```
POST /webhook.php?token={bot_token}
```

**Body:** Telegram Update JSON

## Логирование

Все события логируются в директории `logs/`:
- `app.log` - общие события
- `webhook.log` - входящие webhook'и
- `postback.log` - отправленные постбэки
- `errors.log` - ошибки

## Безопасность

### Авторизация
- 🔐 Доступ к админ-панели защищен паролем
- 🛡️ Rate limiting против брутфорса:
  - 3 попытки → таймаут 5 минут
  - 5 попыток → таймаут 15 минут  
  - 7 попыток → таймаут 1 час
- 🔑 Сессия действует 24 часа

### Рекомендации
- ⚠️ Обязательно измените `ADMIN_PASSWORD` в `.env`
- ⚠️ Используйте сложный пароль (минимум 12 символов)
- ⚠️ Настройте HTTPS (Telegram требует для webhook)
- ⚠️ Регулярно обновляйте PHP и зависимости

## Troubleshooting

### Бот не создает инвайт-ссылки
- Проверьте что бот добавлен админом канала
- Проверьте что у бота есть право "Invite users via link"

### Webhook не работает
- Проверьте что домен доступен по HTTPS
- Проверьте логи: `tail -f logs/webhook.log`
- Убедитесь что webhook зарегистрирован: `https://api.telegram.org/bot<TOKEN>/getWebhookInfo`

### Постбэки не отправляются
- Проверьте логи: `tail -f logs/postback.log`
- Проверьте формат postback URL (должны быть макросы `{clickid}` и `{status}`)

## Лицензия

MIT
