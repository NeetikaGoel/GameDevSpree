# Backend Entity Folder

This folder contains the project’s core domain objects.

## Purpose

Entities represent meaningful application objects instead of raw database arrays.

## Main entities

- `User`
- `UserPermission`
- `UserProgressState`
- `Question`
- `AnswerOption`
- `GameConfig`
- `Quiz`
- `FeatureFlag`

## Why entities help

Entities make the code:
- more readable
- more object-oriented
- less error-prone than using raw arrays everywhere
- easier to reason about in services

## Example role of entities

### `User`
Represents a user account or guest identity.

### `UserPermission`
Represents authorization role like guest, user, or admin.

### `UserProgressState`
Represents a user’s current quiz state.

### `Question`
Represents a question record.

### `AnswerOption`
Represents a selectable answer tied to a question.

### `GameConfig`
Represents the quiz configuration currently used to drive question selection.

### `Quiz`
Acts like a helper domain object around question order and answer resolution.

## Mapping relationship

Database rows are converted into these entities through the mapper layer in `database/mapper/`.
