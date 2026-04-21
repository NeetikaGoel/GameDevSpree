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

## Example mapper roles

- `UserMapper` -> builds `User`
- `QuestionMapper` -> builds `Question`
- `AnswerOptionMapper` -> builds `AnswerOption`
- `GameConfigMapper` -> builds `GameConfig`

## Typical mapper methods

- map one row to one entity
- map many rows to many entities

## Important note

Mappers should stay lightweight.  
They should convert structure, not perform business decisions.
