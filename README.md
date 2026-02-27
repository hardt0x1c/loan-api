# Loan API

API для приёма и асинхронной обработки заявок на кредит.

## Требования

- Docker + Docker Compose

## Быстрый старт

1. Скопируйте переменные окружения:

```bash
cp .env.example .env
```

2. Задайте в `.env` значение `COOKIE_VALIDATION_KEY` (случайная строка).

3. Запустите сервис:

```bash
docker compose up -d --build
```

После запуска контейнер `php` автоматически:
- установит зависимости (если `vendor/` отсутствует),
- дождётся доступности PostgreSQL,
- выполнит миграции,
- поднимет `php-fpm`.

Сервис будет доступен по адресу `http://localhost`.

## Конфигурация

Используемые env-переменные:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `COOKIE_VALIDATION_KEY`

## API

### `GET /health`

Проверка доступности.

Пример ответа:

```json
{"ok":true}
```

### `POST /requests`

Создать заявку.

Тело запроса (`application/json`):

```json
{
  "user_id": 1,
  "amount": 10000,
  "term": 12
}
```

Успех (`201`):

```json
{"result":true,"id":1}
```

Ошибка валидации (`400`):

```json
{
  "result": false,
  "error": "invalid_payload",
  "details": {
    "term": ["Term must be no less than 1."]
  }
}
```

### `GET /processor?delay=<seconds>`

Обработать все заявки со статусом `pending`.

- `delay` обязателен к валидации и должен быть целым числом в диапазоне `0..300`.
- Задержка эмулирует «долгое решение» и выполняется **вне транзакции**.

Успех (`200`):

```json
{"result":true}
```

Ошибка параметра (`400`):

```json
{
  "result": false,
  "error": "invalid_delay",
  "details": {
    "delay": ["Delay must be no greater than 300."]
  }
}
```

## Принципы обработки `/processor`

- В короткой транзакции выбирается одна `pending`-заявка с `FOR UPDATE SKIP LOCKED` и помечается как `processing`.
- Транзакция завершается.
- Вне транзакции выполняется `sleep(delay)`.
- В новой транзакции заявка переводится в `approved` или `declined`.

Это исключает удержание блокировки строки на время искусственной задержки.
