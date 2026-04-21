# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projects
- **AuthenticationSystemRateLimiter** — PHP login + rate limiter + Node.js async feedback
- **GameInventorySystem** — TS + PHP + MySQL cart/orders
- **BTSDImplementations** — 8 quiz iterations (in-memory → featureFlag → MySQL → ORM → auth → stateless → config in DB); reference impl: `StatelessConfigQuizGameWithAuth`
- **DSATopics** — Session Priority Dashboard, URL Shortener
- **PHPBasics** — basic PHP

## Run Commands
```bash
# GameInventorySystem
npx tsc && php -S localhost:8004
mysql -u root -p < sql/schema.sql

# AuthenticationSystemRateLimiter
cd node && npm start          # port 3000
php -S localhost:8000         # http://localhost:8000/php/index.php  creds: test@gmail.com / 1234

# BTSDImplementations/<project>
mysql -u root -p quizGame < database/schema.sql && mysql -u root -p quizGame < database/data.sql
tsc quiz.ts --target ES2020 --module ES2020
php -S 127.0.0.1:8001         # http://127.0.0.1:8001/frontend/index.html

# curl test quiz API
curl -c cookie.txt http://127.0.0.1:8001/backend/api/quizLoad.php
curl -b cookie.txt -c cookie.txt -X POST -d "questionId=1&answerOptionId=2" http://127.0.0.1:8001/backend/api/quizSubmit.php
curl -b cookie.txt -c cookie.txt http://127.0.0.1:8001/backend/api/quizResultShow.php
```

## BSTD Architecture (BTSDImplementations)
```
backend/api/       9-step HTTP pipeline per endpoint
backend/params/    typed param objects per endpoint
backend/service/   business logic
backend/entity/    domain objects
backend/config.php HTTP status constants + defaults
database/dbConnect.php  mysqli (localhost, db=quizGame, user=root)
database/dbManager.php  prepared statement runner
database/ormManager.php service↔repository bridge
database/mapper/   SQL rows → entities
database/query/    parameterized SQL builders
database/repository/ CRUD per entity
frontend/          TS modules, fetch API
```

## API Pipeline (every backend/api/*.php)
Authenticate → Authorize → Validate/Sanitize → RateLimit → Idempotency → Delegate(service) → Respond → AuditLog → try/catch(InvalidArgumentException, RuntimeException, Throwable)

## Conventions
- `declare(strict_types=1)` in all PHP
- HTTP codes via constants: `HTTP_STATUS_BAD_REQUEST` etc. from `config.php`
- mysqli prepared statements only, no string interpolation in queries
- `featureFlag.json` for runtime toggles; GameConfig in DB drives question count/allowed IDs
- Auth sessions: `$_SESSION['useremail']`, `$_SESSION['count']`, `$_SESSION['feedback']`
