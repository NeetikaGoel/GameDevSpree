# Backend Service Folder

This folder contains the business logic layer of the project.

## Purpose

Service classes sit between the API boundary layer and the repository layer.

They take validated input from APIs, apply business rules, use repositories to read/write data, and return structured response arrays.

## Why this layer matters

Without this layer, API files become too large and hard to maintain.

With this layer:
- business logic stays reusable
- APIs stay clean
- database logic remains abstracted
- future changes are easier

## Main services

### Authentication services
- `LoginGuestService`
- `LoginUserService`
- `RegisterUserService`

These services manage:
- guest identity creation
- registered login
- guest-to-user upgrade
- password hash verification and hashing flow

### Quiz services
- `QuizLoadService`
- `QuizSubmitService`
- `QuizResultShowService`

These services manage:
- reading/creating quiz progress
- evaluating answers
- updating progress
- computing result summaries

### Admin services
- `QuestionAddService`
- `QuestionSetCreateService`
- `QuestionSetEditService`

These services manage:
- question creation
- answer-option creation
- game config creation/editing
- evolving admin control over quiz content

## Typical responsibilities of a service

- apply business validation
- check logical consistency
- fetch related records
- compute derived values
- write new records or update existing ones
- return safe response payloads

## What services should avoid

Services should avoid:
- direct HTML handling
- direct cookie handling
- response header handling
- raw low-level SQL embedded inside service logic

That work belongs elsewhere.

## Service-to-database pattern

```text
Service
-> Repository
-> Query
-> OrmManager
-> DBManager
-> MySQL
```
