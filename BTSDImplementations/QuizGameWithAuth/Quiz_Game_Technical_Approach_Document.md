# QuizGameWithAuth — End-to-End API and Flow Document

## Table of Contents

| Section | Title | Link |
|---|---|---|
| 1 | Purpose of this document | [Go to Section 1](#section-1-purpose-of-this-document) |
| 2 | High-level architecture | [Go to Section 2](#section-2-high-level-architecture) |
| 2.1 | Layers in the project | [Go to Section 2.1](#section-21-layers-in-the-project) |
| 3 | Global end-to-end application flow | [Go to Section 3](#section-3-global-end-to-end-application-flow) |
| 3.1 | Current user journey | [Go to Section 3.1](#section-31-current-user-journey) |
| 4 | Current frontend flow | [Go to Section 4](#section-4-current-frontend-flow) |
| 4.1 | Shared auth helper | [Go to Section 4.1](#section-41-shared-auth-helper) |
| 4.2 | Frontend pages and API calls | [Go to Section 4.2](#section-42-frontend-pages-and-api-calls) |
| 5 | Current database tables and their purpose | [Go to Section 5](#section-5-current-database-tables-and-their-purpose) |
| 5.1 | `users` | [Go to Section 5.1](#section-51-users) |
| 5.2 | `user_permissions` | [Go to Section 5.2](#section-52-user_permissions) |
| 5.3 | `user_progress_states` | [Go to Section 5.3](#section-53-user_progress_states) |
| 5.4 | `questions` | [Go to Section 5.4](#section-54-questions) |
| 5.5 | `answer_options` | [Go to Section 5.5](#section-55-answer_options) |
| 5.6 | `game_configs` | [Go to Section 5.6](#section-56-game_configs) |
| 6 | API versioning note | [Go to Section 6](#section-6-api-versioning-note) |
| 7 | Current API-by-API documentation | [Go to Section 7](#section-7-current-api-by-api-documentation) |
| 7.1 | API: Guest Login | [Go to Section 7.1](#section-71-api-guest-login) |
| 7.2 | API: Registered User Login | [Go to Section 7.2](#section-72-api-registered-user-login) |
| 7.3 | API: Register User | [Go to Section 7.3](#section-73-api-register-user) |
| 7.4 | API: Quiz Load | [Go to Section 7.4](#section-74-api-quiz-load) |
| 7.5 | API: Quiz Submit | [Go to Section 7.5](#section-75-api-quiz-submit) |
| 7.6 | API: Quiz Result Show | [Go to Section 7.6](#section-76-api-quiz-result-show) |
| 7.7 | API: Question Add | [Go to Section 7.7](#section-77-api-question-add) |
| 7.8 | API: Question Set Create *(now fully implemented with final contract)* | [Go to Section 7.8](#section-78-api-question-set-create-current-state-planned-refactor) |
| 7.9 | API: Question Set Edit *(now fully implemented with final contract)* | [Go to Section 7.9](#section-79-api-question-set-edit-current-state-planned-refactor) |
| 8 | New admin APIs — now implemented | [Go to Section 8](#section-8-proposed-new-admin-apis-for-manager-approval) |
| 8.1 | API: Question Show | [Go to Section 8.1](#section-81-proposed-api-question-show) |
| 8.2 | API: Question Set Show / Game Config Show | [Go to Section 8.2](#section-82-proposed-api-question-set-show--game-config-show) |
| 9 | Final desired admin workflows | [Go to Section 9](#section-9-final-desired-admin-workflows) |
| 9.1 | Create question-set flow | [Go to Section 9.1](#section-91-create-question-set-flow) |
| 9.2 | Edit question-set flow | [Go to Section 9.2](#section-92-edit-question-set-flow) |
| 9.3 | Question show workflow | [Go to Section 9.3](#section-93-question-show-workflow-final-desired-admin-workflow) |
| 9.4 | Question-set show workflow | [Go to Section 9.4](#section-94-question-set-show-workflow-final-desired-admin-workflow) |
| 10 | Implemented final contracts for question-set APIs | [Go to Section 10](#section-10-recommended-final-contracts-for-question-set-apis) |
| 10.1 | Implemented final create API contract | [Go to Section 10.1](#section-101-recommended-final-create-api-contract) |
| 10.2 | Implemented final edit API contract | [Go to Section 10.2](#section-102-recommended-final-edit-api-contract) |
| 11 | Flowchart-style text diagrams | [Go to Section 11](#section-11-flowchart-style-text-diagrams) |
| 11.1 | Main application flow | [Go to Section 11.1](#section-111-main-application-flow) |
| 11.2 | Quiz flow | [Go to Section 11.2](#section-112-quiz-flow) |
| 11.3 | Admin create question-set flow | [Go to Section 11.3](#section-113-admin-create-question-set-flow-proposed) |
| 11.4 | Admin edit question-set flow | [Go to Section 11.4](#section-114-admin-edit-question-set-flow-proposed) |
| 11.5 | Question show API flow | [Go to Section 11.5](#section-115-question-show-api-flow-proposed) |
| 11.6 | Question-set show API flow | [Go to Section 11.6](#section-116-question-set-show-api-flow-proposed) |
| 12 | API summary table | [Go to Section 12](#section-12-api-summary-table) |
| 13 | Key implementation observations for manager review | [Go to Section 13](#section-13-key-implementation-observations-for-manager-review) |
| 13.1 | What is already strong | [Go to Section 13.1](#section-131-what-is-already-strong) |
| 13.2 | What still needs refinement for question-set feature | [Go to Section 13.2](#section-132-what-still-needs-refinement-for-question-set-feature) |
| 13.3 | Recommended implementation sequence | [Go to Section 13.3](#section-133-recommended-implementation-sequence) |
| 14 | Future improvements / enhancements | [Go to Section 14](#section-14-future-improvements--enhancements) |
| 14.1 | Better toggle UX for `is_active` | [Go to Section 14.1](#section-141-better-toggle-ux-for-is_active) |
| 14.2 | More metadata fields for configs | [Go to Section 14.2](#section-142-more-metadata-fields-for-configs) |
| 14.3 | Stronger naming constraints for configs | [Go to Section 14.3](#section-143-stronger-naming-constraints-for-configs) |
| 14.4 | Better pagination controls | [Go to Section 14.4](#section-144-better-pagination-controls) |
| 14.5 | Config activation by date window | [Go to Section 14.5](#section-145-config-activation-by-date-window) |
| 15 | Final approval-oriented summary | [Go to Section 15](#section-15-final-approval-oriented-summary) |

<a id="section-1-purpose-of-this-document"></a>
## 1. Purpose of this document

This document explains the complete current end-to-end flow of the **QuizGameWithAuth** project, from frontend page actions to backend APIs, services, repositories, queries, mappers, ORM layer, and database tables.

It documents the fully implemented **admin question-set management workflow**, including:
- Question browsing for admin (paginated, with answer options)
- Question-set browsing for admin (paginated)
- Question-set creation (final contract — no raw secret key, derives question count, supports `makeActive`)
- Question-set editing (final contract — edit by config id, preserves secret key, supports `makeActive`)

The goal is to make the architecture, request flow, validations, permissions, database usage, and API contracts crystal clear for ongoing development and review.

This document is written as a **technical README-style design document** so it can be shared for review.

---

<a id="section-2-high-level-architecture"></a>
## 2. High-level architecture

<a id="section-21-layers-in-the-project"></a>
### 2.1 Layers in the project

The project is organized into these layers.

### Frontend
Files in `frontend/`:
- HTML pages render UI
- TS/JS files handle UI events and call backend APIs with `fetch`
- `auth.ts/auth.js` manages cookie-based client-side identity display and navigation state

### Backend API boundary layer
Files in `backend/api/`:
- Each API file accepts HTTP request
- Performs authentication, authorization, validation, rate-limit placeholder checks, and idempotency placeholder checks
- Delegates to service layer
- Returns JSON response
- Logs success/failure

### Backend service layer
Files in `backend/service/`:
- Contains business logic
- Calls repositories
- Builds response payloads
- Applies rules like:
  - guest vs registered behavior
  - quiz loading
  - quiz answer evaluation
  - result generation
  - question creation
  - question-set creation/editing

### Entity layer
Files in `backend/entity/`:
Represents structured domain objects:
- `User`
- `UserPermission`
- `UserProgressState`
- `Question`
- `AnswerOption`
- `GameConfig`
- `Quiz`

This layer helps keep service logic object-oriented and clean.

### Repository layer
Files in `database/repository/`:
- Provides application-friendly methods for database access
- Hides SQL/query details from services

### Query layer
Files in `database/query/`:
- Stores SQL strings

### Mapper layer
Files in `database/mapper/`:
- Converts DB rows into PHP entity objects or mapped arrays

### ORM / DB management layer
Files:
- `database/ormManager.php`
- `database/dbManager.php`
- `database/dbConnect.php`

Responsibilities:
- connect to MySQL
- run queries
- prepare statements
- bind params
- fetch rows
- convert rows to mapped objects

### Database layer
Files:
- `database/schema.sql`
- `database/data.sql`
- `database/updatedb.sql`

Tables currently used:
- `users`
- `user_permissions`
- `user_progress_states`
- `questions`
- `answer_options`
- `game_configs`
- `quiz_attempts` *(older/stateless experiment support, not the primary live flow now)*

---

<a id="section-3-global-end-to-end-application-flow"></a>
## 3. Global end-to-end application flow

<a id="section-31-current-user-journey"></a>
### 3.1 Current user journey

### Landing page
File: `frontend/index.html`
- Shows app intro
- Offers:
  - Start Quiz
  - Continue as Guest
- Uses `login.js`

### Guest flow
1. User clicks **Continue as Guest**
2. Frontend calls `POST /backend/api/v1/loginGuest.php`
3. Backend creates guest user + guest permission
4. Frontend stores returned identity in cookies
5. Frontend redirects to `quiz.html`

### Registered login flow
1. User opens `login.html`
2. Submits email/password
3. Frontend calls `POST /backend/api/v1/loginUser.php`
4. Backend verifies email and password hash using secret key logic
5. Frontend stores returned identity in cookies
6. Frontend redirects to `quiz.html`

### Register flow
1. User opens `register.html`
2. Submits name/email/password
3. Frontend calls `POST /backend/api/v1/registerUser.php`
4. Backend either:
   - creates new registered user, or
   - upgrades guest user to registered user
5. Password is salted using secret-key suffix before hashing
6. Frontend stores returned identity in cookies
7. Frontend redirects to `quiz.html`

### Quiz flow
1. `quiz.html` loads
2. Frontend calls `GET /backend/api/v1/quizLoad.php?uid=...`
3. Backend:
   - checks current user progress
   - if no progress exists, creates quiz state from active config
   - returns current question and answer options
4. User chooses one answer
5. Frontend calls `POST /backend/api/v1/quizSubmit.php`
6. Backend evaluates answer and updates score/progress
7. Either:
   - returns next-question state, or
   - marks quiz complete and frontend moves to result page

### Result flow
1. `result.html` loads
2. Frontend calls `GET /backend/api/v1/quizResultShow.php?uid=...`
3. Backend calculates and returns final result summary

### Admin flow
Admin user can access:
- `questionAdd.html`
- `questionSetCreate.html`
- `questionSetEdit.html`

Currently implemented fully:
- question add
- question-set create
- question-set edit
- question show API (paginated)
- question-set show API (paginated)

---

<a id="section-4-current-frontend-flow"></a>
## 4. Current frontend flow

<a id="section-41-shared-auth-helper"></a>
### 4.1 Shared auth helper

**File:** `frontend/auth.ts` / `frontend/auth.js`

### Role
This file manages client-side cookie storage for identity display and routing.

### Stores these cookies
- `uid`
- `userId`
- `loginType`
- `permissionGroup`
- `name`
- `email`

### What it does
- set/get/delete cookies
- save full user session after login/register/guest login
- clear session on logout
- tell whether user is logged in
- tell whether current user is guest/admin
- update navbar visibility:
  - login/register hidden when logged in
  - logout shown when logged in
  - admin-only links shown only for admin

### Important architectural note
This is **not** server-side session management.

The backend is **not** using PHP sessions.

The backend trusts the `uid` supplied by frontend and validates/authorizes it against DB.

So the current architecture is:
- client stores identity in cookies
- backend treats each API request independently
- quiz state lives in DB (`user_progress_states`)

---

<a id="section-42-frontend-pages-and-api-calls"></a>
### 4.2 Frontend pages and API calls

### `index.html` + `login.js`
Triggers:
- guest login
- start quiz redirect

### `login.html` + `login.ts`
Calls:
- `loginGuest.php`
- `loginUser.php`

### `register.html` + `register.ts`
Calls:
- `registerUser.php`
- `loginGuest.php` for guest continuation option

### `quiz.html` + `quiz.ts`
Calls:
- `quizLoad.php`
- `quizSubmit.php`
- `quizResultShow.php` indirectly when result page loads

### `result.html` + `quiz.ts`
Calls:
- `quizResultShow.php`

### `questionAdd.html` + `questionAdd.ts`
Calls:
- `questionAdd.php`

### `questionSetCreate.html` + `questionSetCreate.ts`
Calls:
- `questionSetCreate.php`

Can call (backend implemented, frontend wiring pending):
- `questionShow.php` — to browse and select questions from a paginated list

### `questionSetEdit.html` + `questionSetEdit.ts`
Calls:
- `questionSetEdit.php`

Can call (backend implemented, frontend wiring pending):
- `questionSetShow.php` — to load existing configs for selection
- `questionShow.php` — to browse and change selected questions

---

<a id="section-5-current-database-tables-and-their-purpose"></a>
## 5. Current database tables and their purpose

<a id="section-51-users"></a>
### 5.1 `users`
Stores user identity.

Used by:
- guest login
- registered login
- registration
- authentication checks for admin APIs

Column definitions:

| Column | SQL type | Signed/Unsigned | Bits | Max length | Nullable | Default | Notes |
|---|---|---|---|---|---|---|---|
| `uid` | `INT AUTO_INCREMENT PRIMARY KEY` | Signed | 32-bit | Max 2,147,483,647 | No | auto | Auto-generated row id |
| `user_id` | `VARCHAR(100)` | N/A | N/A | 100 chars | No | None | Unique user identity string, e.g. `guest_...` or `user_...` |
| `login_type` | `VARCHAR(50)` | N/A | N/A | 50 chars | No | None | Either `guest` or `registered` |
| `email` | `VARCHAR(255)` | N/A | N/A | 255 chars | Yes | NULL | Standard email, nullable for guest users |
| `name` | `VARCHAR(255)` | N/A | N/A | 255 chars | Yes | NULL | Display name, nullable for guest users |
| `password_hash` | `VARCHAR(255)` | N/A | N/A | 255 chars | Yes | NULL | bcrypt hash of password+separator+secret_key, nullable for guest users |
| `created_at` | `DATETIME(3)` | N/A | N/A | 3-digit millisecond precision | No | `CURRENT_TIMESTAMP(3)` | Row creation time |
| `updated_at` | `DATETIME(3)` | N/A | N/A | 3-digit millisecond precision | No | `CURRENT_TIMESTAMP(3) ON UPDATE` | Row last-update time |

Notes:
- `uid` is signed 32-bit INT. Values are always positive (auto-increment starts from 1).
- `user_id` has a `UNIQUE` constraint so no two users can share an identity string.
- `email` has no `UNIQUE` constraint defined at DB level; uniqueness is enforced in application logic.
- `password_hash` stores the result of PHP `password_hash()` which outputs bcrypt hashes of length up to 255 chars.

---

<a id="section-52-user_permissions"></a>
### 5.2 `user_permissions`
Stores authorization role.

Possible values:
- `guest`
- `user`
- `admin`

Used by:
- login flows
- quiz authorization
- admin-only APIs

Column definitions:

| Column | SQL type | Signed/Unsigned | Bits | Max length | Nullable | Default | Notes |
|---|---|---|---|---|---|---|---|
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Signed | 32-bit | Max 2,147,483,647 | No | auto | Auto-generated row id |
| `uid` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | Foreign key → `users.uid` |
| `permission_group` | `VARCHAR(50)` | N/A | N/A | 50 chars | No | None | One of `guest`, `user`, or `admin` |
| `created_at` | `DATETIME(3)` | N/A | N/A | 3-digit millisecond precision | No | `CURRENT_TIMESTAMP(3)` | Row creation time |
| `updated_at` | `DATETIME(3)` | N/A | N/A | 3-digit millisecond precision | No | `CURRENT_TIMESTAMP(3) ON UPDATE` | Row last-update time |

---

<a id="section-53-user_progress_states"></a>
### 5.3 `user_progress_states`
Stores active quiz state for each user.

Used by:
- quiz load
- quiz submit
- result show

Column definitions:

| Column | SQL type | Signed/Unsigned | Bits | Max length | Nullable | Default | Notes |
|---|---|---|---|---|---|---|---|
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Signed | 32-bit | Max 2,147,483,647 | No | auto | Auto-generated row id |
| `uid` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | Foreign key → `users.uid` |
| `score_current` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | Current quiz score, always ≥ 0 |
| `questions_done` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | How many questions answered so far |
| `question_id_order_json` | `TEXT NOT NULL` | N/A | N/A | Up to 65,535 bytes | No | None | JSON array of question ids in quiz order |
| `question_id_order_index_current` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | Current index into the question order array |
| `question_id_current` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | The question id currently being answered |
| `is_complete` | `BOOLEAN NOT NULL` | N/A | 1-bit effectively | `TRUE` or `FALSE` | No | None | Whether the quiz is finished |
| `created_at` | `DATETIME(3)` | N/A | N/A | 3-digit millisecond precision | No | `CURRENT_TIMESTAMP(3)` | Row creation time |
| `updated_at` | `DATETIME(3)` | N/A | N/A | 3-digit millisecond precision | No | `CURRENT_TIMESTAMP(3) ON UPDATE` | Row last-update time |

Notes:
- `question_id_order_json` uses TEXT because JSON arrays of question ids can be moderately long.
- This table is the main reason the quiz is **stateful in DB**, but **stateless over HTTP**.

---

<a id="section-54-questions"></a>
### 5.4 `questions`
Stores question master data.

Used by:
- question add
- quiz load
- question show API (paginated)
- question-set create/edit flow

Column definitions:

| Column | SQL type | Signed/Unsigned | Bits | Max length | Nullable | Default | Notes |
|---|---|---|---|---|---|---|---|
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Signed | 32-bit | Max 2,147,483,647 | No | auto | Auto-generated question id |
| `type` | `VARCHAR(100) NOT NULL` | N/A | N/A | 100 chars | No | None | Question type, e.g. `mcq` or `true/false` |
| `text` | `VARCHAR(255) NOT NULL` | N/A | N/A | 255 chars | No | None | Question content |

Notes:
- `type` allows only 100 characters. Current types in use are short strings like `mcq` and `true/false`.
- `text` is limited to 255 characters, meaning questions must be phrased concisely.
- There is no explicit UNIQUE constraint on `text` — identical question text is not prevented at DB level.

---

<a id="section-55-answer_options"></a>
### 5.5 `answer_options`
Stores answer choices for each question.

Used by:
- question add
- quiz load
- quiz submit
- question show API (paginated)

Column definitions:

| Column | SQL type | Signed/Unsigned | Bits | Max length | Nullable | Default | Notes |
|---|---|---|---|---|---|---|---|
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Signed | 32-bit | Max 2,147,483,647 | No | auto | Auto-generated answer option id |
| `type` | `VARCHAR(100) NOT NULL` | N/A | N/A | 100 chars | No | None | Type, usually matches question type like `mcq` or `true/false` |
| `text` | `VARCHAR(100) NOT NULL` | N/A | N/A | 100 chars | No | None | Answer option display text |
| `question_id` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | Foreign key → `questions.id` |
| `is_correct` | `BOOLEAN NOT NULL` | N/A | 1-bit effectively | `TRUE` or `FALSE` | No | None | Whether this option is the correct answer |

Notes:
- `text` here is 100 chars (shorter than the question's 255 char limit). Answer option text is expected to be brief.
- `is_correct` must have exactly one `TRUE` per question at the application level. The DB does not enforce this with a constraint.

---

<a id="section-56-game_configs"></a>
### 5.6 `game_configs`
Stores question-set / config definitions.

### Current purpose
- determine which questions are allowed for quiz
- determine question count target
- provide secret key for password hashing flow
- determine active question set for quiz load
- allow admin to create and manage named question sets

### Column definitions

| Column | SQL type | Signed/Unsigned | Bits | Max length | Nullable | Default | Notes |
|---|---|---|---|---|---|---|---|
| `id` | `INT AUTO_INCREMENT PRIMARY KEY` | Signed | 32-bit | Max 2,147,483,647 | No | auto | Auto-generated config id |
| `game_config_name` | `VARCHAR(100) NOT NULL UNIQUE` | N/A | N/A | 100 chars | No | None | Unique config name; application enforces character restrictions too |
| `question_count_target` | `INT NOT NULL` | Signed | 32-bit | Max 2,147,483,647 | No | None | How many questions are in this set; derived from `questionIdListAllowed` count |
| `question_id_list_allowed_json` | `TEXT NOT NULL` | N/A | N/A | Up to 65,535 bytes | No | None | JSON array of allowed question ids |
| `secret_key` | `VARCHAR(255) NOT NULL` | N/A | N/A | 255 chars | No | `'neetikagoel12345'` | Secret key used in password hash derivation; never exposed to admin UI |
| `is_active` | `BOOLEAN NOT NULL` | N/A | 1-bit effectively | `TRUE` or `FALSE` | No | `FALSE` | Whether this config drives the live quiz |
| `created_at` | `DATETIME NOT NULL` | N/A | N/A | Second precision | No | `CURRENT_TIMESTAMP` | Row creation time |
| `updated_at` | `DATETIME NOT NULL` | N/A | N/A | Second precision | No | `CURRENT_TIMESTAMP ON UPDATE` | Row last-update time |

Notes:
- `game_config_name` has `UNIQUE` constraint at DB level AND max 100 char limit.
- Application-level validation (in `questionSetCreate.php` and `questionSetEdit.php`) additionally restricts allowed characters to: `A-Z`, `a-z`, `0-9`, space, `_`, `,`, `-`, `(`, `)`, `.`, `&`. This prevents injection or HTML-like input.
- `question_id_list_allowed_json` stores a JSON array of integers, e.g. `[1,2,3,4,5]`. Uses `TEXT` not `VARCHAR` to handle larger question sets safely.
- `secret_key` defaults to `'neetikagoel12345'` in the schema. In practice, new configs copy the secret key from the active or default config at creation time via `QuestionSetCreateService`.
- `created_at` / `updated_at` in this table use plain `DATETIME` (second precision), while `users`, `user_permissions`, and `user_progress_states` use `DATETIME(3)` (millisecond precision).
- Only one config should have `is_active = TRUE` at any time. This is enforced by the service layer via `deactivateAllGameConfigs()` before setting a new active config.

### Used by
- register flow
- login flow
- quiz load
- question-set create API
- question-set edit API
- question-set show API

### Input/constraint notes for config naming
Config name validation is enforced in both the create and edit API boundary layers:
- **Max length**: 100 characters (`mb_strlen` check in PHP + `VARCHAR(100)` in DB)
- **Allowed characters**: alphanumeric, space, `_`, `,`, `-`, `(`, `)`, `.`, `&` — enforced with `preg_match('/^[A-Za-z0-9 _,\-().&]+$/', $gameConfigName)`
- **HTML / script injection**: blocked because `<`, `>`, `"`, `'`, `;` are not in the allowed set
- **Empty name**: rejected at validation step
- This validation is **already implemented and active** in both `questionSetCreate.php` and `questionSetEdit.php`.

---

<a id="section-6-api-versioning-note"></a>
## 6. API versioning note

For documentation consistency and future-proofing, all APIs in this document are shown under a **v1 versioned path**.

### Standard form used in this document
`/backend/api/v1/<apiName>.php`

### Current logic impact
This is a **format and path organization change only** in this document.
It does **not** change the business logic of any existing API.

---

<a id="section-7-current-api-by-api-documentation"></a>
## 7. Current API-by-API documentation

---

<a id="section-71-api-guest-login"></a>
### 7.1 API: Guest Login

### URL
`POST /backend/api/v1/loginGuest.php`

### Purpose
Creates a new guest identity and returns guest access info.

### Authorization
Public.
No auth required.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| None | None | No request body required |

### Example request
```json
{
  "method": "POST",
  "body": {}
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Positive integer generated by DB |
| userId | string | Non-empty guest id like `guest_xxx...` |
| loginType | string | `guest` |
| permissionGroup | string | `guest` |
| error | string | Present only on failure |

### Example success response
```json
{
  "uid": 49,
  "userId": "guest_a1b2c3d4e5f67890",
  "loginType": "guest",
  "permissionGroup": "guest"
}
```

### Example error response
```json
{
  "error": "Runtime failure while logging in guest!!"
}
```

### Internal checks
Boundary file performs:
1. authenticate → always true
2. authorize → always true
3. request method must be POST
4. rate limit placeholder
5. idempotency placeholder

### Service behavior
`LoginGuestService`
1. generates unique guest `user_id`
2. inserts guest row into `users`
3. inserts guest permission row into `user_permissions`
4. fetches both rows back
5. returns guest identity payload

### Database tables used and why
- `users` → create guest identity row
- `user_permissions` → assign guest role

### Flow
Frontend button → `loginGuest.php` → `LoginGuestService` → repositories → DB → JSON → frontend cookies → redirect to quiz

---

<a id="section-72-api-registered-user-login"></a>
### 7.2 API: Registered User Login

### URL
`POST /backend/api/v1/loginUser.php`

### Purpose
Logs in an existing registered user.

### Authorization
Public endpoint, but validates credentials.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| email | string | Valid non-empty email format |
| password | string | Non-empty string |

### Example request
```json
{
  "method": "POST",
  "body": {
    "email": "admin1@example.com",
    "password": "admin123"
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Positive integer |
| userId | string | Non-empty registered user id |
| name | string | Non-empty or nullable stored value |
| email | string | Valid email |
| loginType | string | `registered` |
| permissionGroup | string | `user` or `admin` |
| error | string | Present only on failure |

### Example success response
```json
{
  "uid": 1,
  "userId": "user_admin_001",
  "name": "AdminABC",
  "email": "admin1@example.com",
  "loginType": "registered",
  "permissionGroup": "admin"
}
```

### Example error response
```json
{
  "error": "Invalid request input!!"
}
```

### Internal checks
Boundary file performs:
1. authenticate placeholder → true
2. authorize placeholder → true
3. request method must be POST
4. email/password required
5. email format valid
6. rate limit placeholder
7. idempotency placeholder

### Service behavior
`LoginUserService`
1. fetches user by email from `users`
2. fetches stored password hash
3. fetches game config using default config name
4. reads secret key from config
5. builds `password + separator + secret_key`
6. verifies using `password_verify`
7. fetches user permission
8. returns user identity payload

### Database tables used and why
- `users` → find user and password hash
- `user_permissions` → fetch role for authorization data in response
- `game_configs` → fetch secret key used in password verification flow

### Important security behavior
Password verification is not direct plaintext comparison anymore.
It is:
- raw password entered by user
- appended with secret key from config
- verified against stored hash

### Flow
Frontend login form → `loginUser.php` → `LoginUserService` → `users` + `game_configs` + `user_permissions` → JSON → cookies → quiz

---

<a id="section-73-api-register-user"></a>
### 7.3 API: Register User

### URL
`POST /backend/api/v1/registerUser.php`

### Purpose
Registers a new user, or upgrades a guest user into a registered user.

### Authorization
Public endpoint.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| name | string | Non-empty string |
| email | string | Valid non-empty email format |
| password | string | Non-empty string |
| uid | integer | Optional positive integer when upgrading guest |

### Example request for new registration
```json
{
  "method": "POST",
  "body": {
    "name": "ABC",
    "email": "abc@gmail.com",
    "password": "abcabc"
  }
}
```

### Example request for guest upgrade
```json
{
  "method": "POST",
  "body": {
    "uid": 49,
    "name": "XYZ",
    "email": "xyz@gmail.com",
    "password": "xyzxyz"
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Positive integer |
| userId | string | Non-empty registered user id |
| name | string | Non-empty string |
| email | string | Valid email |
| loginType | string | `registered` |
| permissionGroup | string | `user` |
| error | string | Present only on failure |

### Example success response
```json
{
  "uid": 50,
  "userId": "user_ab12cd34ef56gh78",
  "name": "ABC",
  "email": "abc@gmail.com",
  "loginType": "registered",
  "permissionGroup": "user"
}
```

### Example error response
```json
{
  "error": "A user with this email already exists!!"
}
```

### Internal checks
Boundary file performs:
1. authenticate placeholder
2. authorize placeholder
3. request method POST only
4. name/email/password required
5. email format validation
6. optional uid must be positive if provided
7. rate limit placeholder
8. idempotency placeholder

### Service behavior
`RegisterUserService`
1. checks whether email already exists
2. fetches game config and secret key
3. builds `password + separator + secret_key`
4. hashes with `password_hash`
5. if `uid` present:
   - verifies guest user exists
   - verifies user login type is guest
   - verifies permission group is guest
   - updates guest row into registered user
   - updates permission from guest to user
6. else:
   - generates unique registered user id
   - inserts into `users`
   - inserts permission into `user_permissions`
7. returns final user identity payload

### Database tables used and why
- `users` → create or upgrade registered identity
- `user_permissions` → assign final registered role
- `game_configs` → fetch secret key for password preparation before hashing

### Why it exists
Supports both:
- direct registration
- guest-to-user upgrade flow

---

<a id="section-74-api-quiz-load"></a>
### 7.4 API: Quiz Load

### URL
`GET /backend/api/v1/quizLoad.php?uid={uid}`

### Purpose
Loads the current question for a user.
If no quiz state exists yet, creates a fresh quiz state from active config.

### Authorization
Allowed roles:
- guest
- user
- admin

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |

### Example request
```json
{
  "method": "GET",
  "query": {
    "uid": 24
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Positive integer |
| score | integer | 0 to total questions |
| questionsDone | integer | 0 to total questions |
| questionIdCurrent | integer | Positive integer question id |
| questionTextCurrent | string | Non-empty string |
| questionTypeCurrent | string | Existing question type like `mcq`, `true/false` |
| answerOptionsCurrent | array | Non-empty list of answer options |
| questionCountTotal | integer | Positive integer |
| isQuizDone | boolean | `true` or `false` |
| resultLink | string | Present when quiz already complete |
| error | string | Present only on failure |

### Example active quiz response
```json
{
  "uid": 24,
  "score": 1,
  "questionsDone": 2,
  "questionIdCurrent": 5,
  "questionTextCurrent": "PHP is a backend language!",
  "questionTypeCurrent": "true/false",
  "answerOptionsCurrent": [
    {
      "id": 12,
      "type": "true/false",
      "text": "True"
    },
    {
      "id": 13,
      "type": "true/false",
      "text": "False"
    }
  ],
  "questionCountTotal": 5,
  "isQuizDone": false
}
```

### Example complete quiz response
```json
{
  "uid": 24,
  "isQuizDone": true,
  "resultLink": "quizResultShow.php"
}
```

### Internal checks
Boundary file performs:
1. reads uid from query
2. authenticates by verifying user exists
3. authorizes by checking permission group in DB
4. validates uid non-empty, numeric, positive
5. rate limit placeholder
6. idempotency placeholder
7. delegates to service

### Service behavior
`QuizLoadService`
1. reads `user_progress_states` by uid
2. if no progress exists:
   - fetches active game config
   - determines allowed question list and count target
   - applies feature flag randomization if enabled
   - creates `user_progress_states`
3. validates progress structure
4. if already complete → returns result redirect payload
5. instantiates `Quiz` entity with question order
6. fetches current question object
7. fetches answer options for current question
8. builds frontend-friendly response payload

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → authorization role check
- `user_progress_states` → read or create current quiz progress
- `game_configs` → fetch active question-set configuration
- `questions` → fetch current question details
- `answer_options` → fetch answer options for current question

### Important config behavior
This API now uses the **active game config**, not fixed `default_quiz`.

That means quiz content is driven by whichever config is currently active.

---

<a id="section-75-api-quiz-submit"></a>
### 7.5 API: Quiz Submit

### URL
`POST /backend/api/v1/quizSubmit.php`

### Purpose
Submits one selected answer for the current question and advances quiz state.

### Authorization
Allowed roles:
- guest
- user
- admin

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| answerOptionId | integer | Required positive integer |

### Example request
```json
{
  "method": "POST",
  "body": {
    "uid": 24,
    "answerOptionId": 12
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Positive integer |
| score | integer | 0 to total questions |
| questionsDone | integer | 1 to total questions |
| questionCountTotal | integer | Positive integer |
| isAnswerOptionCorrectForQuestion | boolean | `true` or `false` |
| isQuizDone | boolean | `true` or `false` |
| questionIdNext | integer | Present only when next question exists |
| resultLink | string | Present only when quiz finishes |
| error | string | Present only on failure |

### Example next-step response
```json
{
  "uid": 24,
  "score": 2,
  "questionsDone": 3,
  "questionCountTotal": 5,
  "isAnswerOptionCorrectForQuestion": true,
  "isQuizDone": false,
  "questionIdNext": 3
}
```

### Example complete response
```json
{
  "uid": 24,
  "score": 4,
  "questionsDone": 5,
  "questionCountTotal": 5,
  "isAnswerOptionCorrectForQuestion": true,
  "isQuizDone": true,
  "resultLink": "quizResultShow.php"
}
```

### Internal checks
Boundary file performs:
1. authenticates user exists
2. authorizes role valid
3. validates uid and answerOptionId required, numeric, positive
4. rate limit placeholder
5. idempotency placeholder

### Service behavior
`QuizSubmitService`
1. fetches progress state by uid
2. validates current question state consistency
3. instantiates `Quiz` with question order
4. fetches current question and selected option
5. verifies selected option belongs to current question
6. determines correctness
7. increments score if correct
8. increments questions done
9. if no next question:
   - marks progress complete
   - returns result redirect payload
10. else:
   - updates progress to next question
   - returns next-step payload

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → authorization role check
- `user_progress_states` → read and update quiz progress
- `questions` → fetch current question context
- `answer_options` → validate submitted answer option

---

<a id="section-76-api-quiz-result-show"></a>
### 7.6 API: Quiz Result Show

### URL
`GET /backend/api/v1/quizResultShow.php?uid={uid}`

### Purpose
Returns final quiz result summary.

### Authorization
Allowed roles:
- guest
- user
- admin

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |

### Example request
```json
{
  "method": "GET",
  "query": {
    "uid": 24
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Positive integer |
| score | integer | 0 to total questions |
| questionsDone | integer | 0 to total questions |
| questionCountTotal | integer | Positive integer |
| resultText | string | Non-empty display text |
| scorePercentage | integer | 0 to 100 |
| answerCountWrong | integer | 0 to total questions |
| error | string | Present only on failure |

### Example success response
```json
{
  "uid": 24,
  "score": 4,
  "questionsDone": 5,
  "questionCountTotal": 5,
  "resultText": "GREAT EFFORTS!! CAN DO BETTER!!",
  "scorePercentage": 80,
  "answerCountWrong": 1
}
```

### Internal checks
Boundary file performs:
1. authenticate user exists
2. authorize role valid
3. validate uid
4. rate limit placeholder
5. idempotency placeholder

### Service behavior
`QuizResultShowService`
1. fetches user progress state
2. verifies quiz complete
3. computes:
   - score percentage
   - wrong answer count
   - result text tier
4. returns result payload

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → authorization role check
- `user_progress_states` → calculate result from stored quiz progress

---

<a id="section-77-api-question-add"></a>
### 7.7 API: Question Add

### URL
`POST /backend/api/v1/questionAdd.php`

### Purpose
Allows admin to add a brand new question and its answer options.

### Authorization
Admin only.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| questionText | string | Non-empty string |
| questionType | string | Existing allowed question type |
| answerOptions | JSON array | Non-empty array with valid option objects |

Each answer option object contains:
- `text`
- `type`
- `isCorrect`

### Example request
```json
{
  "method": "POST",
  "body": {
    "uid": 1,
    "questionText": "What is the capital of India?",
    "questionType": "mcq",
    "answerOptions": [
      { "text": "Delhi", "type": "mcq", "isCorrect": true },
      { "text": "Agra", "type": "mcq", "isCorrect": false },
      { "text": "Patna", "type": "mcq", "isCorrect": false },
      { "text": "Pune", "type": "mcq", "isCorrect": false }
    ]
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| questionId | integer | Positive integer |
| questionText | string | Non-empty string |
| questionType | string | Existing question type |
| answerOptionCount | integer | Positive integer |
| isCreated | boolean | `true` on success |
| error | string | Present only on failure |

### Example success response
```json
{
  "questionId": 9,
  "questionText": "What is the capital of India?",
  "questionType": "mcq",
  "answerOptionCount": 4,
  "isCreated": true
}
```

### Internal checks
Boundary file performs:
1. POST only
2. authenticates uid exists in users
3. authorizes permission group must be admin
4. validates fields present
5. validates uid numeric positive
6. validates JSON answer options structure
7. validates answer option fields non-empty
8. rate limit placeholder
9. idempotency placeholder

### Service behavior
`QuestionAddService`
1. ensures exactly one correct answer exists
2. creates question row
3. creates all answer option rows linked to question
4. returns created metadata

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `questions` → create question row
- `answer_options` → create answer option rows

---

<a id="section-78-api-question-set-create-current-state-planned-refactor"></a>
### 7.8 API: Question Set Create *(now fully implemented with final contract)*

### URL
`POST /backend/api/v1/questionSetCreate.php`

### Purpose
Creates a new question-set / game config.

### Authorization
Admin only.

### Implemented service contract
The API and service now implement the final clean contract:
- `uid` — required, authenticated admin user
- `gameConfigName` — non-empty, max 100 chars, restricted character set
- `questionIdListAllowed` — JSON array of positive integer question ids, validated against DB
- `makeActive` — optional boolean-like flag; defaults to `false`
- `secretKey` is **not** accepted from the admin; it is derived internally by the service from the active config or default config
- `questionCountTarget` is **not** accepted from admin; it is derived by the service as `count(questionIdListAllowed)` after deduplication

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| gameConfigName | string | Non-empty, max 100 chars, allowed chars: `A-Z a-z 0-9 space _ , - ( ) . &` |
| questionIdListAllowed | JSON string | Non-empty JSON array of positive integers |
| makeActive | string | Optional; `true`, `1`, or `yes` to activate; anything else is treated as `false` |

### Example request
```json
{
  "method": "POST",
  "body": {
    "uid": 1,
    "gameConfigName": "SpecialGame1",
    "questionIdListAllowed": "[1, 2, 3, 4, 5, 6, 7]",
    "makeActive": "false"
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| gameConfigId | integer | Positive integer |
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | array | Non-empty deduplicated integer list |
| isActive | boolean | `true` or `false` |
| isCreated | boolean | `true` on success |
| error | string | Present only on failure |

### Example success response
```json
{
  "gameConfigId": 2,
  "gameConfigName": "SpecialGame1",
  "questionCountTarget": 7,
  "questionIdListAllowed": [1, 2, 3, 4, 5, 6, 7],
  "isActive": false,
  "isCreated": true
}
```

### Internal checks in API boundary layer (in order)
1. request method must be POST
2. uid must be present and numeric
3. authenticate: uid must exist in `users`
4. authorize: user must have `admin` permission in `user_permissions`
5. `gameConfigName` and `questionIdListAllowed` must be present and non-empty
6. uid must be positive
7. `gameConfigName` must be ≤ 100 chars
8. `gameConfigName` must match allowed character regex
9. `questionIdListAllowed` must decode to a valid non-empty JSON array
10. each element in `questionIdListAllowed` must be a numeric positive integer
11. `makeActive` is read, defaulting to `false`
12. rate limit placeholder
13. idempotency placeholder

### Service behavior (`QuestionSetCreateService`)
1. checks whether a config with the same name already exists → throws if duplicate
2. validates `questionIdListAllowed` is non-empty
3. sanitizes and deduplicates question ids
4. derives `questionCountTarget = count(deduplicated ids)`
5. fetches question rows from DB to verify all ids actually exist
6. resolves `secretKey` from active config or default config (never from admin input)
7. inserts new row into `game_configs` with `is_active = false`
8. if `makeActive` is true: calls `deactivateAllGameConfigs()`, then `activateGameConfigFromId()`
9. returns safe response payload (no secret key exposed)

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `game_configs` → create new config row, and fetch active/default config for secret key
- `questions` → verify all selected question ids exist in DB

---

<a id="section-79-api-question-set-edit-current-state-planned-refactor"></a>
### 7.9 API: Question Set Edit *(now fully implemented with final contract)*

### URL
`POST /backend/api/v1/questionSetEdit.php`

### Purpose
Updates an existing question-set / game config identified by its `gameConfigId`.

### Authorization
Admin only.

### Implemented service contract
The API and service now implement the final clean contract:
- `uid` — required, authenticated admin user
- `gameConfigId` — required, identifies the config to edit
- `gameConfigName` — non-empty, max 100 chars, restricted character set; may rename the config
- `questionIdListAllowed` — JSON array of positive integer question ids, validated against DB
- `makeActive` — optional boolean-like flag; if true, deactivates all configs and activates this one
- `secretKey` is **not** accepted from admin; it is preserved internally from the existing config row
- `questionCountTarget` is **not** accepted from admin; it is derived as `count(deduplicated ids)`

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| gameConfigId | integer | Required positive integer |
| gameConfigName | string | Non-empty, max 100 chars, allowed chars: `A-Z a-z 0-9 space _ , - ( ) . &` |
| questionIdListAllowed | JSON string | Non-empty JSON array of positive integers |
| makeActive | string | Optional; `true`, `1`, or `yes` to activate; anything else treated as `false` |

### Example request
```json
{
  "method": "POST",
  "body": {
    "uid": 1,
    "gameConfigId": 2,
    "gameConfigName": "SpecialGame1Updated",
    "questionIdListAllowed": "[1, 2, 3, 4, 5, 6]",
    "makeActive": "true"
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| gameConfigId | integer | Positive integer |
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | array | Non-empty deduplicated integer list |
| isActive | boolean | `true` or `false` |
| isUpdated | boolean | `true` on success |
| error | string | Present only on failure |

### Example success response
```json
{
  "gameConfigId": 2,
  "gameConfigName": "SpecialGame1Updated",
  "questionCountTarget": 6,
  "questionIdListAllowed": [1, 2, 3, 4, 5, 6],
  "isActive": true,
  "isUpdated": true
}
```

### Internal checks in API boundary layer (in order)
1. request method must be POST
2. uid must be present and numeric
3. authenticate: uid must exist in `users`
4. authorize: user must have `admin` permission in `user_permissions`
5. `uid`, `gameConfigId`, `gameConfigName`, and `questionIdListAllowed` must all be present
6. uid must be positive
7. `gameConfigId` must be numeric and positive
8. `gameConfigName` and `questionIdListAllowed` must be non-empty
9. `gameConfigName` must be ≤ 100 chars
10. `gameConfigName` must match allowed character regex
11. `questionIdListAllowed` must decode to a valid non-empty JSON array
12. each element must be a numeric positive integer
13. `makeActive` is read, defaulting to `false`
14. rate limit placeholder
15. idempotency placeholder

### Service behavior (`QuestionSetEditService`)
1. fetches existing config by `gameConfigId` → throws if not found
2. checks for name collision — another config with same name but different id → throws if collision
3. sanitizes and deduplicates question ids
4. derives `questionCountTarget = count(deduplicated ids)`
5. verifies all selected question ids exist in DB
6. preserves `is_active` state from current config unless `makeActive` is true
7. if `makeActive` is true: calls `deactivateAllGameConfigs()`, sets `finalIsActive = true`
8. calls `updateGameConfigFromId()` which updates `game_config_name`, `question_count_target`, `question_id_list_allowed_json`, `is_active` — secret key is **not** changed
9. returns safe response payload (no secret key exposed)

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `game_configs` → load existing config, update it, manage active state
- `questions` → verify all selected question ids exist in DB

---

<a id="section-8-proposed-new-admin-apis-for-manager-approval"></a>
## 8. New admin APIs — now implemented

These APIs were previously proposed. They are now fully implemented.

---

<a id="section-81-proposed-api-question-show"></a>
### 8.1 API: Question Show

### URL
`GET /backend/api/v1/questionShow.php`

### Purpose
Return paginated question list for admin selection UI.
Used when admin is creating or editing a question set.

### Authorization
Admin only.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| cursor | integer | Optional; last-seen question id for cursor pagination; defaults to `0` |
| limit | integer | Optional positive integer; defaults to `5`; max is `20` |

### Example request
```json
{
  "method": "GET",
  "query": {
    "uid": 1,
    "cursor": 5,
    "limit": 5
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| questions | array | Paginated question list |
| questions[].questionId | integer | Positive integer |
| questions[].questionText | string | Non-empty string |
| questions[].questionType | string | Existing question type |
| questions[].answerOptions | array | Non-empty answer option list |
| questions[].answerOptions[].id | integer | Positive integer |
| questions[].answerOptions[].text | string | Non-empty string |
| questions[].answerOptions[].type | string | Existing option type |
| nextCursor | integer or null | Cursor for next page; `null` when no more pages |
| hasMore | boolean | `true` or `false` |
| error | string | Present only on failure |

### Example success response
```json
{
  "questions": [
    {
      "questionId": 6,
      "questionText": "What is the capital of India?",
      "questionType": "mcq",
      "answerOptions": [
        { "id": 14, "text": "Delhi", "type": "mcq" },
        { "id": 15, "text": "Agra", "type": "mcq" },
        { "id": 16, "text": "Patna", "type": "mcq" },
        { "id": 17, "text": "Pune", "type": "mcq" }
      ]
    }
  ],
  "nextCursor": 6,
  "hasMore": true
}
```

### Internal checks in API boundary layer (in order)
1. request method must be GET
2. uid must be present and numeric
3. uid must be positive
4. cursor must be numeric (defaults to `0`)
5. limit must be numeric (defaults to `5`)
6. cursor must not be negative
7. limit must be between `1` and `20` inclusive
8. authenticate: uid must exist in `users`
9. authorize: user must have `admin` permission in `user_permissions`
10. rate-limit placeholder
11. idempotency placeholder

### Service behavior (`QuestionShowService`)
1. calls `getQuestionPageAfterId($cursor, $limit + 1)` — fetches `limit + 1` rows to detect next page
2. if result count > limit: sets `hasMore = true`, trims to `limit` rows
3. collects all question ids from the current page
4. fetches all answer options for those question ids in one query
5. builds an answer option map keyed by `question_id`
6. assembles questions response array, attaching answer options per question
7. sets `nextCursor` to the last question id in the page when `hasMore` is true, else `null`
8. returns `{ questions, nextCursor, hasMore }`

Note: `isCorrect` is **not exposed** in the question show response — only `id`, `text`, `type` per option.

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `questions` → question master list, cursor-paginated by id
- `answer_options` → option details fetched for the current page questions

---

<a id="section-82-proposed-api-question-set-show--game-config-show"></a>
### 8.2 API: Question Set Show / Game Config Show

### URL
`GET /backend/api/v1/questionSetShow.php`

### Purpose
Show existing configs to admin — used as entry point for edit-selection workflow.

### Authorization
Admin only.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| cursor | integer | Optional; last-seen config id for cursor pagination; defaults to `0` |
| limit | integer | Optional positive integer; defaults to `5`; max is `20` |

### Example request
```json
{
  "method": "GET",
  "query": {
    "uid": 1,
    "cursor": 0,
    "limit": 5
  }
}
```

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| gameConfigs | array | Paginated config list |
| gameConfigs[].id | integer | Positive integer |
| gameConfigs[].gameConfigName | string | Non-empty string |
| gameConfigs[].questionCountTarget | integer | Positive integer |
| gameConfigs[].questionIdListAllowed | array | Integer list |
| gameConfigs[].isActive | boolean | `true` or `false` |
| gameConfigs[].createdAt | string | Valid datetime string |
| gameConfigs[].updatedAt | string | Valid datetime string |
| nextCursor | integer or null | Cursor for next page; `null` when no more pages |
| hasMore | boolean | `true` or `false` |
| error | string | Present only on failure |

Note: `secret_key` is **never** included in the response.

### Example success response
```json
{
  "gameConfigs": [
    {
      "id": 1,
      "gameConfigName": "default_quiz",
      "questionCountTarget": 5,
      "questionIdListAllowed": [1, 2, 3, 4, 5],
      "isActive": true,
      "createdAt": "2026-04-20 14:30:00",
      "updatedAt": "2026-04-20 14:31:00"
    },
    {
      "id": 2,
      "gameConfigName": "SpecialGame1",
      "questionCountTarget": 7,
      "questionIdListAllowed": [1, 2, 3, 4, 5, 6, 7],
      "isActive": false,
      "createdAt": "2026-04-20 14:35:00",
      "updatedAt": "2026-04-20 14:35:00"
    }
  ],
  "nextCursor": 2,
  "hasMore": false
}
```

### Internal checks in API boundary layer (in order)
1. request method must be GET
2. uid must be present and numeric
3. uid must be positive
4. cursor must be numeric (defaults to `0`)
5. limit must be numeric (defaults to `5`)
6. cursor must not be negative
7. limit must be between `1` and `20` inclusive
8. authenticate: uid must exist in `users`
9. authorize: user must have `admin` permission in `user_permissions`
10. rate-limit placeholder
11. idempotency placeholder

### Service behavior (`QuestionSetShowService`)
1. calls `getGameConfigsPageAfterId($cursor, $limit + 1)` — fetches `limit + 1` rows to detect next page
2. if result count > limit: sets `hasMore = true`, trims to `limit` rows
3. builds config response array with: `id`, `gameConfigName`, `questionCountTarget`, `questionIdListAllowed`, `isActive`, `createdAt`, `updatedAt`
4. sets `nextCursor` to the last config id when `hasMore` is true, else `null`
5. returns `{ gameConfigs, nextCursor, hasMore }`

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `game_configs` → list existing question sets/configs, cursor-paginated by id

---

<a id="section-9-final-desired-admin-workflows"></a>
## 9. Final desired admin workflows

<a id="section-91-create-question-set-flow"></a>
### 9.1 Create question-set flow

### Frontend flow
1. Admin clicks **Create Question Set**.
2. Frontend loads page `questionSetCreate.html`.
3. Frontend calls `questionShow.php`.
4. Backend returns paginated questions + answer options.
5. Admin navigates pages using cursor pagination *(5 per page for now)*.
6. Admin selects question checkboxes.
7. Frontend collects selected question ids.
8. Frontend derives `questionCountTarget = selectedQuestionIds.length`.
9. A sensible upper bound should be kept, such as **50 or 100 questions max**.
10. Admin enters only:
   - config name
11. Config-name validation should be user-friendly:
   - bounded length, such as 50 to 100 characters
   - dangerous/special characters should be restricted
   - HTML-like content should be blocked
12. Frontend calls `questionSetCreate.php`.
13. Backend:
   - validates admin
   - validates config name uniqueness
   - validates question ids exist
   - internally copies secret key from backend-owned source config/default source
   - inserts new `game_configs` row
14. Response returns success payload.

### Backend boundary checks
The API boundary layer for `questionSetCreate.php` should perform these checks in order:
1. request method must be correct
2. uid must be present
3. uid must be numeric
4. uid must be positive
5. authenticated user must exist in `users`
6. authorized user must have `admin` permission in `user_permissions`
7. config name must be present and non-empty
8. selected question ids payload must be present and valid JSON array
9. rate-limit and idempotency placeholder checks

### Service layer responsibilities
The service layer for `QuestionSetCreateService` should:
1. sanitize config name
2. sanitize and deduplicate selected question ids
3. verify all question ids exist in `questions`
4. derive `questionCountTarget` from selected ids
5. validate that final question count is valid
6. obtain secret key from backend-controlled source config
7. decide whether config should be active immediately
8. create the new config row in `game_configs`
9. return only safe response fields back to API boundary

### Important rule
Secret key must **never** be shown to admin in UI.
It is internal-only.

### API-side note
The secret-key handling requirement belongs to backend validation and internal logic, not to the visible frontend workflow.

---

<a id="section-92-edit-question-set-flow"></a>
### 9.2 Edit question-set flow

### Frontend flow
1. Admin clicks **Edit Question Set**.
2. Frontend loads page `questionSetEdit.html`.
3. Frontend calls `questionSetShow.php`.
4. Backend returns all configs, preferably paginated.
5. Admin selects one config to edit.
6. Frontend loads:
   - existing config fields
   - selected question ids from that config
   - paginated question options using `questionShow.php`
7. Frontend marks already-selected questions as checked.
8. Admin can:
   - rename config
   - add/remove selected questions
   - decide whether this config should become active
9. For cleaner UX, the frontend should minimize manual inputs and derive what it can automatically.
10. Date fields and extra metadata can be exposed later as a future enhancement if useful.
11. Frontend derives updated `questionCountTarget`.
12. Frontend calls `questionSetEdit.php`.
13. Backend:
   - validates admin
   - validates edited config exists
   - validates all question ids exist
   - updates config row
   - if active requested:
     - deactivates all configs
     - activates selected config only
14. Response returns success payload.

### Backend boundary checks
The API boundary layer for `questionSetEdit.php` should perform these checks in order:
1. request method must be correct
2. uid must be present
3. uid must be numeric
4. uid must be positive
5. authenticated user must exist in `users`
6. authorized user must have `admin` permission in `user_permissions`
7. target config id or editable config identity must be present
8. config name must be present and valid
9. selected question ids payload must be present and valid JSON array
10. active-toggle value, if present, must be valid boolean-like input
11. rate-limit and idempotency placeholder checks

### Service layer responsibilities
The service layer for `QuestionSetEditService` should:
1. load the existing config from `game_configs`
2. verify that config exists
3. sanitize new config name
4. sanitize and deduplicate selected question ids
5. verify all selected question ids exist in `questions`
6. derive `questionCountTarget` from selected ids
7. preserve or internally reuse secret key
8. update config row safely
9. if requested active state is true:
   - deactivate all existing configs
   - activate this config only
10. return only safe response fields back to API boundary

---

<a id="section-93-question-show-workflow-final-desired-admin-workflow"></a>
### 9.3 Question show workflow

### Frontend flow
1. Admin opens either `questionSetCreate.html` or `questionSetEdit.html`.
2. Frontend sends request to `questionShow.php` with:
   - `uid`
   - `cursor` (optional)
   - `limit` (optional, default 5)
3. Backend returns a paginated question list.
4. Frontend renders each question with:
   - question text
   - question type
   - answer options
   - checkbox for selection
5. Admin selects or deselects questions.
6. Frontend stores selected question ids across pages.
7. Admin clicks next or previous page.
8. Frontend uses returned cursor values to continue browsing.
9. On edit flow, already-selected questions are shown pre-checked.

### Backend boundary checks
The API boundary layer for `questionShow.php` should perform these checks in order:
1. request method must be correct
2. uid must be present
3. uid must be numeric
4. uid must be positive
5. authenticated user must exist in `users`
6. authorized user must have `admin` permission in `user_permissions`
7. cursor, if present, must be numeric and valid
8. limit, if present, must be numeric, positive, and within bounded max
9. rate-limit and idempotency placeholder checks

### Service layer responsibilities
The service layer for `QuestionShowService` should:
1. read the paginated question list from `questions`
2. apply cursor-based pagination ordering consistently
3. fetch answer options from `answer_options` for the current page questions
4. shape response data so frontend does not need DB-specific knowledge
5. return:
   - question metadata
   - answer option metadata
   - `nextCursor`
   - `hasMore`

### Why this API is needed
Without this API, admin would need to manually type question ids or raw question content while creating/editing sets.
That is error-prone and not scalable.
This API makes the admin workflow UI-driven and safer.

---

<a id="section-94-question-set-show-workflow-final-desired-admin-workflow"></a>
### 9.4 Question-set show workflow

### Frontend flow
1. Admin opens `questionSetEdit.html`.
2. Frontend sends request to `questionSetShow.php` with:
   - `uid`
   - `cursor` (optional)
   - `limit` (optional)
3. Backend returns paginated config rows.
4. Frontend renders each config with:
   - config name
   - question count target
   - selected question ids summary
   - active/inactive state
   - created/updated metadata if needed
5. Admin selects which config to edit.
6. Frontend uses selected config data to prefill edit page.
7. Frontend then loads `questionShow.php` so admin can change selected question set composition.

### Backend boundary checks
The API boundary layer for `questionSetShow.php` should perform these checks in order:
1. request method must be correct
2. uid must be present
3. uid must be numeric
4. uid must be positive
5. authenticated user must exist in `users`
6. authorized user must have `admin` permission in `user_permissions`
7. cursor, if present, must be numeric and valid
8. limit, if present, must be numeric, positive, and within bounded max
9. rate-limit and idempotency placeholder checks

### Service layer responsibilities
The service layer for `QuestionSetShowService` should:
1. read paginated config rows from `game_configs`
2. include fields needed for admin display only
3. not expose secret key in response
4. return config metadata in frontend-friendly structure
5. return:
   - config id
   - config name
   - question count target
   - selected question ids
   - active/inactive state
   - timestamps if needed
   - `nextCursor`
   - `hasMore`

### Why this API is needed
Without this API, admin has no clean way to discover existing question sets before editing them.
This API becomes the entry point for controlled question-set editing.

---

<a id="section-10-recommended-final-contracts-for-question-set-apis"></a>
## 10. Implemented final contracts for question-set APIs

<a id="section-101-recommended-final-create-api-contract"></a>
### 10.1 Implemented final create API contract

### URL
`POST /backend/api/v1/questionSetCreate.php`

### Request body
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| gameConfigName | string | Non-empty validated config name |
| questionIdListAllowed | JSON array | Non-empty integer list |
| makeActive | boolean-like | Optional `true/false`, `1/0`, or omitted |

### Example request
```json
{
  "method": "POST",
  "body": {
    "uid": 1,
    "gameConfigName": "SpecialGame1",
    "questionIdListAllowed": [1, 2, 3, 4, 5, 6, 7],
    "makeActive": false
  }
}
```

### Derived internally
- `questionCountTarget = count(questionIdListAllowed)`
- `secretKey` copied from current active/default config
- `isActive` optional default false

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| gameConfigId | integer | Positive integer |
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | array | Integer list |
| isActive | boolean | `true` or `false` |
| isCreated | boolean | `true` on success |
| error | string | Present only on failure |

### Example response
```json
{
  "gameConfigId": 3,
  "gameConfigName": "SpecialGame1",
  "questionCountTarget": 7,
  "questionIdListAllowed": [1, 2, 3, 4, 5, 6, 7],
  "isActive": false,
  "isCreated": true
}
```

---

<a id="section-102-recommended-final-edit-api-contract"></a>
### 10.2 Implemented final edit API contract

### URL
`POST /backend/api/v1/questionSetEdit.php`

### Request body
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| gameConfigId | integer | Required positive integer |
| gameConfigName | string | Non-empty validated config name |
| questionIdListAllowed | JSON array | Non-empty integer list |
| makeActive | boolean-like | Optional `true/false`, `1/0`, or omitted |

### Example request
```json
{
  "method": "POST",
  "body": {
    "uid": 1,
    "gameConfigId": 2,
    "gameConfigName": "SpecialGame1Updated",
    "questionIdListAllowed": [1, 2, 3, 4, 5, 6],
    "makeActive": true
  }
}
```

### Derived internally
- `questionCountTarget = count(questionIdListAllowed)`
- preserve or internally reuse secret key

### Response parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| gameConfigId | integer | Positive integer |
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | array | Integer list |
| isActive | boolean | `true` or `false` |
| isUpdated | boolean | `true` on success |
| error | string | Present only on failure |

### Example response
```json
{
  "gameConfigId": 2,
  "gameConfigName": "SpecialGame1Updated",
  "questionCountTarget": 6,
  "questionIdListAllowed": [1, 2, 3, 4, 5, 6],
  "isActive": true,
  "isUpdated": true
}
```

---

<a id="section-11-flowchart-style-text-diagrams"></a>
## 11. Flowchart-style text diagrams

<a id="section-111-main-application-flow"></a>
### 11.1 Main application flow

```text
User opens frontend page
    -> frontend TS/JS handles event
    -> fetch() call to backend/api/v1/*.php
    -> API boundary validates/authenticates/authorizes
    -> API calls service class
    -> Service calls repository classes
    -> Repository uses query + mapper + ormManager
    -> ormManager uses dbManager/dbConnect
    -> MySQL returns rows
    -> mapper converts rows to entities
    -> service builds response array
    -> API returns JSON
    -> frontend updates UI / cookies / redirects
```

<a id="section-112-quiz-flow"></a>
### 11.2 Quiz flow

```text
quiz.html loads
    -> quizLoad.php
        -> authenticate user
        -> authorize by permission
        -> QuizLoadService
            -> check user_progress_states
            -> if none, create from active game config
            -> load current question + options
        -> return question payload
    -> user selects answer
    -> quizSubmit.php
        -> validate uid + answerOptionId
        -> QuizSubmitService
            -> validate current state
            -> check correctness
            -> update progress state
            -> if last question, mark complete
        -> return next step
    -> if complete
        -> result.html
        -> quizResultShow.php
        -> QuizResultShowService
        -> return score/result summary
```

<a id="section-113-admin-create-question-set-flow-proposed"></a>
### 11.3 Admin create question-set flow

```text
Admin opens questionSetCreate.html
    -> questionShow.php
        -> admin auth check
        -> return paginated questions + answer options
    -> admin selects questions across pages
    -> frontend stores selected ids
    -> frontend derives questionCountTarget
    -> admin enters config name
    -> questionSetCreate.php
        -> admin auth check
        -> validate config name + question ids
        -> QuestionSetCreateService
            -> verify all question ids exist
            -> copy secret key internally
            -> create game config row
        -> return created config metadata
```

<a id="section-114-admin-edit-question-set-flow-proposed"></a>
### 11.4 Admin edit question-set flow

```text
Admin opens questionSetEdit.html
    -> questionSetShow.php
        -> admin auth check
        -> return existing configs
    -> admin selects one config
    -> frontend loads config details
    -> questionShow.php
        -> return paginated question browser
    -> frontend marks already selected question ids
    -> admin edits selection / name / active state
    -> questionSetEdit.php
        -> admin auth check
        -> validate config exists
        -> QuestionSetEditService
            -> verify question ids exist
            -> update config row
            -> if makeActive=true
                -> deactivate all configs
                -> activate chosen config
        -> return updated config metadata
```

<a id="section-115-question-show-api-flow-proposed"></a>
### 11.5 Question show API flow

```text
Admin opens create/edit question-set page
    -> frontend requests questionShow.php with uid + cursor + limit
    -> API boundary authenticates uid
    -> API boundary checks admin permission
    -> request params are validated
    -> QuestionShowService fetches paginated question rows
    -> service loads answer options for each question
    -> response includes questions + options + nextCursor + hasMore
    -> frontend renders checkbox-based selection UI
    -> admin can move to next page and keep selected question ids in frontend state
```

<a id="section-116-question-set-show-api-flow-proposed"></a>
### 11.6 Question-set show API flow

```text
Admin opens questionSetEdit page
    -> frontend requests questionSetShow.php with uid + cursor + limit
    -> API boundary authenticates uid
    -> API boundary checks admin permission
    -> request params are validated
    -> QuestionSetShowService fetches paginated game config rows
    -> response includes config metadata + active state + nextCursor + hasMore
    -> frontend shows selectable config list
    -> admin chooses one config for editing
    -> frontend then loads questionShow.php to let admin update selected questions
```

---

<a id="section-12-api-summary-table"></a>
## 12. API summary table

| API | Method | Role Access | Main Tables Used | Status | Purpose |
|---|---|---|---|---|---|
| `loginGuest.php` | POST | Public | `users`, `user_permissions` | Implemented | Create guest identity |
| `loginUser.php` | POST | Public | `users`, `user_permissions`, `game_configs` | Implemented | Registered login |
| `registerUser.php` | POST | Public | `users`, `user_permissions`, `game_configs` | Implemented | Register / upgrade guest |
| `quizLoad.php` | GET | Guest/User/Admin | `users`, `user_permissions`, `user_progress_states`, `game_configs`, `questions`, `answer_options` | Implemented | Load current quiz question |
| `quizSubmit.php` | POST | Guest/User/Admin | `users`, `user_permissions`, `user_progress_states`, `questions`, `answer_options` | Implemented | Submit answer |
| `quizResultShow.php` | GET | Guest/User/Admin | `users`, `user_permissions`, `user_progress_states` | Implemented | Show final result |
| `questionAdd.php` | POST | Admin only | `users`, `user_permissions`, `questions`, `answer_options` | Implemented | Create question + options |
| `questionSetCreate.php` | POST | Admin only | `users`, `user_permissions`, `game_configs`, `questions` | Implemented (final contract) | Create config/question set |
| `questionSetEdit.php` | POST | Admin only | `users`, `user_permissions`, `game_configs`, `questions` | Implemented (final contract) | Edit config/question set |
| `questionShow.php` | GET | Admin only | `users`, `user_permissions`, `questions`, `answer_options` | Implemented | Paginated question browser |
| `questionSetShow.php` | GET | Admin only | `users`, `user_permissions`, `game_configs` | Implemented | List configs for edit |

---

<a id="section-13-key-implementation-observations-for-manager-review"></a>
## 13. Key implementation observations for manager review

<a id="section-131-what-is-already-strong"></a>
### 13.1 What is already strong
- Clear API boundary pattern in all current PHP APIs
- Good separation between API layer and service layer
- Repositories/queries/mappers give structured DB access
- Quiz state is persisted in DB cleanly through `user_progress_states`
- Password hashing flow uses secret key + password hash verification
- Role-based admin gating exists and is reused consistently in all admin APIs
- All admin APIs follow the same 9-step boundary pattern (authenticate → authorize → validate/sanitize → ratelimit → idempotency → delegate → respond → auditlog → try/catch)
- `DBManager.explainQuery()` is present for future DB query analysis
- Secret key is fully backend-controlled — not exposed in any API response or admin form
- Active config management is centralized with `deactivateAllGameConfigs()` + `activateGameConfigFromId()`
- Cursor-based pagination is consistently implemented in both `questionShow` and `questionSetShow` with `limit + 1` look-ahead pattern

<a id="section-132-what-still-needs-refinement-for-question-set-feature"></a>
### 13.2 Current status of the question-set feature

All major planned APIs for the admin question-set management workflow are now implemented:
- `questionShow.php` + `QuestionShowService` — paginated question browser with answer options
- `questionSetShow.php` + `QuestionSetShowService` — paginated config browser
- `questionSetCreate.php` + `QuestionSetCreateService` — creates config using final contract (no manual secret key, no manual count target, supports `makeActive`)
- `questionSetEdit.php` + `QuestionSetEditService` — edits config by id using final contract (same clean model)

Remaining minor areas for future refinement:
- Frontend pages (`questionSetCreate.html`, `questionSetEdit.html`) can be connected to the new paginated question browser using `questionShow.php`
- Frontend checkbox selection UX for question-set management is not yet fully wired to the implemented APIs
- Stronger uniqueness enforcement for email could be added at DB level in addition to application-level check

<a id="section-133-recommended-implementation-sequence"></a>
### 13.3 Completed implementation sequence

1. ✅ DB support for active config and timestamps (`is_active`, `created_at`, `updated_at` in `game_configs`)
2. ✅ `GameConfig` repository/query/entity methods (`getGameConfigsPageAfterId`, `createGameConfig`, `updateGameConfigFromId`, `deactivateAllGameConfigs`, `activateGameConfigFromId`)
3. ✅ `questionShow.php` + `QuestionShowService`
4. ✅ `questionSetShow.php` + `QuestionSetShowService`
5. ✅ `questionSetCreate.php` + `QuestionSetCreateService` aligned to final contract
6. ✅ `questionSetEdit.php` + `QuestionSetEditService` aligned to final contract
7. ⬜ Frontend create/edit pages full wiring to implemented APIs (pending)
8. ✅ Quiz load relies on active config

---

<a id="section-14-future-improvements--enhancements"></a>
## 14. Future improvements / enhancements

These are useful improvements, but they are not mandatory for the current implementation approval.

<a id="section-141-better-toggle-ux-for-is_active"></a>
### 14.1 Better toggle UX for `is_active`
Instead of asking the admin for raw active/inactive inputs, the frontend can provide:
- toggle button
- radio-style current selection control
- one-click “Make Current Set Active” action

<a id="section-142-more-metadata-fields-for-configs"></a>
### 14.2 More metadata fields for configs
Possible future additions:
- display-friendly created date in UI
- default activation date
- optional notes or description for config
- environment tags if needed later

<a id="section-143-stronger-naming-constraints-for-configs"></a>
### 14.3 Naming constraints for configs — already implemented
Backend validation for config names is already in place:
- **Length bound**: max 100 characters (`mb_strlen` check + `VARCHAR(100)` DB column)
- **Allowed characters**: alphanumeric, space, `_`, `,`, `-`, `(`, `)`, `.`, `&` — regex enforced
- **HTML/script injection blocked**: `<`, `>`, `"`, `'`, `;` are all excluded from the allowed set
- This validation is active in both `questionSetCreate.php` and `questionSetEdit.php`

Future refinement could add:
- stricter normalization (e.g. trim repeated spaces)
- case-insensitive uniqueness checks

<a id="section-144-better-pagination-controls"></a>
### 14.4 Better pagination controls
Current plan is cursor pagination with small page size like 5.
Future enhancement can include:
- page summary text
- selected question count badge
- persisted selection state more visibly in UI

<a id="section-145-config-activation-by-date-window"></a>
### 14.5 Config activation by date window
A useful future enhancement is to let admin choose a **start date** and **end date** for when a game config should be active.

This would reduce the need for admin to come back manually and change active config at the exact required time.

Possible future behavior:
- admin creates or edits a config
- admin optionally sets:
  - activation start date/time
  - activation end date/time
- backend stores these fields in `game_configs`
- quiz load checks current time against active window
- the correct config becomes active automatically based on configured schedule

Possible future DB additions:
- `active_start_at`
- `active_end_at`
- `activation_mode` such as manual vs scheduled

Possible future UX behavior:
- default values can be prefilled for convenience
- admin can still choose manual activation if date-window activation is not needed
- validation should ensure start date is before end date

This is a **future improvement only**, not part of the current required implementation.

---

<a id="section-15-final-approval-oriented-summary"></a>
## 15. Final approval-oriented summary

The project has a strong layered architecture and a fully working end-to-end quiz flow.

The current working system supports:
- guest login
- registered login
- registration / guest upgrade
- quiz play
- quiz scoring and results
- admin question addition
- admin question-set creation (final contract, no raw secret key, `makeActive` support)
- admin question-set editing (final contract, by config id, `makeActive` support)
- paginated question browser for admin (`questionShow.php`)
- paginated question-set browser for admin (`questionSetShow.php`)

The **admin-driven question-set management workflow** is now fully implemented at the backend level:
- admin can browse questions page by page using cursor pagination
- admin can create named question sets from selected question ids
- admin can edit existing question sets by config id
- admin can activate a specific question set which deactivates all others
- quiz content shown to all users is driven by whichever `game_configs` row has `is_active = TRUE`

Security model is enforced:
- `secret_key` is never exposed to admin via any API response
- only admin-permissioned users can access question-set management APIs
- config name is validated for length (≤ 100 chars) and character safety (no HTML / injection)
- question ids are validated against the DB on every create/edit

The remaining work is primarily frontend wiring:
- connecting `questionSetCreate.html` and `questionSetEdit.html` to the paginated question selection UIs that use `questionShow.php`

The backend structure remains:
- frontend: fetch-based API calls
- backend: API-boundary + service-layer driven
- repositories: DB access abstraction
- game config: central control point for live quiz content
