# Database Query Folder

This folder stores SQL query definitions.

## Purpose

Each query file centralizes the SQL strings related to one domain area.

Examples:
- `userQuery.php`
- `questionQuery.php`
- `answerOptionQuery.php`
- `gameConfigQuery.php`

## Why this layer exists

Keeping SQL here gives:
- cleaner repositories
- easier updates
- better separation of concerns
- simpler debugging

## Typical query responsibilities

A query class usually returns SQL for:
- insert
- select by id or name
- update
- special lookup operations

## Query files in this folder

| Query file | Key queries |
|---|---|
| `userQuery.php` | select by uid, select by email, insert, update |
| `userPermissionQuery.php` | select by uid, insert, update |
| `userProgressStateQuery.php` | select by uid, insert, update |
| `questionQuery.php` | select all, select by id list (with `FIELD` ordering), insert, cursor-paginated select (`WHERE id > ? LIMIT ?`) |
| `answerOptionQuery.php` | select by question id list (IN clause), insert |
| `gameConfigQuery.php` | select by name, select by id, select active, select all, cursor-paginated select (`WHERE id > ? LIMIT ?`), insert, update by id, deactivate all, activate by id |
| `quizAttemptQuery.php` | legacy quiz attempt queries |
