# Backend Params Folder

This folder contains lightweight request parameter wrapper classes.

## Purpose

These classes help represent request input in a structured way before it is passed deeper into backend logic.

## Why it exists

Instead of passing many loose values everywhere, params classes make selected APIs cleaner and more explicit.

## Current use

This folder currently includes parameter wrappers for quiz-related APIs, such as:
- `quizLoadParams.php`
- `quizResultShowParams.php`
- `quizSubmitParams.php`

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
