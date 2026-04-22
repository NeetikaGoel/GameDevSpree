import { authUidGet, authPermissionGroupGet, authNavbarUpdate } from "./auth.js";
const questionAddApiUrl = "../backend/api/v1/questionAdd.php";
function questionAddMessageTextSet(message) {
    const questionAddMessageBoxElement = document.getElementById("question-add-message-box");
    const questionAddMessageTextElement = document.getElementById("question-add-message-text");
    if (!questionAddMessageBoxElement || !questionAddMessageTextElement) {
        return;
    }
    if (message.trim() === "") {
        questionAddMessageBoxElement.classList.add("ui-hidden");
        questionAddMessageTextElement.textContent = "";
        return;
    }
    questionAddMessageBoxElement.classList.remove("ui-hidden");
    questionAddMessageTextElement.textContent = message;
}
function questionAddAccessCheck() {
    const uidCurrent = authUidGet();
    const permissionGroupCurrent = authPermissionGroupGet();
    if (!uidCurrent) {
        questionAddMessageTextSet("You must be logged in to access question add.");
        return false;
    }
    if (permissionGroupCurrent !== "admin") {
        questionAddMessageTextSet("Only admin user can add questions.");
        return false;
    }
    return true;
}
function questionAddTypeUiUpdate() {
    const questionTypeInputElement = document.getElementById("question-type-input");
    const questionAddMcqSectionElement = document.getElementById("question-add-mcq-section");
    const questionAddTrueFalseSectionElement = document.getElementById("question-add-true-false-section");
    if (!questionTypeInputElement ||
        !questionAddMcqSectionElement ||
        !questionAddTrueFalseSectionElement) {
        return;
    }
    if (questionTypeInputElement.value === "true/false") {
        questionAddMcqSectionElement.classList.add("ui-hidden");
        questionAddTrueFalseSectionElement.classList.remove("ui-hidden");
    }
    else {
        questionAddMcqSectionElement.classList.remove("ui-hidden");
        questionAddTrueFalseSectionElement.classList.add("ui-hidden");
    }
}
function questionAddFormReset() {
    const questionTextInputElement = document.getElementById("question-text-input");
    const questionTypeInputElement = document.getElementById("question-type-input");
    const answerOption1TextInputElement = document.getElementById("answer-option-1-text-input");
    const answerOption2TextInputElement = document.getElementById("answer-option-2-text-input");
    const answerOption3TextInputElement = document.getElementById("answer-option-3-text-input");
    const answerOption4TextInputElement = document.getElementById("answer-option-4-text-input");
    if (questionTextInputElement) {
        questionTextInputElement.value = "";
    }
    if (questionTypeInputElement) {
        questionTypeInputElement.value = "mcq";
    }
    if (answerOption1TextInputElement) {
        answerOption1TextInputElement.value = "";
    }
    if (answerOption2TextInputElement) {
        answerOption2TextInputElement.value = "";
    }
    if (answerOption3TextInputElement) {
        answerOption3TextInputElement.value = "";
    }
    if (answerOption4TextInputElement) {
        answerOption4TextInputElement.value = "";
    }
    const answerOptionCorrectRadioElementList = document.querySelectorAll('input[name="answerOptionCorrectIndex"]');
    answerOptionCorrectRadioElementList.forEach((answerOptionCorrectRadioElement) => {
        answerOptionCorrectRadioElement.checked = false;
    });
    const trueFalseCorrectRadioElementList = document.querySelectorAll('input[name="trueFalseCorrectValue"]');
    trueFalseCorrectRadioElementList.forEach((trueFalseCorrectRadioElement) => {
        trueFalseCorrectRadioElement.checked = false;
    });
    questionAddTypeUiUpdate();
}
async function questionAddSubmit(event) {
    event.preventDefault();
    if (questionAddAccessCheck() !== true) {
        return;
    }
    const uidCurrent = authUidGet();
    const questionTextInputElement = document.getElementById("question-text-input");
    const questionTypeInputElement = document.getElementById("question-type-input");
    const answerOption1TextInputElement = document.getElementById("answer-option-1-text-input");
    const answerOption2TextInputElement = document.getElementById("answer-option-2-text-input");
    const answerOption3TextInputElement = document.getElementById("answer-option-3-text-input");
    const answerOption4TextInputElement = document.getElementById("answer-option-4-text-input");
    if (!uidCurrent ||
        !questionTextInputElement ||
        !questionTypeInputElement ||
        !answerOption1TextInputElement ||
        !answerOption2TextInputElement ||
        !answerOption3TextInputElement ||
        !answerOption4TextInputElement) {
        return;
    }
    const questionText = questionTextInputElement.value.trim();
    const questionType = questionTypeInputElement.value.trim();
    if (questionText === "" || questionType === "") {
        questionAddMessageTextSet("Question text and question type are required.");
        return;
    }
    const answerOptions = [];
    if (questionType === "true/false") {
        const trueFalseCorrectValueElement = document.querySelector('input[name="trueFalseCorrectValue"]:checked');
        if (!trueFalseCorrectValueElement) {
            questionAddMessageTextSet("Please choose whether True or False is the correct answer.");
            return;
        }
        const isTrueCorrect = trueFalseCorrectValueElement.value === "true";
        answerOptions.push({
            text: "True",
            type: "true/false",
            isCorrect: isTrueCorrect
        });
        answerOptions.push({
            text: "False",
            type: "true/false",
            isCorrect: !isTrueCorrect
        });
    }
    else {
        const answerOptionCorrectIndexElement = document.querySelector('input[name="answerOptionCorrectIndex"]:checked');
        if (!answerOptionCorrectIndexElement) {
            questionAddMessageTextSet("Please mark exactly one correct answer option.");
            return;
        }
        const answerOptionCorrectIndex = parseInt(answerOptionCorrectIndexElement.value, 10);
        const answerOptionTextList = [
            answerOption1TextInputElement.value.trim(),
            answerOption2TextInputElement.value.trim(),
            answerOption3TextInputElement.value.trim(),
            answerOption4TextInputElement.value.trim()
        ];
        for (let index = 0; index < answerOptionTextList.length; index++) {
            if (answerOptionTextList[index] === "") {
                questionAddMessageTextSet("All MCQ option fields are required.");
                return;
            }
            answerOptions.push({
                text: answerOptionTextList[index],
                type: "mcq",
                isCorrect: index === answerOptionCorrectIndex
            });
        }
    }
    questionAddMessageTextSet("Adding question...");
    const questionAddFormData = new FormData();
    questionAddFormData.append("uid", uidCurrent);
    questionAddFormData.append("questionText", questionText);
    questionAddFormData.append("questionType", questionType);
    questionAddFormData.append("answerOptions", JSON.stringify(answerOptions));
    const response = await fetch(questionAddApiUrl, {
        method: "POST",
        body: questionAddFormData
    });
    const questionAddResponse = await response.json();
    if (questionAddResponse.error) {
        questionAddMessageTextSet(questionAddResponse.error);
        return;
    }
    questionAddMessageTextSet("Question added successfully with id " + String(questionAddResponse.questionId) + ".");
    questionAddFormReset();
}
function questionAddPageInitialize() {
    authNavbarUpdate();
    const questionAddFormElement = document.getElementById("question-add-form");
    const questionTypeInputElement = document.getElementById("question-type-input");
    if (!questionAddFormElement) {
        return;
    }
    if (questionAddAccessCheck() !== true) {
        questionAddFormElement.style.display = "none";
        return;
    }
    if (questionTypeInputElement) {
        questionTypeInputElement.addEventListener("change", () => {
            questionAddTypeUiUpdate();
        });
    }
    questionAddTypeUiUpdate();
    questionAddFormElement.addEventListener("submit", questionAddSubmit);
}
questionAddPageInitialize();
