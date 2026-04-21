import { authUidGet, authPermissionGroupGet, authNavbarUpdate } from "./auth.js";
const questionSetEditApiUrl = "../backend/api/v1/questionSetEdit.php";
const questionSetShowApiUrl = "../backend/api/v1/questionSetShow.php";
const questionShowApiUrl = "../backend/api/v1/questionShow.php";
let questionSetEditConfigCursorCurrent = 0;
let questionSetEditConfigCursorHistory = [];
let questionSetEditConfigNextCursor = null;
let questionSetEditQuestionCursorCurrent = 0;
let questionSetEditQuestionCursorHistory = [];
let questionSetEditQuestionNextCursor = null;
let questionSetEditSelectedGameConfigId = null;
let questionSetEditSelectedQuestionIdSet = new Set();
function questionSetEditMessageTextSet(message) {
    const questionSetEditMessageTextElement = document.getElementById("question-set-edit-message-text");
    if (questionSetEditMessageTextElement) {
        questionSetEditMessageTextElement.textContent = message;
    }
}
function questionSetEditAccessCheck() {
    const uidCurrent = authUidGet();
    const permissionGroupCurrent = authPermissionGroupGet();
    if (!uidCurrent) {
        questionSetEditMessageTextSet("You must be logged in to access question set edit.");
        return false;
    }
    if (permissionGroupCurrent !== "admin") {
        questionSetEditMessageTextSet("Only admin user can edit question sets.");
        return false;
    }
    return true;
}
function questionSetEditCountUpdate() {
    const questionSetEditCountInputElement = document.getElementById("question-set-edit-count-input");
    if (!questionSetEditCountInputElement) {
        return;
    }
    questionSetEditCountInputElement.value = String(questionSetEditSelectedQuestionIdSet.size);
}
function questionSetEditConfigSelectionFill(gameConfigCurrent) {
    const questionSetEditIdInputElement = document.getElementById("question-set-edit-id-input");
    const questionSetEditNameInputElement = document.getElementById("question-set-edit-name-input");
    const questionSetEditMakeActiveInputElement = document.getElementById("question-set-edit-make-active-input");
    if (!questionSetEditIdInputElement ||
        !questionSetEditNameInputElement ||
        !questionSetEditMakeActiveInputElement) {
        return;
    }
    questionSetEditSelectedGameConfigId = gameConfigCurrent.id;
    questionSetEditSelectedQuestionIdSet = new Set(gameConfigCurrent.questionIdListAllowed);
    questionSetEditIdInputElement.value = String(gameConfigCurrent.id);
    questionSetEditNameInputElement.value = gameConfigCurrent.gameConfigName;
    questionSetEditMakeActiveInputElement.checked = gameConfigCurrent.isActive;
    questionSetEditCountUpdate();
    questionSetEditQuestionRenderRefreshSelectionOnly();
}
function questionSetEditQuestionRenderRefreshSelectionOnly() {
    const questionCheckboxElementList = document.querySelectorAll('input[name="questionSetEditQuestionSelect"]');
    questionCheckboxElementList.forEach((questionCheckboxElement) => {
        const questionIdCurrent = parseInt(questionCheckboxElement.value, 10);
        if (!Number.isNaN(questionIdCurrent)) {
            questionCheckboxElement.checked = questionSetEditSelectedQuestionIdSet.has(questionIdCurrent);
        }
    });
    questionSetEditCountUpdate();
}
function questionSetEditConfigPaginationButtonsUpdate(hasMore) {
    const questionSetEditConfigPrevButtonElement = document.getElementById("question-set-edit-config-prev-button");
    const questionSetEditConfigNextButtonElement = document.getElementById("question-set-edit-config-next-button");
    if (questionSetEditConfigPrevButtonElement) {
        questionSetEditConfigPrevButtonElement.disabled = questionSetEditConfigCursorHistory.length === 0;
    }
    if (questionSetEditConfigNextButtonElement) {
        questionSetEditConfigNextButtonElement.disabled = !hasMore || questionSetEditConfigNextCursor === null;
    }
}
function questionSetEditQuestionPaginationButtonsUpdate(hasMore) {
    const questionSetEditQuestionPrevButtonElement = document.getElementById("question-set-edit-question-prev-button");
    const questionSetEditQuestionNextButtonElement = document.getElementById("question-set-edit-question-next-button");
    if (questionSetEditQuestionPrevButtonElement) {
        questionSetEditQuestionPrevButtonElement.disabled = questionSetEditQuestionCursorHistory.length === 0;
    }
    if (questionSetEditQuestionNextButtonElement) {
        questionSetEditQuestionNextButtonElement.disabled = !hasMore || questionSetEditQuestionNextCursor === null;
    }
}
function questionSetEditConfigListRender(gameConfigList) {
    const questionSetEditConfigListContainerElement = document.getElementById("question-set-edit-config-list-container");
    if (!questionSetEditConfigListContainerElement) {
        return;
    }
    questionSetEditConfigListContainerElement.innerHTML = "";
    if (gameConfigList.length === 0) {
        const emptyStateElement = document.createElement("div");
        emptyStateElement.className = "compact-info-box";
        emptyStateElement.textContent = "No question sets found.";
        questionSetEditConfigListContainerElement.appendChild(emptyStateElement);
        return;
    }
    for (const gameConfigCurrent of gameConfigList) {
        const configCardElement = document.createElement("div");
        configCardElement.className = "question-set-card";
        const configTitleElement = document.createElement("h1");
        configTitleElement.textContent = gameConfigCurrent.gameConfigName;
        const configInfoElement = document.createElement("p");
        configInfoElement.textContent =
            "Id: " +
                String(gameConfigCurrent.id) +
                " | Questions: " +
                String(gameConfigCurrent.questionCountTarget) +
                " | Active: " +
                (gameConfigCurrent.isActive ? "Yes" : "No");
        const configButtonElement = document.createElement("button");
        configButtonElement.type = "button";
        configButtonElement.textContent = "Select This Set";
        configButtonElement.addEventListener("click", () => {
            questionSetEditConfigSelectionFill(gameConfigCurrent);
            questionSetEditMessageTextSet("Question set loaded. You can now update it.");
        });
        configCardElement.appendChild(configTitleElement);
        configCardElement.appendChild(configInfoElement);
        configCardElement.appendChild(configButtonElement);
        questionSetEditConfigListContainerElement.appendChild(configCardElement);
    }
}
function questionSetEditQuestionListRender(questionList) {
    const questionSetEditQuestionListContainerElement = document.getElementById("question-set-edit-question-list-container");
    if (!questionSetEditQuestionListContainerElement) {
        return;
    }
    questionSetEditQuestionListContainerElement.innerHTML = "";
    if (questionList.length === 0) {
        const emptyStateElement = document.createElement("div");
        emptyStateElement.className = "compact-info-box";
        emptyStateElement.textContent = "No questions found.";
        questionSetEditQuestionListContainerElement.appendChild(emptyStateElement);
        return;
    }
    for (const questionCurrent of questionList) {
        const questionCardElement = document.createElement("div");
        questionCardElement.className = "question-set-card";
        const questionCheckboxWrapperElement = document.createElement("label");
        questionCheckboxWrapperElement.className = "question-set-checkbox-row";
        const questionCheckboxElement = document.createElement("input");
        questionCheckboxElement.type = "checkbox";
        questionCheckboxElement.name = "questionSetEditQuestionSelect";
        questionCheckboxElement.value = String(questionCurrent.questionId);
        questionCheckboxElement.checked = questionSetEditSelectedQuestionIdSet.has(questionCurrent.questionId);
        questionCheckboxElement.addEventListener("change", () => {
            if (questionCheckboxElement.checked) {
                questionSetEditSelectedQuestionIdSet.add(questionCurrent.questionId);
            }
            else {
                questionSetEditSelectedQuestionIdSet.delete(questionCurrent.questionId);
            }
            questionSetEditCountUpdate();
        });
        const questionHeadingElement = document.createElement("span");
        questionHeadingElement.textContent =
            "Question " +
                String(questionCurrent.questionId) +
                ": " +
                questionCurrent.questionText +
                " (" +
                questionCurrent.questionType +
                ")";
        questionCheckboxWrapperElement.appendChild(questionCheckboxElement);
        questionCheckboxWrapperElement.appendChild(questionHeadingElement);
        const answerOptionListElement = document.createElement("ul");
        answerOptionListElement.className = "question-set-answer-option-list";
        for (const answerOptionCurrent of questionCurrent.answerOptions) {
            const answerOptionItemElement = document.createElement("li");
            answerOptionItemElement.textContent = answerOptionCurrent.text + " (" + answerOptionCurrent.type + ")";
            answerOptionListElement.appendChild(answerOptionItemElement);
        }
        questionCardElement.appendChild(questionCheckboxWrapperElement);
        questionCardElement.appendChild(answerOptionListElement);
        questionSetEditQuestionListContainerElement.appendChild(questionCardElement);
    }
}
async function questionSetEditConfigPageLoad() {
    const uidCurrent = authUidGet();
    if (!uidCurrent) {
        return;
    }
    const response = await fetch(questionSetShowApiUrl +
        "?uid=" + encodeURIComponent(uidCurrent) +
        "&cursor=" + encodeURIComponent(String(questionSetEditConfigCursorCurrent)) +
        "&limit=5", {
        method: "GET"
    });
    const questionSetShowResponse = await response.json();
    if (questionSetShowResponse.error) {
        questionSetEditMessageTextSet(questionSetShowResponse.error);
        return;
    }
    questionSetEditConfigNextCursor = questionSetShowResponse.nextCursor;
    questionSetEditConfigListRender(questionSetShowResponse.gameConfigs);
    questionSetEditConfigPaginationButtonsUpdate(questionSetShowResponse.hasMore);
}
async function questionSetEditQuestionPageLoad() {
    const uidCurrent = authUidGet();
    if (!uidCurrent) {
        return;
    }
    const response = await fetch(questionShowApiUrl +
        "?uid=" + encodeURIComponent(uidCurrent) +
        "&cursor=" + encodeURIComponent(String(questionSetEditQuestionCursorCurrent)) +
        "&limit=5", {
        method: "GET"
    });
    const questionShowResponse = await response.json();
    if (questionShowResponse.error) {
        questionSetEditMessageTextSet(questionShowResponse.error);
        return;
    }
    questionSetEditQuestionNextCursor = questionShowResponse.nextCursor;
    questionSetEditQuestionListRender(questionShowResponse.questions);
    questionSetEditQuestionPaginationButtonsUpdate(questionShowResponse.hasMore);
}
async function questionSetEditSubmit(event) {
    event.preventDefault();
    if (questionSetEditAccessCheck() !== true) {
        return;
    }
    const uidCurrent = authUidGet();
    const questionSetEditIdInputElement = document.getElementById("question-set-edit-id-input");
    const questionSetEditNameInputElement = document.getElementById("question-set-edit-name-input");
    const questionSetEditMakeActiveInputElement = document.getElementById("question-set-edit-make-active-input");
    if (!uidCurrent ||
        !questionSetEditIdInputElement ||
        !questionSetEditNameInputElement ||
        !questionSetEditMakeActiveInputElement) {
        return;
    }
    const gameConfigIdRaw = questionSetEditIdInputElement.value.trim();
    const gameConfigName = questionSetEditNameInputElement.value.trim();
    const makeActive = questionSetEditMakeActiveInputElement.checked;
    if (gameConfigIdRaw === "" || questionSetEditSelectedGameConfigId === null) {
        questionSetEditMessageTextSet("Please select a question set first.");
        return;
    }
    if (gameConfigName === "") {
        questionSetEditMessageTextSet("Question set name is required.");
        return;
    }
    const questionIdListAllowed = Array.from(questionSetEditSelectedQuestionIdSet);
    if (questionIdListAllowed.length === 0) {
        questionSetEditMessageTextSet("Please select at least one question.");
        return;
    }
    questionSetEditMessageTextSet("Editing question set...");
    const questionSetEditFormData = new FormData();
    questionSetEditFormData.append("uid", uidCurrent);
    questionSetEditFormData.append("gameConfigId", gameConfigIdRaw);
    questionSetEditFormData.append("gameConfigName", gameConfigName);
    questionSetEditFormData.append("questionIdListAllowed", JSON.stringify(questionIdListAllowed));
    questionSetEditFormData.append("makeActive", makeActive ? "true" : "false");
    const response = await fetch(questionSetEditApiUrl, {
        method: "POST",
        body: questionSetEditFormData
    });
    const questionSetEditResponse = await response.json();
    if (questionSetEditResponse.error) {
        questionSetEditMessageTextSet(questionSetEditResponse.error);
        return;
    }
    questionSetEditMessageTextSet("Question set updated successfully for " + questionSetEditResponse.gameConfigName + ".");
    await questionSetEditConfigPageLoad();
}
function questionSetEditConfigPaginationInitialize() {
    const questionSetEditConfigPrevButtonElement = document.getElementById("question-set-edit-config-prev-button");
    const questionSetEditConfigNextButtonElement = document.getElementById("question-set-edit-config-next-button");
    if (questionSetEditConfigPrevButtonElement) {
        questionSetEditConfigPrevButtonElement.addEventListener("click", async () => {
            if (questionSetEditConfigCursorHistory.length === 0) {
                return;
            }
            const cursorPrevious = questionSetEditConfigCursorHistory.pop();
            if (cursorPrevious === undefined) {
                return;
            }
            questionSetEditConfigCursorCurrent = cursorPrevious;
            await questionSetEditConfigPageLoad();
        });
    }
    if (questionSetEditConfigNextButtonElement) {
        questionSetEditConfigNextButtonElement.addEventListener("click", async () => {
            if (questionSetEditConfigNextCursor === null) {
                return;
            }
            questionSetEditConfigCursorHistory.push(questionSetEditConfigCursorCurrent);
            questionSetEditConfigCursorCurrent = questionSetEditConfigNextCursor;
            await questionSetEditConfigPageLoad();
        });
    }
}
function questionSetEditQuestionPaginationInitialize() {
    const questionSetEditQuestionPrevButtonElement = document.getElementById("question-set-edit-question-prev-button");
    const questionSetEditQuestionNextButtonElement = document.getElementById("question-set-edit-question-next-button");
    if (questionSetEditQuestionPrevButtonElement) {
        questionSetEditQuestionPrevButtonElement.addEventListener("click", async () => {
            if (questionSetEditQuestionCursorHistory.length === 0) {
                return;
            }
            const cursorPrevious = questionSetEditQuestionCursorHistory.pop();
            if (cursorPrevious === undefined) {
                return;
            }
            questionSetEditQuestionCursorCurrent = cursorPrevious;
            await questionSetEditQuestionPageLoad();
        });
    }
    if (questionSetEditQuestionNextButtonElement) {
        questionSetEditQuestionNextButtonElement.addEventListener("click", async () => {
            if (questionSetEditQuestionNextCursor === null) {
                return;
            }
            questionSetEditQuestionCursorHistory.push(questionSetEditQuestionCursorCurrent);
            questionSetEditQuestionCursorCurrent = questionSetEditQuestionNextCursor;
            await questionSetEditQuestionPageLoad();
        });
    }
}
async function questionSetEditPageInitialize() {
    authNavbarUpdate();
    const questionSetEditFormElement = document.getElementById("question-set-edit-form");
    const questionSetEditConfigListContainerElement = document.getElementById("question-set-edit-config-list-container");
    const questionSetEditQuestionListContainerElement = document.getElementById("question-set-edit-question-list-container");
    if (!questionSetEditFormElement ||
        !questionSetEditConfigListContainerElement ||
        !questionSetEditQuestionListContainerElement) {
        return;
    }
    if (questionSetEditAccessCheck() !== true) {
        questionSetEditFormElement.style.display = "none";
        if (questionSetEditConfigListContainerElement) {
            questionSetEditConfigListContainerElement.style.display = "none";
        }
        if (questionSetEditQuestionListContainerElement) {
            questionSetEditQuestionListContainerElement.style.display = "none";
        }
        return;
    }
    questionSetEditFormElement.addEventListener("submit", questionSetEditSubmit);
    questionSetEditConfigPaginationInitialize();
    questionSetEditQuestionPaginationInitialize();
    await questionSetEditConfigPageLoad();
    await questionSetEditQuestionPageLoad();
}
questionSetEditPageInitialize();
