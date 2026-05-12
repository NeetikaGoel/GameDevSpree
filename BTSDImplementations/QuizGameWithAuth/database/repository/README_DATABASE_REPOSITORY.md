# Database Repository Folder

This folder contains repository classes that expose application-friendly database methods.

## Purpose

Repositories hide SQL details and provide clear methods for backend services.

Instead of services dealing with SQL directly, services call repository methods like:
- get user by uid
- create guest user
- create question
- get active config
- update quiz progress

## Why repositories are useful

- service layer becomes cleaner
- database access stays centralized
- logic becomes easier to maintain
- future refactors are easier

## Common repository responsibilities

- assemble query + mapper + orm usage
- validate simple method-level conditions
- return entities or arrays to services
- insert/update records safely

## Examples in this folder

| Repository | Key methods |
|---|---|
| `UserRepository` | `getUserFromUid`, `getUserFromEmail`, `createGuestUser`, `createRegisteredUser`, `upgradeGuestToRegistered` |
| `UserPermissionRepository` | `getUserPermissionFromUid`, `createUserPermission`, `updateUserPermission` |
| `UserProgressStateRepository` | `getUserProgressStateFromUid`, `createUserProgressState`, `updateUserProgressState` |
| `QuestionRepository` | `getQuestionsFromQuestionIdListAllowed`, `createQuestion`, `getQuestionPageAfterId` |
| `AnswerOptionRepository` | `getAnswerOptionsFromQuestionIdList`, `createAnswerOption` |
| `GameConfigRepository` | `getGameConfigFromName`, `getGameConfigFromId`, `getActiveGameConfig`, `getGameConfigsPageAfterId`, `createGameConfig`, `updateGameConfigFromId`, `deactivateAllGameConfigs`, `activateGameConfigFromId` |

## Pattern

```text
Service
-> Repository method
-> Query class for SQL
-> Mapper for row conversion
-> OrmManager for execution
```
