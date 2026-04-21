# QuizGameWithAuth — End-to-End API and Flow Document

## 1. Purpose of this document

This document explains the complete current end-to-end flow of the **QuizGameWithAuth** project, from frontend page actions to backend APIs, services, repositories, queries, mappers, ORM layer, and database tables.

It also documents the **proposed question-set workflow** for review, including the additional APIs needed for:
- Question browsing for admin
- Question-set browsing for admin
- Question-set creation
- Question-set editing

The goal is to make the architecture, request flow, validations, permissions, database usage, and proposed API evolution crystal clear before further implementation work starts.

This document is written as a **technical README-style design document** so it can be shared for review and approval.

---

# 2. High-level architecture

## 2.1 Layers in the project

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

# 3. Global end-to-end application flow

## 3.1 Current user journey

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

Partially implemented / planned:
- question-set create/edit
- question show API
- question-set show API

---

# 4. Current frontend flow

## 4.1 Shared auth helper

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

## 4.2 Frontend pages and API calls

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
Planned to call:
- `questionShow.php` *(proposed)*
- `questionSetCreate.php`

### `questionSetEdit.html` + `questionSetEdit.ts`
Planned to call:
- `questionSetShow.php` *(proposed)*
- `questionShow.php` *(proposed)*
- `questionSetEdit.php`

---

# 5. Current database tables and their purpose

## 5.1 `users`
Stores user identity.

Used by:
- guest login
- registered login
- registration
- authentication checks for admin APIs

Important columns:
- `uid`
- `user_id`
- `login_type`
- `email`
- `name`
- `password_hash`
- `created_at`
- `updated_at`

---

## 5.2 `user_permissions`
Stores authorization role.

Possible values:
- `guest`
- `user`
- `admin`

Used by:
- login flows
- quiz authorization
- admin-only APIs

---

## 5.3 `user_progress_states`
Stores active quiz state for each user.

Used by:
- quiz load
- quiz submit
- result show

Important columns:
- `uid`
- `score_current`
- `questions_done`
- `question_id_order_json`
- `question_id_order_index_current`
- `question_id_current`
- `is_complete`

This table is the main reason the quiz is **stateful in DB**, but **stateless over HTTP**.

---

## 5.4 `questions`
Stores question master data.

Used by:
- question add
- quiz load
- planned question show API
- planned question-set create/edit flow

---

## 5.5 `answer_options`
Stores answer choices for each question.

Used by:
- question add
- quiz load
- quiz submit
- planned question show API

---

## 5.6 `game_configs`
Stores question-set / config definitions.

### Current purpose
- determine which questions are allowed for quiz
- determine question count target
- provide secret key for password hashing flow
- determine active question set for quiz load

### Important columns
- `game_config_name`
- `question_count_target`
- `question_id_list_allowed_json`
- `secret_key`
- `is_active`
- `created_at`
- `updated_at`

### Used by
- register flow
- login flow
- quiz load
- question-set create/edit flows

### Current structural notes
From the current DB state:
- `secret_key` is already present
- `is_active` is already present
- `created_at` and `updated_at` are already present

### Input/constraint notes for future config naming
For user-friendly config name validation in future implementation, the document assumes:
- a max length bound should be applied, such as **50 to 100 characters**
- dangerous or unnecessary special characters should be restricted
- HTML-like content should not be accepted
- config naming should stay business-friendly and UI-friendly, not developer-centric

This is a **validation and UX recommendation**, not a current backend behavior commitment yet.

---

# 6. API versioning note

For documentation consistency and future-proofing, all APIs in this document are shown under a **v1 versioned path**.

### Standard form used in this document
`/backend/api/v1/<apiName>.php`

This is a documentation-level structure alignment so that all APIs follow one consistent versioning convention.

### Current logic impact
This is a **format and path organization change only** in this document.
It does **not** change the business logic of any existing API.

---

# 7. Current API-by-API documentation

---

## 7.1 API: Guest Login

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

## 7.2 API: Registered User Login

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

## 7.3 API: Register User

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

## 7.4 API: Quiz Load

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

## 7.5 API: Quiz Submit

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

## 7.6 API: Quiz Result Show

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

## 7.7 API: Question Add

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

## 7.8 API: Question Set Create *(current state, planned refactor)*

### URL
`POST /backend/api/v1/questionSetCreate.php`

### Purpose
Creates a new question-set / game config.

### Authorization
Admin only.

### Current implemented service contract
Current service expects:
- `gameConfigName`
- `questionCountTarget`
- `questionIdListAllowed`
- `secretKey`

### Request parameters *(current state)*
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | JSON array | Non-empty integer list |
| secretKey | string | Non-empty string |

### Example current-state request
```json
{
  "method": "POST",
  "body": {
    "uid": 1,
    "gameConfigName": "SpecialGame1",
    "questionCountTarget": 7,
    "questionIdListAllowed": [1, 2, 3, 4, 5, 6, 7],
    "secretKey": "neetikagoel12345"
  }
}
```

### Response parameters *(current state)*
| Parameter | Type of value | Valid value range |
|---|---|---|
| gameConfigId | integer | Positive integer |
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | array | Non-empty integer list |
| secretKey | string | Present currently, but should not be exposed in final design |
| isCreated | boolean | `true` on success |
| error | string | Present only on failure |

### Example current-state response
```json
{
  "gameConfigId": 2,
  "gameConfigName": "SpecialGame1",
  "questionCountTarget": 7,
  "questionIdListAllowed": [1, 2, 3, 4, 5, 6, 7],
  "secretKey": "neetikagoel12345",
  "isCreated": true
}
```

### Current intended business role
Creates a new row in `game_configs` for a new quiz configuration.

### Current gaps versus final desired workflow
The current code structure is not yet aligned with final UX because in final workflow:
- admin should not type raw secret key
- admin should select questions from paginated list
- question count target should be derived automatically from selected questions or constrained logically

### Correct final purpose
This API should ultimately receive a clean admin-selected question-set payload and create a new `game_configs` row.

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `game_configs` → create new config row
- `questions` → verify selected question ids exist

---

## 7.9 API: Question Set Edit *(current state, planned refactor)*

### URL
`POST /backend/api/v1/questionSetEdit.php`

### Purpose
Updates an existing question-set / game config.

### Authorization
Admin only.

### Current implemented service contract
Current service expects:
- `gameConfigName`
- `questionCountTarget`
- `questionIdListAllowed`
- `secretKey`

### Request parameters *(current state)*
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | JSON array | Non-empty integer list |
| secretKey | string | Non-empty string |

### Example current-state request
```json
{
  "method": "POST",
  "body": {
    "uid": 1,
    "gameConfigName": "SpecialGame1",
    "questionCountTarget": 6,
    "questionIdListAllowed": [1, 2, 3, 4, 5, 6],
    "secretKey": "neetikagoel12345"
  }
}
```

### Response parameters *(current state)*
| Parameter | Type of value | Valid value range |
|---|---|---|
| gameConfigName | string | Non-empty string |
| questionCountTarget | integer | Positive integer |
| questionIdListAllowed | array | Non-empty integer list |
| secretKey | string | Present currently, but should not be exposed in final design |
| isUpdated | boolean | `true` on success |
| error | string | Present only on failure |

### Example current-state response
```json
{
  "gameConfigName": "SpecialGame1",
  "questionCountTarget": 6,
  "questionIdListAllowed": [1, 2, 3, 4, 5, 6],
  "secretKey": "neetikagoel12345",
  "isUpdated": true
}
```

### Current gaps versus final desired workflow
Final workflow requires:
- select an existing config first
- edit by config id or original identity, not ambiguous rename-only flow
- optionally make config active
- preserve secret key from backend side
- support question selection through paginated question browser

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `game_configs` → update existing config row
- `questions` → verify updated selected question ids exist

---

# 8. Proposed new admin APIs for manager approval

These APIs are the correct additional APIs needed to support the desired admin workflow.

---

## 8.1 Proposed API: Question Show

### URL
`GET /backend/api/v1/questionShow.php`

### Purpose
Return paginated question list for admin selection UI.

### Why needed
Admin must be able to browse questions with answer options instead of manually typing question ids.

### Authorization
Admin only.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| cursor | integer | Optional last-seen question id for cursor pagination |
| limit | integer | Optional positive integer, default 5 |

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
| nextCursor | integer or null | Cursor for next page |
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

### Backend boundary checks
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
1. read the paginated question list from `questions`
2. apply cursor-based pagination ordering consistently
3. fetch answer options from `answer_options` for the current page questions
4. shape response data so frontend does not need DB-specific knowledge
5. return:
   - question metadata
   - answer option metadata
   - `nextCursor`
   - `hasMore`

### Internal flow
Frontend question-set page → `questionShow.php` → service fetches paginated questions + answer options → frontend renders checkbox list → admin selects questions

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `questions` → question master list
- `answer_options` → option details shown alongside each question

---

## 8.2 Proposed API: Question Set Show / Game Config Show

### URL
`GET /backend/api/v1/questionSetShow.php`

### Purpose
Show existing configs to admin for edit selection.

### Authorization
Admin only.

### Request parameters
| Parameter | Type of value | Valid value range |
|---|---|---|
| uid | integer | Required positive integer |
| cursor | integer | Optional config cursor for pagination |
| limit | integer | Optional positive integer, default 5 |

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
| nextCursor | integer or null | Cursor for next page |
| hasMore | boolean | `true` or `false` |
| error | string | Present only on failure |

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

### Backend boundary checks
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

### Why needed
Admin edit flow first needs to choose which config to edit.

### Database tables used and why
- `users` → authentication existence check
- `user_permissions` → admin authorization check
- `game_configs` → list existing question sets/configs

---

# 9. Final desired admin workflows

## 9.1 Create question-set flow

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

## 9.2 Edit question-set flow

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

## 9.3 Question show workflow *(final desired admin workflow)*

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

## 9.4 Question-set show workflow *(final desired admin workflow)*

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

# 10. Recommended final contracts for question-set APIs

## 10.1 Recommended final create API contract

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

## 10.2 Recommended final edit API contract

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

# 11. Flowchart-style text diagrams

## 11.1 Main application flow

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

## 11.2 Quiz flow

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

## 11.3 Admin create question-set flow *(proposed)*

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

## 11.4 Admin edit question-set flow *(proposed)*

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

## 11.5 Question show API flow *(proposed)*

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

## 11.6 Question-set show API flow *(proposed)*

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

# 12. API summary table

| API | Method | Role Access | Main Tables Used | Purpose |
|---|---|---:|---|---|
| `loginGuest.php` | POST | Public | `users`, `user_permissions` | Create guest identity |
| `loginUser.php` | POST | Public | `users`, `user_permissions`, `game_configs` | Registered login |
| `registerUser.php` | POST | Public | `users`, `user_permissions`, `game_configs` | Register / upgrade guest |
| `quizLoad.php` | GET | Guest/User/Admin | `users`, `user_permissions`, `user_progress_states`, `game_configs`, `questions`, `answer_options` | Load current quiz question |
| `quizSubmit.php` | POST | Guest/User/Admin | `users`, `user_permissions`, `user_progress_states`, `questions`, `answer_options` | Submit answer |
| `quizResultShow.php` | GET | Guest/User/Admin | `users`, `user_permissions`, `user_progress_states` | Show final result |
| `questionAdd.php` | POST | Admin only | `users`, `user_permissions`, `questions`, `answer_options` | Create question + options |
| `questionSetCreate.php` | POST | Admin only | `users`, `user_permissions`, `game_configs`, `questions` | Create config/question set |
| `questionSetEdit.php` | POST | Admin only | `users`, `user_permissions`, `game_configs`, `questions` | Edit config/question set |
| `questionShow.php` *(proposed)* | GET | Admin only | `users`, `user_permissions`, `questions`, `answer_options` | Paginated question browser |
| `questionSetShow.php` *(proposed)* | GET | Admin only | `users`, `user_permissions`, `game_configs` | List configs for edit |

---

# 13. Key implementation observations for manager review

## 13.1 What is already strong
- Clear API boundary pattern in all current PHP APIs
- Good separation between API layer and service layer
- Repositories/queries/mappers give structured DB access
- Quiz state is persisted in DB cleanly through `user_progress_states`
- Password hashing flow now uses secret key + password hash verification
- Role-based admin gating already exists and can be reused for new admin APIs

## 13.2 What still needs refinement for question-set feature
- `questionSetCreate.php` and `questionSetEdit.php` should move away from raw/manual input style
- Need dedicated APIs for question browsing and config browsing
- Secret key must not be exposed in admin response payloads or frontend forms
- Active config selection should be centralized so only one config is active at a time
- Existing `QuestionSetCreateService` and `QuestionSetEditService` should be reshaped around final request contracts

## 13.3 Recommended implementation sequence
1. Finalize DB support for active config and timestamps
2. Finalize `GameConfig` repository/query/entity methods
3. Build `questionShow.php` + service
4. Build `questionSetShow.php` + service
5. Refactor `questionSetCreate.php` + service to final contract
6. Refactor `questionSetEdit.php` + service to final contract
7. Update frontend create/edit pages and navbar admin links
8. Update quiz load to rely only on active config

---

# 14. Future improvements / enhancements

These are useful improvements, but they are not mandatory for the current implementation approval.

## 14.1 Better toggle UX for `is_active`
Instead of asking the admin for raw active/inactive inputs, the frontend can provide:
- toggle button
- radio-style current selection control
- one-click “Make Current Set Active” action

## 14.2 More metadata fields for configs
Possible future additions:
- display-friendly created date in UI
- default activation date
- optional notes or description for config
- environment tags if needed later

## 14.3 Stronger naming constraints for configs
Future backend validation can include:
- length bounds
- normalized naming
- restricted special characters
- HTML/content sanitization

## 14.4 Better pagination controls
Current plan is cursor pagination with small page size like 5.
Future enhancement can include:
- page summary text
- selected question count badge
- persisted selection state more visibly in UI

## 14.5 Config activation by date window
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

# 15. Final approval-oriented summary

The project already has a strong layered architecture and a working end-to-end quiz flow.

The current working system supports:
- guest login
- registered login
- registration / guest upgrade
- quiz play
- quiz scoring and results
- admin question addition

The next planned phase is the **admin-driven question-set management workflow**, where admins will:
- browse questions with pagination
- create named question sets from selected questions
- edit existing question sets
- choose which question set is active for the live quiz experience

This requires two new support APIs:
- `questionShow.php`
- `questionSetShow.php`

And it requires aligning the existing question-set create/edit APIs with the final UX and security model.

The resulting architecture will ensure that quiz content shown to all end users is controlled centrally through the currently active `game_configs` row, while the frontend remains simple and backend ownership of sensitive fields like secret key is preserved.

It also keeps the current code structure intact conceptually:
- frontend remains fetch-based
- backend remains API-boundary + service-layer driven
- repositories remain DB access abstraction
- game config becomes the central control point for live quiz content

This document therefore presents both:
- the **current implemented flow**, and
- the **proposed approved direction** for the question-set feature evolution

without changing the existing business logic for documentation-only structural improvements such as API versioning and contract presentation.

