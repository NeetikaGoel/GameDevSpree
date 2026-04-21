# Database Folder

This folder contains database structure, seed data, and the data-access support layers that help the backend work with MySQL.

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

### `ormManager.php`
Acts as the bridge between DB execution and mapper-driven object conversion.

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

## Core tables

- `users`
- `user_permissions`
- `user_progress_states`
- `questions`
- `answer_options`
- `game_configs`
- `quiz_attempts`

## Design intention

This folder is structured to keep SQL, execution, mapping, and business access cleanly separated.
