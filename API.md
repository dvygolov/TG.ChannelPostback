# API Documentation

## Публичные эндпоинты

### 1. Генерация инвайт-ссылки

**Endpoint:** `GET /subscribe.php`

**Параметры:**
- `channel` (required) - ID канала (@mychannel или -1001234567890)
- `subid` (required) - Уникальный идентификатор из трекера

**Пример:**
```
GET https://yourdomain.com/subscribe.php?channel=@mychannel&subid=abc123xyz
```

**Ответ:**
- HTTP 302 Redirect на Telegram инвайт-ссылку
- Или HTTP 400/404/500 с JSON ошибкой

**Использование на лендинге:**
```html
<a href="https://yourdomain.com/subscribe.php?channel=<?= $channel ?>&subid=<?= $subid ?>">
    Подписаться на канал
</a>
```

---

### 2. Webhook от Telegram

**Endpoint:** `POST /webhook.php`

**Параметры:**
- `token` (query, required) - Токен бота

**Body:** Telegram Update JSON

**Пример URL:**
```
POST https://yourdomain.com/webhook.php?token=123456789:ABCdefGHI...
```

**Примечание:** Этот эндпоинт вызывается автоматически Telegram при событиях в канале.

---

## API для админ-панели

Все запросы к `api.php` с параметром `action`.

### Боты

#### Получить список ботов
```
GET /api.php?action=get_bots
```

**Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "My Bot",
      "token": "123456789:ABC...",
      "created_at": "2024-01-01 12:00:00",
      "is_active": true
    }
  ]
}
```

#### Добавить бота
```
POST /api.php?action=add_bot
Content-Type: application/x-www-form-urlencoded

name=My%20Bot&token=123456789:ABC...
```

**Ответ:**
```json
{
  "success": true,
  "message": "Bot added successfully",
  "data": { /* bot object */ }
}
```

#### Удалить бота
```
POST /api.php?action=delete_bot
Content-Type: application/x-www-form-urlencoded

id=1
```

#### Переключить статус бота
```
POST /api.php?action=toggle_bot
Content-Type: application/x-www-form-urlencoded

id=1
```

---

### Каналы

#### Получить список каналов
```
GET /api.php?action=get_channels
GET /api.php?action=get_channels&bot_id=1  # для конкретного бота
```

**Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "bot_id": 1,
      "channel_id": "@mychannel",
      "channel_name": "My Channel",
      "postback_url": "https://tracker.com/postback?subid={subid}&status={status}",
      "is_active": true,
      "created_at": "2024-01-01 12:00:00"
    }
  ]
}
```

#### Добавить канал
```
POST /api.php?action=add_channel
Content-Type: application/x-www-form-urlencoded

bot_id=1&channel_id=@mychannel&postback_url=https://...&channel_name=My%20Channel
```

**Примечание:** Система автоматически:
- Проверит права бота в канале
- Получит название канала (если не указано)
- Установит webhook для бота

**Ответ:**
```json
{
  "success": true,
  "message": "Channel added successfully",
  "data": { /* channel object */ }
}
```

#### Удалить канал
```
POST /api.php?action=delete_channel
Content-Type: application/x-www-form-urlencoded

id=1
```

#### Переключить статус канала
```
POST /api.php?action=toggle_channel
Content-Type: application/x-www-form-urlencoded

id=1
```

---

### Инвайты

#### Получить инвайты для канала
```
GET /api.php?action=get_invites&channel_id=1&limit=100
```

**Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "channel_id": 1,
      "subid": "abc123xyz",
      "invite_link": "https://t.me/+UniqueHash",
      "status": "subscribed",
      "user_telegram_id": 987654321,
      "created_at": "2024-01-01 12:00:00",
      "subscribed_at": "2024-01-01 12:05:00"
    }
  ]
}
```

---

### Статистика

#### Получить общую статистику
```
GET /api.php?action=get_stats
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "total": 1000,
    "pending": 250,
    "subscribed": 750
  }
}
```

---

## Формат ошибок

Все ошибки возвращаются в формате:

```json
{
  "error": "Error message here"
}
```

HTTP коды:
- `200` - Success
- `400` - Bad Request (неверные параметры)
- `404` - Not Found (бот/канал не найден)
- `405` - Method Not Allowed (неверный HTTP метод)
- `500` - Internal Server Error

---

## Postback URL

Формат postback URL должен содержать плейсхолдеры:

```
https://tracker.com/postback?subid={subid}&status={status}
```

**Плейсхолдеры:**
- `{subid}` - заменяется на реальный subid
- `{status}` - заменяется на статус (обычно `lead`)

**Пример результата:**
```
https://tracker.com/postback?subid=abc123xyz&status=lead
```

Система отправит GET запрос на этот URL при успешной подписке пользователя.

---

## Webhook от Telegram (детали)

Telegram отправляет `chat_member` update при изменении статуса участника:

```json
{
  "update_id": 123456789,
  "chat_member": {
    "chat": {
      "id": -1001234567890,
      "title": "My Channel",
      "type": "channel"
    },
    "from": {
      "id": 987654321,
      "first_name": "User"
    },
    "date": 1234567890,
    "old_chat_member": {
      "user": {
        "id": 987654321,
        "first_name": "User"
      },
      "status": "left"
    },
    "new_chat_member": {
      "user": {
        "id": 987654321,
        "first_name": "User"
      },
      "status": "member"
    },
    "invite_link": {
      "invite_link": "https://t.me/+UniqueHash",
      "creator": {
        "id": 123456789,
        "is_bot": true
      },
      "creates_join_request": false,
      "is_primary": false,
      "is_revoked": false,
      "member_limit": 1
    }
  }
}
```

Система обрабатывает:
- Переход статуса в `member` = подписка
- Находит invite по ссылке
- Обновляет статус в БД
- Отправляет postback

---

## Безопасность

1. **Webhook URL** защищен токеном бота в query параметре
2. **Валидация** всех входных данных
3. **Escape** всех выводимых данных (XSS protection)
4. **SQL Injection** защита через prepared statements
5. **HTTPS** обязателен для webhook

Рекомендуется:
- Использовать HTTP Basic Auth для админ-панели
- Ограничить доступ по IP
- Регулярно обновлять PHP
