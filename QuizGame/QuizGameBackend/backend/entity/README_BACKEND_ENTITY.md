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

## Role of each entity

### `User`
Represents a user account or guest identity.
Key fields: `uid`, `userId`, `loginType`, `email`, `name`, `passwordHash`.

### `UserPermission`
Represents authorization role: `guest`, `user`, or `admin`.
Key fields: `uid`, `permissionGroup`.

### `UserProgressState`
Represents a user’s current quiz state.
Key fields: `uid`, `scoreCurrent`, `questionsDone`, `questionIdOrderJson`, `questionIdOrderIndexCurrent`, `questionIdCurrent`, `isComplete`.

### `Question`
Represents a question record.
Key fields: `id`, `text`, `type`.

### `AnswerOption`
Represents a selectable answer tied to a question.
Key fields: `id`, `text`, `type`, `questionId`, `isCorrect`.

### `GameConfig`
Represents a named question-set configuration.
Key fields: `id`, `gameConfigName`, `questionCountTarget`, `questionIdListAllowed` (PHP array), `secretKey`, `isActive`, `createdAt`, `updatedAt`.
Note: `secretKey` is internal and must never be exposed in API responses.

### `Quiz`
Acts as a helper domain object around question order and answer resolution.
Used by quiz load and submit services.

### `FeatureFlag`
Represents a runtime feature flag loaded from `featureFlag.json`.
Currently used for: `questionOrderRandomEnabled` — controls whether question order is randomized at quiz start.

## Mapping relationship

Database rows are converted into these entities through the mapper layer in `database/mapper/`.
