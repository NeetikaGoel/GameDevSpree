# QuizGameWithAuth

A layered quiz application built with **PHP, MySQL, HTML, CSS, TypeScript, and JavaScript**.

This project supports:
- guest login
- registered login
- guest-to-user upgrade
- quiz play with persisted progress
- result calculation
- admin-only question creation
- evolving question-set management through `game_configs`

## Project overview

QuizGameWithAuth is designed around a clear layered architecture:

- **Frontend** handles UI rendering, user events, cookie-based identity display, and API calls.
- **Backend API boundary layer** receives requests, performs checks, delegates work, and returns JSON.
- **Service layer** contains business logic.
- **Repository / Query / Mapper / ORM layers** manage database access cleanly.
- **Database layer** stores users, permissions, quiz progress, questions, answer options, and game configs.

## Folder guide

- [Backend overview](./backend/README_BACKEND.md)
- [Backend API layer](./backend/api/README_BACKEND_API.md)
- [Backend service layer](./backend/service/README_BACKEND_SERVICE.md)
- [Backend params layer](./backend/params/README_BACKEND_PARAMS.md)
- [Backend entity layer](./backend/entity/README_BACKEND_ENTITY.md)
- [Database overview](./database/README_DATABASE.md)
- [Database query layer](./database/query/README_DATABASE_QUERY.md)
- [Database repository layer](./database/repository/README_DATABASE_REPOSITORY.md)
- [Database mapper layer](./database/mapper/README_DATABASE_MAPPER.md)
- [Frontend overview](./frontend/README_FRONTEND.md)
- [Logs folder](./logs/README_LOGS.md)

## Main flow

1. User opens a frontend page.
2. Frontend JS/TS calls a backend API with `fetch`.
3. API boundary performs:
   - authentication
   - authorization
   - validation
   - placeholder rate-limit and idempotency checks
4. API delegates to a service class.
5. Service calls repositories.
6. Repositories use query + mapper + ORM layers.
7. Database returns data.
8. Service shapes a response.
9. API returns JSON.
10. Frontend updates UI, cookies, or redirects.

## Core features

### Authentication
- Guest login creates a temporary guest identity.
- Registered login validates email + password hash.
- Registration can create a new registered user or upgrade a guest user.

### Quiz engine
- Quiz state is stored in `user_progress_states`.
- Quiz question selection is controlled by `game_configs`.
- Active config decides which questions are currently used.
- Result summary is computed from stored progress.

### Admin controls
- Add question with answer options
- Planned / evolving:
  - question browsing with pagination
  - question-set browsing
  - question-set creation
  - question-set editing
  - active config switching

## Key tables

- `users`
- `user_permissions`
- `user_progress_states`
- `questions`
- `answer_options`
- `game_configs`

## API versioning note

Documentation and future organization assume:

```text
/backend/api/v1/<apiName>.php
```

