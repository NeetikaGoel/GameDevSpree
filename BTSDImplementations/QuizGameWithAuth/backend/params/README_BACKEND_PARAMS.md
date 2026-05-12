# Backend Params Folder

This folder contains lightweight request parameter wrapper classes.

## Purpose

These classes help represent request input in a structured way before it is passed deeper into backend logic.

## Why it exists

Instead of passing many loose values everywhere, params classes make selected APIs cleaner and more explicit.

## Current use

This folder currently includes parameter wrappers for quiz-related APIs:

| File | Purpose |
|---|---|
| `quizLoadParams.php` | Wraps `uid` for the quiz load API |
| `quizResultShowParams.php` | Wraps `uid` for the quiz result show API |
| `quizSubmitParams.php` | Wraps `uid` and `answerOptionId` for the quiz submit API |

The admin APIs (`questionSetCreate`, `questionSetEdit`, `questionShow`, `questionSetShow`) pass parameters directly rather than through params objects, as their inputs are simpler or handled inline in the boundary layer.

## Benefits

- clearer method signatures
- easier validation flow
- better readability
- easier future extension

## Typical contents of a params class

A params class usually contains:
- properties representing request fields
- constructor assignment
- getters for safe access

## Good use case

Params objects are especially useful when:
- request shape is stable
- request data has multiple fields
- APIs need better structure than passing raw arrays
