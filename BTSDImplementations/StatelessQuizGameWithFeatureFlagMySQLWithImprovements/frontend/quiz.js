//first defining api endpoints of backend which frontend will call
const quizApiLoadUrl = "../backend/quizLoad.php";
const quizApiSubmitUrl = "../backend/quizSubmit.php";
const quizApiResultUrl = "../backend/quizResultShow.php";
//COPIED EVEYRHITNG ABOVE FROM THE PHP FILES HEHE
//NOW 3 FUNCTIONS THAT WILL HELP IN CONNECTING FRONTEND WITH BACKEND
function quizAttemptIdGetFromUrl() {
    const quizUrlSearchParams = new URLSearchParams(window.location.search);
    const quizAttemptId = quizUrlSearchParams.get("quizAttemptId");
    if (!quizAttemptId || quizAttemptId.trim() === "") {
        return null;
    }
    return quizAttemptId;
}
function quizAttemptIdSetInUrl(quizAttemptId) {
    const quizUrl = new URL(window.location.href);
    quizUrl.searchParams.set("quizAttemptId", quizAttemptId);
    window.history.replaceState({}, "", quizUrl.toString());
}
async function quizQuestionCurrentLoad() {
    //this will call quizLoad.php file
    //also will take json and read it and also update the quiz page frontend
    //so first of all need to tell browser whether to add cookies/session so yes 
    const quizAttemptIdCurrent = quizAttemptIdGetFromUrl();
    let quizApiLoadUrlFinal = quizApiLoadUrl;
    if (quizAttemptIdCurrent) {
        quizApiLoadUrlFinal = quizApiLoadUrl + "?quizAttemptId=" + encodeURIComponent(quizAttemptIdCurrent);
    }
    const response = await fetch(quizApiLoadUrlFinal, {
        method: "GET"
    });
    const quizResponseLoad = await response.json();
    if (quizResponseLoad.error) {
        const quizQuestionTextCurrentElement = document.getElementById("quiz-question-text-current");
        const quizMessageTextElement = document.getElementById("quiz-message-text");
        if (quizQuestionTextCurrentElement) {
            quizQuestionTextCurrentElement.textContent = quizResponseLoad.error;
        }
        if (quizMessageTextElement) {
            quizMessageTextElement.textContent = quizResponseLoad.error;
        }
        return;
    }
    if (quizResponseLoad.quizAttemptId) {
        quizAttemptIdSetInUrl(String(quizResponseLoad.quizAttemptId));
    }
    if (quizResponseLoad.isQuizDone === true) {
        //if quiz finished-go to result page ofc
        window.location.href = "result.html?quizAttemptId=" + encodeURIComponent(String(quizResponseLoad.quizAttemptId));
        return;
    }
    //taking references from html page to fill in values
    const quizScoreCurrentElement = document.getElementById("quiz-score-current");
    const quizQuestionCountTotalElement = document.getElementById("quiz-question-count-total");
    const quizQuestionCountTotalSecondElement = document.getElementById("quiz-question-count-total-second");
    const quizQuestionIdCurrentElement = document.getElementById("quiz-question-id-current");
    const quizQuestionTextCurrentElement = document.getElementById("quiz-question-text-current");
    const quizAnswerOptionsContainerElement = document.getElementById("quiz-answer-options-container");
    if (!quizScoreCurrentElement ||
        !quizQuestionCountTotalElement ||
        !quizQuestionCountTotalSecondElement ||
        !quizQuestionIdCurrentElement ||
        !quizQuestionTextCurrentElement ||
        !quizAnswerOptionsContainerElement) {
        return;
    }
    quizScoreCurrentElement.textContent = String(quizResponseLoad.score);
    quizQuestionCountTotalElement.textContent = String(quizResponseLoad.questionCountTotal);
    quizQuestionCountTotalSecondElement.textContent = String(quizResponseLoad.questionCountTotal);
    quizQuestionIdCurrentElement.textContent = String(quizResponseLoad.questionsDone + 1);
    quizQuestionTextCurrentElement.textContent = quizResponseLoad.questionTextCurrent;
    //now update answer so first clear existing
    quizAnswerOptionsContainerElement.innerHTML = "";
    //have to create buttons now as per backend response
    for (const quizAnswerOption of quizResponseLoad.answerOptionsCurrent) {
        const quizOptionWrapperElement = document.createElement("div");
        quizOptionWrapperElement.className = "quiz-option-row";
        const quizOptionLabelElement = document.createElement("label");
        const quizOptionInputElement = document.createElement("input");
        quizOptionInputElement.type = "radio";
        quizOptionInputElement.name = "answerOptionId";
        quizOptionInputElement.value = String(quizAnswerOption.id);
        quizOptionLabelElement.appendChild(quizOptionInputElement);
        quizOptionLabelElement.append(" " + quizAnswerOption.text);
        quizOptionWrapperElement.appendChild(quizOptionLabelElement);
        quizAnswerOptionsContainerElement.appendChild(quizOptionWrapperElement);
    }
    const quizAnswerFormElement = document.getElementById("quiz-answer-form");
    if (!quizAnswerFormElement) {
        return;
    }
    quizAnswerFormElement.dataset.questionIdCurrent = String(quizResponseLoad.questionIdCurrent);
    quizAnswerFormElement.dataset.quizAttemptId = String(quizResponseLoad.quizAttemptId);
}
//SO FIRST OF ALL IT'S IMPORTANT TO INITIALIZE PAGE
async function quizAnswerCorrectSubmit(event) {
    //this function will read selected asnwer by user and send it to backend
    event.preventDefault();
    const quizAnswerFormElement = document.getElementById("quiz-answer-form");
    const quizMessageTextElement = document.getElementById("quiz-message-text");
    if (!quizAnswerFormElement || !quizMessageTextElement) {
        return;
    }
    const quizQuestionIdCurrent = quizAnswerFormElement.dataset.questionIdCurrent;
    const quizAttemptIdCurrent = quizAnswerFormElement.dataset.quizAttemptId;
    const quizSelectedAnswerOptionElement = document.querySelector('input[name="answerOptionId"]:checked'); //will find selected radio option
    if (!quizQuestionIdCurrent || !quizAttemptIdCurrent || !quizSelectedAnswerOptionElement) {
        quizMessageTextElement.textContent = "Please select an answer option.";
        return;
    }
    const quizFormData = new FormData();
    quizFormData.append("quizAttemptId", quizAttemptIdCurrent);
    quizFormData.append("answerOptionId", quizSelectedAnswerOptionElement.value);
    //now have to call backend submit endpoint
    const response = await fetch(quizApiSubmitUrl, {
        method: "POST",
        body: quizFormData
    });
    const quizSubmitResponse = await response.json();
    if (quizSubmitResponse.error) {
        quizMessageTextElement.textContent = quizSubmitResponse.error;
        return;
    }
    if (quizSubmitResponse.quizAttemptId) {
        quizAttemptIdSetInUrl(String(quizSubmitResponse.quizAttemptId));
        quizAnswerFormElement.dataset.quizAttemptId = String(quizSubmitResponse.quizAttemptId);
    }
    if (quizSubmitResponse.isAnswerOptionCorrectForQuestion === true) {
        quizMessageTextElement.textContent = "Correct answer!";
    }
    else {
        quizMessageTextElement.textContent = "Wrong answer!";
    }
    //if backend says quiz is over then we neeed to go to the results.html
    if (quizSubmitResponse.isQuizDone === true) {
        window.location.href = "result.html?quizAttemptId=" + encodeURIComponent(String(quizSubmitResponse.quizAttemptId));
        return;
    }
    //and else case if not over then load next question
    await quizQuestionCurrentLoad();
}
async function quizResultLoad() {
    //this function will call result backend endpoint api and update result page html
    const quizAttemptIdCurrent = quizAttemptIdGetFromUrl();
    if (!quizAttemptIdCurrent) {
        const quizResultTextElement = document.getElementById("quiz-result-text");
        if (quizResultTextElement) {
            quizResultTextElement.textContent = "Quiz attempt id is missing.";
        }
        return;
    }
    const response = await fetch(quizApiResultUrl + "?quizAttemptId=" + encodeURIComponent(quizAttemptIdCurrent), {
        method: "GET"
    });
    const quizResultResponse = await response.json();
    const quizResultScoreElement = document.getElementById("quiz-result-score");
    const quizResultQuestionsDoneElement = document.getElementById("quiz-result-questions-done");
    const quizResultQuestionCountTotalElement = document.getElementById("quiz-result-question-count-total");
    const quizResultAnswerCountWrongElement = document.getElementById("quiz-result-answer-count-wrong");
    const quizResultScorePercentageElement = document.getElementById("quiz-result-score-percentage");
    const quizResultTextElement = document.getElementById("quiz-result-text");
    if (!quizResultScoreElement ||
        !quizResultQuestionsDoneElement ||
        !quizResultQuestionCountTotalElement ||
        !quizResultAnswerCountWrongElement ||
        !quizResultScorePercentageElement ||
        !quizResultTextElement) {
        return;
    }
    if (quizResultResponse.error) {
        quizResultTextElement.textContent = quizResultResponse.error;
        return;
    }
    quizResultScoreElement.textContent = String(quizResultResponse.score);
    quizResultQuestionsDoneElement.textContent = String(quizResultResponse.questionsDone);
    quizResultQuestionCountTotalElement.textContent = String(quizResultResponse.questionCountTotal);
    quizResultAnswerCountWrongElement.textContent = String(quizResultResponse.answerCountWrong);
    quizResultScorePercentageElement.textContent = String(quizResultResponse.scorePercentage);
    quizResultTextElement.textContent = quizResultResponse.resultText;
}
//this will detect which page is currently open
function quizPageInitialize() {
    const quizAnswerFormElement = document.getElementById("quiz-answer-form");
    if (quizAnswerFormElement) {
        //if it exists
        quizQuestionCurrentLoad(); //naming followed
        quizAnswerFormElement.addEventListener("submit", quizAnswerCorrectSubmit);
        return;
    }
    const quizResultScoreElement = document.getElementById("quiz-result-score");
    if (quizResultScoreElement) {
        quizResultLoad(); //naming followed
    }
}
quizPageInitialize();
export {};
