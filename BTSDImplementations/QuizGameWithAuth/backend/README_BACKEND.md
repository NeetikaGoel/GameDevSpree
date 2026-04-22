# Backend Folder

This folder contains the server-side logic of the QuizGameWithAuth project.

## Purpose

The backend receives HTTP requests from the frontend, validates access and inputs, executes business logic, talks to the database through repository abstractions, and returns JSON responses.

## Folder structure

- [API layer](./api/README_BACKEND_API.md)
- [Service layer](./service/README_BACKEND_SERVICE.md)
- [Params layer](./params/README_BACKEND_PARAMS.md)
- [Entity layer](./entity/README_BACKEND_ENTITY.md)
- `config.php`
- `featureFlag.json`

## Responsibilities

### 1. API boundary handling
Files in `api/`:
- receive request
- perform boundary checks
- call services
- return JSON
- log failures and success

### 2. Business logic
Files in `service/`:
- registration logic
- guest login logic
- registered login logic
- quiz load logic
- quiz submit logic
- result generation
- admin question creation
- question show service (cursor-paginated question list with answer options)
- question-set show service (cursor-paginated config list)
- question-set create service (final contract: derives count target, resolves secret key internally, makeActive support)
- question-set edit service (final contract: edit by config id, preserves secret key, makeActive support)

### 3. Request parameter typing
Files in `params/`:
- wrap and validate request parameter structure for selected APIs

### 4. Domain modeling
Files in `entity/`:
- represent business objects like user, question, answer option, game config, and quiz progress

## Important files

### `config.php`
Central configuration and constants file.  
Usually holds:
- status codes
- permission names
- login type names
- config defaults
- secret-key related constants
- path-independent shared constants

### `featureFlag.json`
Stores feature flag information used by the backend, such as quiz randomization behavior.

## Backend pattern used in this project

A typical backend request follows this path:

```text
frontend fetch
-> backend/api/*.php
-> backend/service/*.php
-> database/repository/*
-> database/query + mapper + orm
-> MySQL
-> JSON response
```

## Why this backend structure is good

- separates transport logic from business logic
- keeps DB access away from controllers/APIs
- makes future APIs easier to add
- improves readability and testing friendliness
- supports manager/reviewer understanding more easily
