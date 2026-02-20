# Self-test Checklist

## Current Status In This Environment

- [x] `docker compose up -d --build` starts containers without manual actions.
- [x] `localhost:80` responds correctly (`GET /health` -> `200`).
- [x] `POST /requests` returns `201` and `id`.
- [x] `POST /requests` returns `400` when `user_id` already has an `approved` request.
- [x] `GET /processor` processes `pending` and sets `approved/declined`.
- [x] Parallel `/processor` calls do not produce double `approved` for the same `user_id`.

## Quick Self-test (After Aligning PHP Version)

```bash
set -euo pipefail

# 1) Start services

docker compose up -d --build

# 2) Install dependencies and run migrations

docker compose exec -T php composer install --no-interaction --prefer-dist
docker compose exec -T php php yii migrate/up --interactive=0

# 3) localhost:80 responds (check health endpoint)

code=$(curl -sS -o /tmp/health.body -w '%{http_code}' http://localhost/health)
[ "$code" = "200" ]
grep -q '"ok":true' /tmp/health.body

# 4) POST /requests -> 201 + id

curl -sS -D /tmp/r1.headers -o /tmp/r1.body \
  -X POST http://localhost/requests \
  -H 'Content-Type: application/json' \
  -d '{"user_id":10001,"amount":10000,"term":12}'

grep -q ' 201 ' /tmp/r1.headers
grep -Eq '"id":[0-9]+' /tmp/r1.body

# 5) POST /requests -> 400 if user_id already has approved
# Make this deterministic via SQL.

docker compose exec -T postgres psql -U user -d loans -c "
INSERT INTO loan_requests (user_id, amount, term, status, processed_at)
VALUES (10002, 10000, 12, 'approved', NOW())
ON CONFLICT DO NOTHING;
"

curl -sS -D /tmp/r2.headers -o /tmp/r2.body \
  -X POST http://localhost/requests \
  -H 'Content-Type: application/json' \
  -d '{"user_id":10002,"amount":20000,"term":6}'

grep -q ' 400 ' /tmp/r2.headers
grep -q '"result":false' /tmp/r2.body

# 6) GET /processor processes pending -> approved/declined

for u in 10010 10011 10012; do
  curl -sS -X POST http://localhost/requests \
    -H 'Content-Type: application/json' \
    -d "{\"user_id\":$u,\"amount\":5000,\"term\":6}" >/dev/null
done

curl -sS 'http://localhost/processor?delay=0' >/dev/null

# Check: no pending remains for these user_ids

docker compose exec -T postgres psql -U user -d loans -tAc "
SELECT COUNT(*)
FROM loan_requests
WHERE user_id IN (10010,10011,10012) AND status='pending';
" | grep -qx '0'

# 7) Parallel /processor calls do not produce double approved for one user_id
# Create multiple pending rows for one user_id.

docker compose exec -T postgres psql -U user -d loans -c "
INSERT INTO loan_requests (user_id, amount, term, status)
SELECT 10099, 7000, 9, 'pending'
FROM generate_series(1, 10);
"

# Call processor in parallel

seq 1 8 | xargs -I{} -n1 -P8 curl -sS 'http://localhost/processor?delay=2' >/dev/null

# Check: approved for user_id=10099 is at most one

docker compose exec -T postgres psql -U user -d loans -tAc "
SELECT COUNT(*)
FROM loan_requests
WHERE user_id=10099 AND status='approved';
" | awk '{exit !($1 <= 1)}'

# Additional check: all requests for this user_id are processed

docker compose exec -T postgres psql -U user -d loans -tAc "
SELECT COUNT(*)
FROM loan_requests
WHERE user_id=10099 AND status='pending';
" | grep -qx '0'

echo 'Self-test passed'
```
