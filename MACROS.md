# Макросы для Postback URL

При настройке постбэк URL для канала вы можете использовать специальные макросы, которые будут автоматически заменяться на реальные данные при отправке постбэка.

## Доступные макросы

### Основные

| Макрос | Описание | Пример значения |
|--------|----------|-----------------|
| `{clickid}` | Уникальный Click ID из трекера | `abc123xyz` |
| `{status}` | Статус конверсии | `lead`, `sale` |

### Данные пользователя Telegram

| Макрос | Описание | Пример значения |
|--------|----------|-----------------|
| `{user_id}` | Telegram ID пользователя | `123456789` |
| `{first_name}` | Имя пользователя | `Иван` |
| `{last_name}` | Фамилия пользователя | `Петров` |
| `{username}` | Username (без @) | `ivanpetrov` |
| `{is_premium}` | Премиум статус Telegram | `true` или `false` |
| `{language_code}` | Код языка интерфейса | `ru`, `en`, `uk` и т.д. |

## Примеры использования

### 1. Базовый постбэк (только clickid и статус)

```
https://tracker.com/postback?click_id={clickid}&status={status}
```

**Результат:**
```
https://tracker.com/postback?click_id=abc123xyz&status=lead
```

---

### 2. С именем и фамилией пользователя

```
https://tracker.com/postback?clickid={clickid}&status={status}&fname={first_name}&lname={last_name}
```

**Результат:**
```
https://tracker.com/postback?clickid=abc123xyz&status=lead&fname=%D0%98%D0%B2%D0%B0%D0%BD&lname=%D0%9F%D0%B5%D1%82%D1%80%D0%BE%D0%B2
```

---

### 3. С премиум статусом (для сегментации)

```
https://tracker.com/postback?clickid={clickid}&status={status}&premium={is_premium}
```

**Результат:**
```
https://tracker.com/postback?clickid=abc123xyz&status=lead&premium=true
```

---

### 4. Полный набор данных

```
https://tracker.com/postback?clickid={clickid}&status={status}&uid={user_id}&fname={first_name}&lname={last_name}&username={username}&premium={is_premium}&lang={language_code}
```

**Результат:**
```
https://tracker.com/postback?clickid=abc123xyz&status=lead&uid=123456789&fname=%D0%98%D0%B2%D0%B0%D0%BD&lname=%D0%9F%D0%B5%D1%82%D1%80%D0%BE%D0%B2&username=ivanpetrov&premium=true&lang=ru
```

---

### 5. Для Keitaro

```
https://your-tracker.com/postback?campaign_id=123&sub_id={clickid}&status={status}
```

---

### 6. Для ClickFlare

```
https://your-tracker.com/postback?click_id={clickid}&payout=1.00&status={status}&name={first_name}%20{last_name}
```

---

## Важные замечания

### 1. URL-кодирование
Все значения автоматически кодируются в URL-безопасный формат. Не нужно делать это вручную.

Например, имя "Иван" станет `%D0%98%D0%B2%D0%B0%D0%BD`.

### 2. Пустые значения
Если данные недоступны (например, пользователь не указал фамилию), макрос заменяется на пустую строку.

```
URL: https://tracker.com/postback?fname={first_name}&lname={last_name}
Если last_name отсутствует: https://tracker.com/postback?fname=Ivan&lname=
```

### 3. Username
Не все пользователи Telegram имеют username. Проверяйте наличие значения на стороне трекера.

### 4. Premium статус
Доступен только если у пользователя есть Telegram Premium. Иначе возвращает `false`.

### 5. Language code
Определяется настройками языка в приложении Telegram пользователя. Стандартные коды: `ru`, `en`, `uk`, `de`, `fr`, `es` и т.д.

---

## Зачем нужны эти данные?

### 🎯 Сегментация аудитории
- **Премиум пользователи**: Часто более платежеспособные
- **Язык**: Для таргетинга по странам
- **Имя/Фамилия**: Для персонализации в CRM

### 📊 Аналитика
- Отслеживание качества трафика
- A/B тестирование по разным сегментам
- ROI анализ по типам пользователей

### 🔄 Интеграция с CRM
- Автоматическое создание лидов с полными данными
- Персонализация коммуникации
- Сегментированные email/push рассылки

---

## Тестирование

Используйте [webhook.site](https://webhook.site) для тестирования постбэков:

1. Создайте уникальный URL на webhook.site
2. Добавьте его как Postback URL с макросами
3. Подпишитесь на канал
4. Проверьте полученные параметры на webhook.site

**Пример тестового URL:**
```
https://webhook.site/your-unique-id?clickid={clickid}&status={status}&fname={first_name}&lname={last_name}&uid={user_id}&premium={is_premium}&lang={language_code}
```

---

## FAQ

**Q: Можно ли использовать макросы в пути URL, а не только в query параметрах?**  
A: Да! Макросы работают в любой части URL:
```
https://tracker.com/{clickid}/postback?status={status}
```

**Q: Регистр имеет значение?**  
A: Да, макросы чувствительны к регистру. Используйте точно как указано: `{first_name}`, а не `{First_Name}`.

**Q: Что если трекер не поддерживает какие-то параметры?**  
A: Просто не используйте ненужные макросы. Базовый вариант `{clickid}` и `{status}` работает везде.

**Q: Можно ли добавить свои макросы?**  
A: Текущий набор покрывает все данные, доступные от Telegram API. Для дополнительных параметров используйте фиксированные значения в URL.

---

## Поддержка трекеров

### ✅ Протестировано с:
- Keitaro
- Binom
- ClickFlare
- RedTrack
- Voluum
- Custom trackers

### 📝 Универсальный формат:
```
https://your-tracker.com/postback?click_id={clickid}&status={status}
```

Работает с любым трекером, поддерживающим GET-запросы для постбэков.
