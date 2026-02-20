# Loan API

## Run project

```bash
docker compose up -d --build
```

Install PHP dependencies:

```bash
docker compose exec php composer install --no-interaction --prefer-dist
```

Run migrations:

```bash
docker compose exec php php yii migrate/up --interactive=0
```

## Service URL

`http://localhost` (port `80`)

## Database settings

- host: `localhost`
- port: `5432`
- db: `loans`
- user: `user`
- password: `password`

## cURL examples

### POST /requests (success)

```bash
curl -i -X POST http://localhost/requests \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"amount":10000,"term":12}'
```

Expected response:

```json
{"result":true,"id":1}
```

### POST /requests (error)

```bash
curl -i -X POST http://localhost/requests \
  -H "Content-Type: application/json" \
  -d '{"user_id":0,"amount":10000,"term":12}'
```

Expected response:

```json
{"result":false}
```

### GET /processor?delay=5

```bash
curl -i "http://localhost/processor?delay=5"
```

Expected response:

```json
{"result":true}
```

## Time spent

Approximately `6 hours`.
