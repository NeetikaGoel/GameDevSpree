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

## Example domains handled here

- users
- permissions
- quiz progress
- questions
- answer options
- game configs
