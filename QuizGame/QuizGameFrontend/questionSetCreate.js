import { authUidGet, authPermissionGroupGet, authNavbarUpdate } from "./auth.js";
const questionShowApiUrl = "../backend/api/v1/questionShow.php";
const questionSetCreateApiUrl = "../backend/api/v1/questionSetCreate.php";
const questionSetCreateSelectedQuestionIdSet = new Set();
const questionSetCreateCursorHistory = [0];
let questionSetCreateCurrentCursor = 0;
let questionSetCreateNextCursor = null;
function questionSetCreateMessageTextSet(message) {
    const questionSetCreateMessageTextElement = document.getElementById("question-set-create-message-text");
    if (questionSetCreateMessageTextElement) {
        questionSetCreateMessageTextElement.textContent = message;
    }
}
function questionSetCreateCountUpdate() {
    const questionSetCreateCountTextElement = document.getElementById("question-set-create-count-text");
    if (questionSetCreateCountTextElement) {
        questionSetCreateCountTextElement.textContent = String(questionSetCreateSelectedQuestionIdSet.size);
    }
}
function questionSetCreateAccessCheck() {
    const uidCurrent = authUidGet();
    const permissionGroupCurrent = authPermissionGroupGet();
    if (!uidCurrent) {
        questionSetCreateMessageTextSet("You must be logged in to access question set create.");
        return false;
    }
    if (permissionGroupCurrent !== "admin") {
        questionSetCreateMessageTextSet("Only admin user can create question sets.");
        return false;
    }
    return true;
}
function questionSetCreateQuestionSelectionToggle(questionId, isChecked) {
    if (isChecked === true) {
        questionSetCreateSelectedQuestionIdSet.add(questionId);
    }
    else {
        questionSetCreateSelectedQuestionIdSet.delete(questionId);
    }
    questionSetCreateCountUpdate();
}
function questionSetCreateQuestionListRender(questionList) {
    const questionSetCreateQuestionListContainerElement = document.getElementById("question-set-create-question-list-container");
    if (!questionSetCreateQuestionListContainerElement) {
        return;
    }
    questionSetCreateQuestionListContainerElement.innerHTML = "";
    if (questionList.length === 0) {
        const emptyStateElement = document.createElement("div");
        emptyStateElement.className = "question-set-empty-state";
        emptyStateElement.textContent = "No questions found.";
        questionSetCreateQuestionListContainerElement.appendChild(emptyStateElement);
        return;
    }
    for (const questionCurrent of questionList) {
        const questionRowElement = document.createElement("div");
        questionRowElement.className = "question-set-question-row";
        const questionSelectLabelElement = document.createElement("label");
        questionSelectLabelElement.className = "question-set-outside-select";
        const questionCheckboxInputElement = document.createElement("input");
        questionCheckboxInputElement.type = "checkbox";
        questionCheckboxInputElement.name = "questionSetCreateQuestionSelect";
        questionCheckboxInputElement.value = String(questionCurrent.questionId);
        questionCheckboxInputElement.checked = questionSetCreateSelectedQuestionIdSet.has(questionCurrent.questionId);
        questionCheckboxInputElement.addEventListener("change", () => {
            questionSetCreateQuestionSelectionToggle(questionCurrent.questionId, questionCheckboxInputElement.checked);
        });
        questionSelectLabelElement.appendChild(questionCheckboxInputElement);
        const questionCardElement = document.createElement("div");
        questionCardElement.className = "question-set-question-card";
        const questionHeaderElement = document.createElement("div");
        questionHeaderElement.className = "question-set-question-header";
        const questionTitleWrapperElement = document.createElement("div");
        const questionTitleElement = document.createElement("div");
        questionTitleElement.className = "question-set-question-title";
        questionTitleElement.textContent = questionCurrent.questionText;
        const questionMetaElement = document.createElement("div");
        questionMetaElement.className = "question-set-question-meta";
        questionMetaElement.textContent =
            "Question #" +
                String(questionCurrent.questionId) +
                " • Type: " +
                questionCurrent.questionType;
        const questionBadgeElement = document.createElement("div");
        questionBadgeElement.className = "question-set-question-badge";
        questionBadgeElement.textContent = questionCurrent.questionType;
        questionTitleWrapperElement.appendChild(questionTitleElement);
        questionTitleWrapperElement.appendChild(questionMetaElement);
        questionHeaderElement.appendChild(questionTitleWrapperElement);
        questionHeaderElement.appendChild(questionBadgeElement);
        const answerOptionListElement = document.createElement("div");
        answerOptionListElement.className = "question-set-question-options-inline";
        const answerOptionTextList = [];
        for (const answerOptionCurrent of questionCurrent.answerOptions) {
            answerOptionTextList.push(answerOptionCurrent.text);
        }
        answerOptionListElement.textContent = "• " + answerOptionTextList.join("      •      ");
        questionCardElement.appendChild(questionHeaderElement);
        questionCardElement.appendChild(answerOptionListElement);
        questionRowElement.appendChild(questionSelectLabelElement);
        questionRowElement.appendChild(questionCardElement);
        questionSetCreateQuestionListContainerElement.appendChild(questionRowElement);
    }
}
function questionSetCreatePaginationButtonUpdate(hasMore) {
    const questionSetCreatePrevButtonElement = document.getElementById("question-set-create-prev-button");
    const questionSetCreateNextButtonElement = document.getElementById("question-set-create-next-button");
    if (questionSetCreatePrevButtonElement) {
        questionSetCreatePrevButtonElement.disabled = questionSetCreateCursorHistory.length <= 1;
    }
    if (questionSetCreateNextButtonElement) {
        questionSetCreateNextButtonElement.disabled = hasMore !== true;
    }
}
async function questionSetCreateQuestionPageLoad(cursor) {
    const uidCurrent = authUidGet();
    if (!uidCurrent) {
        questionSetCreateMessageTextSet("User id is missing.");
        return;
    }
    questionSetCreateMessageTextSet("Loading questions...");
    const response = await fetch(questionShowApiUrl +
        "?uid=" +
        encodeURIComponent(uidCurrent) +
        "&cursor=" +
        encodeURIComponent(String(cursor)) +
        "&limit=5", {
        method: "GET"
    });
    const questionShowResponse = await response.json();
    if (questionShowResponse.error) {
        questionSetCreateMessageTextSet(questionShowResponse.error);
        return;
    }
    questionSetCreateCurrentCursor = cursor;
    questionSetCreateNextCursor = questionShowResponse.nextCursor;
    questionSetCreateQuestionListRender(questionShowResponse.questions);
    questionSetCreatePaginationButtonUpdate(questionShowResponse.hasMore);
    questionSetCreateCountUpdate();
    questionSetCreateMessageTextSet("Questions loaded successfully.");
}
async function questionSetCreateNextPageLoad() {
    if (questionSetCreateNextCursor === null) {
        return;
    }
    questionSetCreateCursorHistory.push(questionSetCreateNextCursor);
    await questionSetCreateQuestionPageLoad(questionSetCreateNextCursor);
}
async function questionSetCreatePreviousPageLoad() {
    if (questionSetCreateCursorHistory.length <= 1) {
        return;
    }
    questionSetCreateCursorHistory.pop();
    const questionSetCreatePreviousCursor = questionSetCreateCursorHistory[questionSetCreateCursorHistory.length - 1];
    await questionSetCreateQuestionPageLoad(questionSetCreatePreviousCursor);
}
async function questionSetCreateSubmit(event) {
    event.preventDefault();
    if (questionSetCreateAccessCheck() !== true) {
        return;
    }
    const uidCurrent = authUidGet();
    const questionSetCreateNameInputElement = document.getElementById("question-set-create-name-input");
    const questionSetCreateMakeActiveInputElement = document.getElementById("question-set-create-make-active-input");
    if (!uidCurrent ||
        !questionSetCreateNameInputElement ||
        !questionSetCreateMakeActiveInputElement) {
        return;
    }
    const gameConfigName = questionSetCreateNameInputElement.value.trim();
    const makeActive = questionSetCreateMakeActiveInputElement.checked;
    const questionIdListAllowed = Array.from(questionSetCreateSelectedQuestionIdSet);
    if (gameConfigName === "") {
        questionSetCreateMessageTextSet("Question set name is required.");
        return;
    }
    if (questionIdListAllowed.length === 0) {
        questionSetCreateMessageTextSet("Please select at least one question.");
        return;
    }
    questionSetCreateMessageTextSet("Creating question set...");
    const questionSetCreateFormData = new FormData();
    questionSetCreateFormData.append("uid", uidCurrent);
    questionSetCreateFormData.append("gameConfigName", gameConfigName);
    questionSetCreateFormData.append("questionIdListAllowed", JSON.stringify(questionIdListAllowed));
    questionSetCreateFormData.append("makeActive", makeActive ? "true" : "false");
    const response = await fetch(questionSetCreateApiUrl, {
        method: "POST",
        body: questionSetCreateFormData
    });
    const questionSetCreateResponse = await response.json();
    if (questionSetCreateResponse.error) {
        questionSetCreateMessageTextSet(questionSetCreateResponse.error);
        return;
    }
    questionSetCreateMessageTextSet("Question set created successfully with id " +
        String(questionSetCreateResponse.gameConfigId) +
        ".");
    questionSetCreateNameInputElement.value = "";
    questionSetCreateMakeActiveInputElement.checked = false;
    questionSetCreateSelectedQuestionIdSet.clear();
    questionSetCreateCountUpdate();
    await questionSetCreateQuestionPageLoad(0);
}
function questionSetCreatePageInitialize() {
    authNavbarUpdate();
    const questionSetCreateFormElement = document.getElementById("question-set-create-form");
    const questionSetCreatePrevButtonElement = document.getElementById("question-set-create-prev-button");
    const questionSetCreateNextButtonElement = document.getElementById("question-set-create-next-button");
    if (!questionSetCreateFormElement) {
        return;
    }
    if (questionSetCreateAccessCheck() !== true) {
        questionSetCreateFormElement.style.display = "none";
        return;
    }
    questionSetCreateCountUpdate();
    questionSetCreateQuestionPageLoad(0);
    questionSetCreateFormElement.addEventListener("submit", questionSetCreateSubmit);
    if (questionSetCreatePrevButtonElement) {
        questionSetCreatePrevButtonElement.addEventListener("click", () => {
            questionSetCreatePreviousPageLoad();
        });
    }
    if (questionSetCreateNextButtonElement) {
        questionSetCreateNextButtonElement.addEventListener("click", () => {
            questionSetCreateNextPageLoad();
        });
    }
}
questionSetCreatePageInitialize();
