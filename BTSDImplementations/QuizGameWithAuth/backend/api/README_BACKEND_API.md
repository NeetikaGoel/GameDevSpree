# Backend API Folder

This folder contains the API boundary files of the project.

## Purpose

Each file in this folder acts as a request boundary between the frontend and backend business logic.

These files should remain thin and structured.

## What an API file does

A typical API file in this project is responsible for:

1. setting response type to JSON
2. reading request input
3. authenticating user if needed
4. authorizing role if needed
5. validating and sanitizing request fields
6. applying placeholder rate-limit and idempotency checks
7. delegating to the correct service
8. returning JSON response
9. writing audit and error logs

## APIs in this folder

All APIs are under `v1/`:

- `v1/loginGuest.php`
- `v1/loginUser.php`
- `v1/registerUser.php`
- `v1/quizLoad.php`
- `v1/quizSubmit.php`
- `v1/quizResultShow.php`
- `v1/questionAdd.php`
- `v1/questionSetCreate.php`
- `v1/questionSetEdit.php`
- `v1/questionShow.php`
- `v1/questionSetShow.php`

All APIs live under the versioned path:

```text
/backend/api/v1/
```

## Design guideline

API files should **not** contain core business logic.

They should mainly coordinate:
- input reading
- boundary checks
- service delegation
- response output

## Typical API skeleton

```text
authenticate
-> authorize
-> validate
-> rate limit placeholder
-> idempotency placeholder
-> delegate to service
-> respond
-> audit log
```

## Role behavior in this project

### Public APIs
- guest login
- registered login
- register user

### Guest/User/Admin APIs
- quiz load
- quiz submit
- quiz result show

### Admin-only APIs
- question add
- question set create (final contract: no raw secret key, derives question count from selection, supports `makeActive`)
- question set edit (final contract: edit by config id, preserves secret key, supports `makeActive`)
- question show (paginated question browser with answer options, cursor-based, limit 1–20)
- question set show (paginated config browser, cursor-based, limit 1–20)
