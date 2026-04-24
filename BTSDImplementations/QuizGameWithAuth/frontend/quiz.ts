export {};  //SO IT DOESNT GIVE ERRORRRRRRRRRR

import {
    authUidGet,
    authNavbarUpdate,
    authIsLoggedIn
} from "./auth.js";

//first defining api endpoints of backend which frontend will call
const quizApiLoadUrl="../backend/api/v1/quizLoad.php";
const quizApiSubmitUrl="../backend/api/v1/quizSubmit.php";
const quizApiResultUrl="../backend/api/v1/quizResultShow.php";
const quizApiResetUrl="../backend/api/v1/quizReset.php";


//now type blocks to specify what shape will json come in from backend - helps to understand backend fields that will be returned
type QuizAnswerOption=
{
    id:number;
    type:string;
    text:string;
}

type QuizResponseLoad=
{
    uid:number;
    gameConfigId:number;
    gameConfigName:string;
    score:number;
    scoreHighest:number;
    playCount:number;
    questionsDone:number;
    questionIdCurrent:number;
    questionTextCurrent:string;
    questionTypeCurrent:string;
    answerOptionsCurrent:QuizAnswerOption[]; //it will be array
    questionCountTotal:number;
    error?:string; //error can be there anywhere
    isQuizDone?:boolean; //can be optional so put ???
    resultLink?:string;
}

type QuizResponseSubmit=
{
    uid:number;
    gameConfigId:number;
    score:number;
    scoreHighest:number;
    playCount:number;
    questionsDone:number;
    questionCountTotal:number;
    isAnswerOptionCorrectForQuestion:boolean;
    isQuizDone:boolean; 
    questionIdNext?:number;
    error?:string;
    resultLink?:string;
}


type QuizResponseResult=
{
    uid:number;
    gameConfigId:number;
    gameConfigName:string;
    score:number;
    scoreHighest:number;
    playCount:number;
    questionsDone:number;
    questionCountTotal:number;
    resultText:string;
    scorePercentage:number;
    answerCountWrong:number;
    isQuizDone?:boolean;
    error?:string; //still optional not mandatory 
}

type QuizResponseReset=
{
    uid:number;
    gameConfigId:number;
    gameConfigName:string;
    scoreCurrent:number;
    scoreHighest:number;
    playCount:number;
    questionCountTotal:number;
    isReset:boolean;
    error?:string;
}

//COPIED EVEYRHITNG ABOVE FROM THE PHP FILES HEHE

function quizGameConfigIdGetFromUrl():string | null
{
    const queryParamCurrent=new URLSearchParams(window.location.search);
    const gameConfigId=queryParamCurrent.get("gameConfigId");

    if (!gameConfigId || gameConfigId.trim()==="")
        {
            return null;
        }

    return gameConfigId;
}

//NOW FUNCTION TO CALL BACKEND AND UPDATE FRONTEND WITH CURRENT QUESTION AND ANSWER OPTIONS
async function quizQuestionCurrentLoad():Promise<void> //RETURN PROMISE VOID BECOZ ASYNC FUNCTION ALWAYS RETURNS PROMISE AND WE DONT WANT TO RETURN ANY VALUE SO VOID, NOW PROMISE IS USED WHEN FUNC IS ASYNC
{
    const uidCurrent=authUidGet();
    const gameConfigIdCurrent=quizGameConfigIdGetFromUrl();

    if (!uidCurrent)
        {
            window.location.href="login.html";
            return;
        }

    if (!gameConfigIdCurrent)
        {
            window.location.href="questionSetShowToUser.html";
            return;
        }

    const response=await fetch(
        quizApiLoadUrl +
        "?uid=" +
        encodeURIComponent(uidCurrent) +
        "&gameConfigId=" +
        encodeURIComponent(gameConfigIdCurrent),
        {
            method:"GET"
        }
    );

    const quizResponseLoad:QuizResponseLoad=await response.json();

    //some error came from backend so show it in frontend and return
    if (quizResponseLoad.error)
        {
            const quizQuestionTextCurrentElement=document.getElementById("quiz-question-text-current");
            const quizMessageTextElement=document.getElementById("quiz-message-text");

            if (quizQuestionTextCurrentElement)
                {
                    quizQuestionTextCurrentElement.textContent=quizResponseLoad.error;
                }

            if (quizMessageTextElement)
                {
                    quizMessageTextElement.textContent=quizResponseLoad.error;
                }

            return;
        }

    //if backend says quiz is done then we need to go to result page with same config id
    if (quizResponseLoad.isQuizDone===true)
        {
            window.location.href="result.html?gameConfigId=" + encodeURIComponent(String(quizResponseLoad.gameConfigId));
            return;
        }

    //taking references from html page to fill in values
    const quizScoreCurrentElement=document.getElementById("quiz-score-current");
    const quizScoreHighestElement=document.getElementById("quiz-score-highest");
    const quizPlayCountElement=document.getElementById("quiz-play-count");
    const quizQuestionCountTotalElement=document.getElementById("quiz-question-count-total");
    const quizQuestionCountTotalSecondElement=document.getElementById("quiz-question-count-total-second");
    const quizQuestionIdCurrentElement=document.getElementById("quiz-question-id-current");
    const quizQuestionTextCurrentElement=document.getElementById("quiz-question-text-current");
    const quizAnswerOptionsContainerElement=document.getElementById("quiz-answer-options-container");
    const quizGameConfigNameTextElement=document.getElementById("quiz-game-config-name-text");

    if 
    (
        !quizScoreCurrentElement ||
        !quizScoreHighestElement ||
        !quizPlayCountElement ||
        !quizQuestionCountTotalElement ||
        !quizQuestionCountTotalSecondElement ||
        !quizQuestionIdCurrentElement ||
        !quizQuestionTextCurrentElement ||
        !quizAnswerOptionsContainerElement ||
        !quizGameConfigNameTextElement
    ) 
    {
        return;
    }

    //now fill in values in html elements
    quizScoreCurrentElement.textContent=String(quizResponseLoad.score);
    quizScoreHighestElement.textContent=String(quizResponseLoad.scoreHighest);
    quizPlayCountElement.textContent=String(quizResponseLoad.playCount);
    quizQuestionCountTotalElement.textContent=String(quizResponseLoad.questionCountTotal);
    quizQuestionCountTotalSecondElement.textContent=String(quizResponseLoad.questionCountTotal);
    quizQuestionIdCurrentElement.textContent=String(quizResponseLoad.questionsDone+1);
    quizQuestionTextCurrentElement.textContent=quizResponseLoad.questionTextCurrent;
    quizGameConfigNameTextElement.textContent="Playing: " + quizResponseLoad.gameConfigName;

    //now update answer so first clear existing
    quizAnswerOptionsContainerElement.innerHTML="";

    //have to create buttons now as per backend response
    for (const quizAnswerOption of quizResponseLoad.answerOptionsCurrent)
        {
            const quizOptionWrapperElement=document.createElement("div");
            quizOptionWrapperElement.className="quiz-option-row";

            const quizOptionLabelElement=document.createElement("label");

            const quizOptionInputElement=document.createElement("input");
            quizOptionInputElement.type="radio";
            quizOptionInputElement.name="answerOptionId";
            quizOptionInputElement.value=String(quizAnswerOption.id);

            quizOptionLabelElement.appendChild(quizOptionInputElement);
            quizOptionLabelElement.append(" " + quizAnswerOption.text);

            quizOptionWrapperElement.appendChild(quizOptionLabelElement);
            quizAnswerOptionsContainerElement.appendChild(quizOptionWrapperElement);
        }

    const quizAnswerFormElement=document.getElementById("quiz-answer-form") as HTMLFormElement | null;

    if (!quizAnswerFormElement) 
        {
            return;
        }

    quizAnswerFormElement.dataset.questionIdCurrent=String(quizResponseLoad.questionIdCurrent);
    quizAnswerFormElement.dataset.uid=String(quizResponseLoad.uid);
    quizAnswerFormElement.dataset.gameConfigId=String(quizResponseLoad.gameConfigId);
}

//was answer correct???
async function quizAnswerCorrectSubmit(event:Event): Promise<void> 
{
    //this function will read selected asnwer by user and send it to backend
    event.preventDefault(); //to not let page reload which is default

    const quizAnswerFormElement=document.getElementById("quiz-answer-form") as HTMLFormElement | null;
    const quizMessageTextElement=document.getElementById("quiz-message-text");

    if (!quizAnswerFormElement || !quizMessageTextElement) 
        {
            return;
        }

    const uidCurrent=quizAnswerFormElement.dataset.uid;
    const gameConfigIdCurrent=quizAnswerFormElement.dataset.gameConfigId;
    const quizSelectedAnswerOptionElement=document.querySelector('input[name="answerOptionId"]:checked') as HTMLInputElement | null; //will find selected radio option

    if (!uidCurrent || !gameConfigIdCurrent || !quizSelectedAnswerOptionElement) 
        {
            quizMessageTextElement.textContent="Please select an answer option.";
            return;
        }

    const quizFormData=new FormData();
    quizFormData.append("uid",uidCurrent);
    quizFormData.append("gameConfigId",gameConfigIdCurrent);
    quizFormData.append("answerOptionId",quizSelectedAnswerOptionElement.value);
    
    //now have to call backend submit endpoint
    const response=await fetch(quizApiSubmitUrl, 
        {
            method:"POST",
            body:quizFormData
        }
    );

    const quizSubmitResponse:QuizResponseSubmit=await response.json();

    if (quizSubmitResponse.error)
        {
            quizMessageTextElement.textContent=quizSubmitResponse.error;
            return;
        }

    if (quizSubmitResponse.isAnswerOptionCorrectForQuestion===true) 
        {
            quizMessageTextElement.textContent="Correct answer!";
        } 
    else 
        {
            quizMessageTextElement.textContent="Wrong answer!";
        }

    //if backend says quiz is over then we neeed to go to the results.html
    if (quizSubmitResponse.isQuizDone===true) 
        {
            window.location.href="result.html?gameConfigId=" + encodeURIComponent(String(quizSubmitResponse.gameConfigId));
            return;
        }

    //and else case if not over then load next question
    await quizQuestionCurrentLoad();
}

async function quizResultLoad():Promise<void> 
{
    const uidCurrent=authUidGet();
    const gameConfigIdCurrent=quizGameConfigIdGetFromUrl();

    if (!uidCurrent)
        {
            const quizResultTextElement=document.getElementById("quiz-result-text");

            if (quizResultTextElement)
                {
                    quizResultTextElement.textContent="User id is missing.";
                }

            return;
        }

    if (!gameConfigIdCurrent)
        {
            window.location.href="questionSetShowToUser.html";
            return;
        }

    const response=await fetch(
        quizApiResultUrl +
        "?uid=" +
        encodeURIComponent(uidCurrent) +
        "&gameConfigId=" +
        encodeURIComponent(gameConfigIdCurrent),
        {
            method:"GET"
        }
    );

    const quizResultResponse:QuizResponseResult=await response.json();

    const quizResultScoreElement=document.getElementById("quiz-result-score");
    const quizResultScoreHighestElement=document.getElementById("quiz-result-score-highest");
    const quizResultPlayCountElement=document.getElementById("quiz-result-play-count");
    const quizResultQuestionsDoneElement=document.getElementById("quiz-result-questions-done");
    const quizResultQuestionCountTotalElement=document.getElementById("quiz-result-question-count-total");
    const quizResultAnswerCountWrongElement=document.getElementById("quiz-result-answer-count-wrong");
    const quizResultScorePercentageElement=document.getElementById("quiz-result-score-percentage");
    const quizResultTextElement=document.getElementById("quiz-result-text");
    const quizResultGameConfigNameElement=document.getElementById("quiz-result-game-config-name");

    if 
    (
        !quizResultScoreElement ||
        !quizResultScoreHighestElement ||
        !quizResultPlayCountElement ||
        !quizResultQuestionsDoneElement ||
        !quizResultQuestionCountTotalElement ||
        !quizResultAnswerCountWrongElement ||
        !quizResultScorePercentageElement ||
        !quizResultTextElement ||
        !quizResultGameConfigNameElement
    ) 
    {
        return;
    }

    if (quizResultResponse.error) 
        {
            quizResultTextElement.textContent=quizResultResponse.error;
            return;
        }

    quizResultScoreElement.textContent=String(quizResultResponse.score);
    quizResultScoreHighestElement.textContent=String(quizResultResponse.scoreHighest);
    quizResultPlayCountElement.textContent=String(quizResultResponse.playCount);
    quizResultQuestionsDoneElement.textContent=String(quizResultResponse.questionsDone);
    quizResultQuestionCountTotalElement.textContent=String(quizResultResponse.questionCountTotal);
    quizResultAnswerCountWrongElement.textContent=String(quizResultResponse.answerCountWrong);
    quizResultScorePercentageElement.textContent=String(quizResultResponse.scorePercentage);
    quizResultTextElement.textContent=quizResultResponse.resultText;
    quizResultGameConfigNameElement.textContent="Question Set: " + quizResultResponse.gameConfigName;
}

async function quizResultPlayAgainSubmit():Promise<void>
{
    const uidCurrent=authUidGet();
    const gameConfigIdCurrent=quizGameConfigIdGetFromUrl();
    const quizResultTextElement=document.getElementById("quiz-result-text");

    if (!uidCurrent || !gameConfigIdCurrent)
        {
            window.location.href="questionSetShowToUser.html";
            return;
        }

    if (quizResultTextElement)
        {
            quizResultTextElement.textContent="Resetting question set...";
        }

    const quizResetFormData=new FormData();
    quizResetFormData.append("uid",uidCurrent);
    quizResetFormData.append("gameConfigId",gameConfigIdCurrent);

    const response=await fetch(
        quizApiResetUrl,
        {
            method:"POST",
            body:quizResetFormData
        }
    );

    const quizResetResponse:QuizResponseReset=await response.json();

    if (quizResetResponse.error)
        {
            if (quizResultTextElement)
                {
                    quizResultTextElement.textContent=quizResetResponse.error;
                }

            return;
        }

    window.location.href="questionSetShowToUser.html";
}

//this will detect which page is currently open
function quizPageInitialize():void
{
    authNavbarUpdate();

    if (!authIsLoggedIn())
        {
            const quizAnswerFormElementRedirect=document.getElementById("quiz-answer-form");
            const quizResultScoreElementRedirect=document.getElementById("quiz-result-score");

            if (quizAnswerFormElementRedirect || quizResultScoreElementRedirect)
                {
                    window.location.href="login.html";
                    return;
                }
        }

    const quizAnswerFormElement=document.getElementById("quiz-answer-form");

    if (quizAnswerFormElement)
        {
            //if it exists
            quizQuestionCurrentLoad();  //naming followed
            quizAnswerFormElement.addEventListener("submit",quizAnswerCorrectSubmit);
            return;
        }

    const quizResultScoreElement=document.getElementById("quiz-result-score");
    const quizResultPlayAgainButtonElement=document.getElementById("quiz-result-play-again-button");

    if (quizResultScoreElement)
        {
            quizResultLoad();  //naming followed
        }

    if (quizResultPlayAgainButtonElement)
        {
            quizResultPlayAgainButtonElement.addEventListener("click",() => {
                quizResultPlayAgainSubmit();
            });
        }
}

quizPageInitialize();