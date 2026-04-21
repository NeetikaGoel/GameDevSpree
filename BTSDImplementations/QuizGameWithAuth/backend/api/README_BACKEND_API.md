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

## Example APIs in this folder

- `loginGuest.php`
- `loginUser.php`
- `registerUser.php`
- `quizLoad.php`
- `quizSubmit.php`
- `quizResultShow.php`
- `questionAdd.php`
- `questionSetCreate.php`
- `questionSetEdit.php`

## Recommended future structure

For long-term cleanliness, place APIs under versioned paths such as:

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
- question set create
- question set edit
- proposed question show
- proposed question set show
