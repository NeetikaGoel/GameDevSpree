# Frontend Folder

This folder contains the client-side UI of QuizGameWithAuth.

## Purpose

The frontend:
- renders pages
- handles button clicks and form submissions
- calls backend APIs using `fetch`
- stores current identity details in cookies for display/navigation behavior
- redirects users through the quiz flow

## Main files

### Shared files
- `auth.ts`
- `auth.js`
- `style.css`

### Pages
- `index.html`
- `login.html`
- `register.html`
- `quiz.html`
- `result.html`
- `questionAdd.html`
- `questionSetCreate.html`
- `questionSetEdit.html`

### Scripts
- `login.ts/js`
- `register.ts/js`
- `quiz.ts/js`
- `questionAdd.ts/js`
- `questionSetCreate.ts/js`
- `questionSetEdit.ts/js`

## Frontend architecture

### HTML
Provides page structure and form inputs.

### TypeScript / JavaScript
Handles:
- DOM events
- validation at UI level
- API calls
- JSON response handling
- page navigation

### `auth.ts` / `auth.js`
This is the shared auth helper for:
- cookie set/get/delete
- session save/clear
- login-state display
- navbar visibility
- admin-only link visibility

## Important architectural note

The frontend is **not** the source of truth for authentication.  
It only stores and displays identity-related values in cookies.

The backend still validates the `uid` against the database on every relevant request.

## Current user-facing flows

- guest login
- registered login
- registration
- quiz play
- result page
- admin question add
- admin question-set create (page exists; backend API is fully implemented)
- admin question-set edit (page exists; backend API is fully implemented)

Note: the frontend pages for question-set create/edit (`questionSetCreate.html`, `questionSetEdit.html`) have their forms, but full wiring to the paginated question browser (`questionShow.php`) is pending.
