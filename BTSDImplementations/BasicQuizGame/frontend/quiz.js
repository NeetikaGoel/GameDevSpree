"use strict";
//first defining api endpoints of backend which frontend will call
const quizApiLoadUrl = "../backend/quizLoad.php";
const quizApiSubmitUrl = "../backend/quizSubmit.php";
const quizApiResultUrl = "../backend/quizResultShow.php";
//COPIED EVEYRHITNG ABOVE FROM THE PHP FILES HEHE
//NOW 3 FUNCTIONS THAT WILL HELP IN CONNECTING FRONTEND WITH BACKEND
async function quizQuestionCurrentLoad() {
    //this will call quizLoad.php file
    //also will take json and read it and also update the quiz page frontend
    //so first of all need to tell browser whether to add cookies/session so yes 
    const response = await fetch(quizApiLoadUrl, {
        method: "GET",
        credentials: "include"
    });
    const quizResponseLoad = await response.json();
    if (quizResponseLoad.isQuizDone === true) {
        //if quiz finished-go to result page ofc
        window.location.href = "result.html";
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
    quizQuestionIdCurrentElement.textContent = String(quizResponseLoad.questionIdCurrent);
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
}
//SO FIRST OF ALL ITS IMPORTANT TO INILIALIZE PAGE
async function quizAnswerCorrectSubmit(event) {
    //this function will read selected asnwer by user and send it to backend
    event.preventDefault();
    const quizAnswerFormElement = document.getElementById("quiz-answer-form");
    const quizMessageTextElement = document.getElementById("quiz-message-text");
    if (!quizAnswerFormElement || !quizMessageTextElement) {
        return;
    }
    const quizQuestionIdCurrent = quizAnswerFormElement.dataset.questionIdCurrent;
    const quizSelectedAnswerOptionElement = document.querySelector('input[name="answerOptionId"]:checked'); //will find selected radio option
    if (!quizQuestionIdCurrent || !quizSelectedAnswerOptionElement) {
        quizMessageTextElement.textContent = "Please select an answer option.";
        return;
    }
    const quizFormData = new FormData();
    quizFormData.append("questionId", quizQuestionIdCurrent);
    quizFormData.append("answerOptionId", quizSelectedAnswerOptionElement.value);
    //now have to call backend submit endpoint
    const response = await fetch(quizApiSubmitUrl, {
        method: "POST",
        credentials: "include",
        body: quizFormData
    });
    const quizSubmitResponse = await response.json();
    if (quizSubmitResponse.error) {
        quizMessageTextElement.textContent = quizSubmitResponse.error;
        return;
    }
    if (quizSubmitResponse.isAnswerOptionCorrectForQuestion === true) {
        quizMessageTextElement.textContent = "Correct answer!";
    }
    else {
        quizMessageTextElement.textContent = "Wrong answer!";
    }
    //if backend says quiz is over then we neeed to go to the results.html
    if (quizSubmitResponse.isQuizDone === true) {
        window.location.href = "result.html";
        return;
    }
    //and else case if not over then load next question
    await quizQuestionCurrentLoad();
}
async function quizResultLoad() {
    //this function will call result backend endpoint api and update result page html
    const response = await fetch(quizApiResultUrl, {
        method: "GET",
        credentials: "include"
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
