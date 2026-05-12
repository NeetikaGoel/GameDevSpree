# Database Folder

This folder contains database structure, seed data, and the data-access support layers that help the backend work with MySQL.

---

## Main contents

- `schema.sql`
- `data.sql`
- `updatedb.sql`
- `dbConnect.php`
- `dbManager.php`
- `ormManager.php`
- `query/`
- `repository/`
- `mapper/`

---

## Purpose of each file

### `schema.sql`
Defines database tables and structure.

### `data.sql`
Seeds default data such as:
- initial questions
- initial answer options
- default game config
- admin user
- guest user baseline

### `updatedb.sql`
Used for controlled DB updates when existing schema/data needs to evolve.

### `dbConnect.php`
Creates the MySQL connection.

### `dbManager.php`
Executes prepared queries and returns raw rows.

Methods:
- `getAllRows`
- `getAllRowsPrepared`
- `getOneRowPrepared`
- `runQuery`
- `insertAttemptIdRow`
- `explainQuery`

`explainQuery` wraps a query with `EXPLAIN` for DB query analysis.

### `ormManager.php`
Acts as the bridge between DB execution and mapper-driven object conversion.

Methods:
- `ormManage`
- `ormManageForOneRow`
- `runQuery`
- `insertQuery`
- `ormManageWithParams`
- `explainQuery`

---

## Data-access structure

```text
Repository
-> Query
-> OrmManager
-> DBManager
-> MySQL
-> Mapper
-> Entity
```

---

## Core tables

| Table | Description |
|---|---|
| `users` | Stores guest and registered user identity rows |
| `user_permissions` | Stores role per user: `guest`, `user`, or `admin` |
| `user_progress_states` | Stores live quiz state per user per game config |
| `questions` | Master list of all quiz questions |
| `answer_options` | Answer choices linked to questions; each question has exactly one correct option |
| `game_configs` | Named question-set configurations; multiple configs can be `is_active = TRUE` at the same time |
| `quiz_attempts` | Older stateless experiment support; not used in the current primary flow |

---

## Key column type notes

- Primary keys are signed `INT` (32-bit, max 2,147,483,647) with `AUTO_INCREMENT`.
- Timestamp columns in `users`, `user_permissions`, `user_progress_states` use `DATETIME(3)` (millisecond precision).
- Timestamp columns in `game_configs` use plain `DATETIME` (second precision).
- JSON data (`question_id_order_json`, `question_id_list_allowed_json`) uses `TEXT` type to support larger payloads.
- `password_hash` is `VARCHAR(255)` to hold bcrypt output from PHP `password_hash()`.
- `game_config_name` has a DB-level `UNIQUE` constraint and `VARCHAR(100)` limit. Application layer also enforces character restrictions.
- `user_progress_states` now uses one row per `(uid, game_config_id)` enforced by a unique constraint.

---

## Design intention

This folder is structured to keep SQL, execution, mapping, and business access cleanly separated.