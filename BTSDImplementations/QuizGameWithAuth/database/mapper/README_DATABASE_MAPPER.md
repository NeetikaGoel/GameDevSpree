# Database Mapper Folder

This folder contains mapper classes that convert raw database rows into entities.

## Purpose

Mappers transform associative arrays returned by MySQL into structured PHP objects.

## Why this matters

Without mappers, services and repositories would keep handling raw DB arrays.

With mappers:
- entities stay consistent
- domain objects are created in one place
- code becomes easier to read and maintain

## Mapper files in this folder

| Mapper | Entity built | Key DB columns mapped |
|---|---|---|
| `UserMapper` | `User` | `uid`, `user_id`, `login_type`, `email`, `name`, `password_hash`, `created_at`, `updated_at` |
| `UserPermissionMapper` | `UserPermission` | `id`, `uid`, `permission_group`, `created_at`, `updated_at` |
| `UserProgressStateMapper` | `UserProgressState` | `id`, `uid`, `score_current`, `questions_done`, `question_id_order_json`, `question_id_order_index_current`, `question_id_current`, `is_complete`, `created_at`, `updated_at` |
| `QuestionMapper` | `Question` | `id`, `text`, `type` |
| `AnswerOptionMapper` | `AnswerOption` | `id`, `text`, `type`, `question_id`, `is_correct` |
| `GameConfigMapper` | `GameConfig` | `id`, `game_config_name`, `question_count_target`, `question_id_list_allowed_json` (JSON-decoded to array), `secret_key`, `is_active`, `created_at`, `updated_at` |
| `QuizAttemptMapper` | `QuizAttempt` | legacy fields |

## Typical mapper methods

- map one row to one entity
- map many rows to many entities

## Important note

Mappers should stay lightweight.  
They should convert structure, not perform business decisions.
