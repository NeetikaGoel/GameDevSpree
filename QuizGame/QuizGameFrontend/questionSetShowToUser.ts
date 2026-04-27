export {};

import {
    authUidGet,
    authIsLoggedIn,
    authNavbarUpdate
} from "./auth.js";

const questionSetShowToUserApiUrl="../backend/api/v1/questionSetShowToUser.php";
const quizResetApiUrl="../backend/api/v1/quizReset.php";

type QuestionSetShowToUserItem=
{
    gameConfigId:number;
    gameConfigName:string;
    questionCountTotal:number;
    playedAlready:boolean;
    status:string;
    scoreCurrent:number;
    scoreHighest:number;
    playCount:number;
    questionsDone:number;
    isComplete:boolean;
    showPlay:boolean;
    showResume:boolean;
    showPlayAgain:boolean;
};

type QuestionSetShowToUserResponse=
{
    uid:number;
    gameConfigs:QuestionSetShowToUserItem[];
    error?:string;
};

type QuizResetResponse=
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
};

function questionSetShowToUserMessageTextSet(message:string):void
{
    const questionSetShowToUserMessageTextElement=document.getElementById("question-set-show-to-user-message-text");

    if (questionSetShowToUserMessageTextElement)
        {
            questionSetShowToUserMessageTextElement.textContent=message;
        }
}

function questionSetShowToUserAccessCheck():boolean
{
    if (authIsLoggedIn()!==true)
        {
            questionSetShowToUserMessageTextSet("You must be logged in to access question sets.");
            return false;
        }

    return true;
}

function questionSetShowToUserPlayGo(gameConfigId:number):void
{
    window.location.href="quiz.html?gameConfigId=" + encodeURIComponent(String(gameConfigId));
}

async function questionSetShowToUserPlayAgainReset(gameConfigId:number):Promise<void>
{
    const uidCurrent=authUidGet();

    if (!uidCurrent)
        {
            window.location.href="login.html";
            return;
        }

    questionSetShowToUserMessageTextSet("Resetting selected question set...");

    const quizResetFormData=new FormData();
    quizResetFormData.append("uid",uidCurrent);
    quizResetFormData.append("gameConfigId",String(gameConfigId));

    const response=await fetch(
        quizResetApiUrl,
        {
            method:"POST",
            body:quizResetFormData
        }
    );

    const quizResetResponse:QuizResetResponse=await response.json();

    if (quizResetResponse.error)
        {
            questionSetShowToUserMessageTextSet(quizResetResponse.error);
            return;
        }

    questionSetShowToUserMessageTextSet(
        "Question set reset successfully. You can start it again now."
    );

    await questionSetShowToUserLoad();
}

function questionSetShowToUserCardRender(gameConfigCurrent:QuestionSetShowToUserItem):HTMLDivElement
{
    const questionSetCardElement=document.createElement("div");
    questionSetCardElement.className="question-set-user-card";

    const questionSetHeaderElement=document.createElement("div");
    questionSetHeaderElement.className="question-set-user-card-header";

    const questionSetTitleWrapperElement=document.createElement("div");

    const questionSetTitleElement=document.createElement("div");
    questionSetTitleElement.className="question-set-user-card-title";
    questionSetTitleElement.textContent=gameConfigCurrent.gameConfigName;

    const questionSetMetaElement=document.createElement("div");
    questionSetMetaElement.className="question-set-user-card-meta";
    questionSetMetaElement.textContent=
        "Set #" +
        String(gameConfigCurrent.gameConfigId) +
        " • " +
        String(gameConfigCurrent.questionCountTotal) +
        " questions";

    const questionSetStatusBadgeElement=document.createElement("div");
    questionSetStatusBadgeElement.className="question-set-user-card-badge";
    questionSetStatusBadgeElement.textContent=gameConfigCurrent.status;

    questionSetTitleWrapperElement.appendChild(questionSetTitleElement);
    questionSetTitleWrapperElement.appendChild(questionSetMetaElement);

    questionSetHeaderElement.appendChild(questionSetTitleWrapperElement);
    questionSetHeaderElement.appendChild(questionSetStatusBadgeElement);

    const questionSetStatsGridElement=document.createElement("div");
    questionSetStatsGridElement.className="question-set-user-stats-grid";

    const questionSetScoreCurrentCardElement=document.createElement("div");
    questionSetScoreCurrentCardElement.className="question-set-user-stat-card";
    questionSetScoreCurrentCardElement.innerHTML=
        '<span class="question-set-user-stat-label">Current Score</span>' +
        '<span class="question-set-user-stat-value">' + String(gameConfigCurrent.scoreCurrent) + '</span>';

    const questionSetScoreHighestCardElement=document.createElement("div");
    questionSetScoreHighestCardElement.className="question-set-user-stat-card";
    questionSetScoreHighestCardElement.innerHTML=
        '<span class="question-set-user-stat-label">Highest Score</span>' +
        '<span class="question-set-user-stat-value">' + String(gameConfigCurrent.scoreHighest) + '</span>';

    const questionSetPlayCountCardElement=document.createElement("div");
    questionSetPlayCountCardElement.className="question-set-user-stat-card";
    questionSetPlayCountCardElement.innerHTML=
        '<span class="question-set-user-stat-label">Play Count</span>' +
        '<span class="question-set-user-stat-value">' + String(gameConfigCurrent.playCount) + '</span>';

    const questionSetProgressCardElement=document.createElement("div");
    questionSetProgressCardElement.className="question-set-user-stat-card";
    questionSetProgressCardElement.innerHTML=
        '<span class="question-set-user-stat-label">Questions Done</span>' +
        '<span class="question-set-user-stat-value">' + String(gameConfigCurrent.questionsDone) + '</span>';

    questionSetStatsGridElement.appendChild(questionSetScoreCurrentCardElement);
    questionSetStatsGridElement.appendChild(questionSetScoreHighestCardElement);
    questionSetStatsGridElement.appendChild(questionSetPlayCountCardElement);
    questionSetStatsGridElement.appendChild(questionSetProgressCardElement);

    const questionSetActionRowElement=document.createElement("div");
    questionSetActionRowElement.className="question-set-user-action-row";

    if (gameConfigCurrent.showPlay===true)
        {
            const playButtonElement=document.createElement("button");
            playButtonElement.type="button";
            playButtonElement.textContent="Play";
            playButtonElement.addEventListener("click",() => {
                questionSetShowToUserPlayGo(gameConfigCurrent.gameConfigId);
            });

            questionSetActionRowElement.appendChild(playButtonElement);
        }

    if (gameConfigCurrent.showResume===true)
        {
            const resumeButtonElement=document.createElement("button");
            resumeButtonElement.type="button";
            resumeButtonElement.textContent="Resume";
            resumeButtonElement.addEventListener("click",() => {
                questionSetShowToUserPlayGo(gameConfigCurrent.gameConfigId);
            });

            questionSetActionRowElement.appendChild(resumeButtonElement);
        }

    if (gameConfigCurrent.showPlayAgain===true)
        {
            const playAgainButtonElement=document.createElement("button");
            playAgainButtonElement.type="button";
            playAgainButtonElement.textContent="Play Again";
            playAgainButtonElement.addEventListener("click",() => {
                questionSetShowToUserPlayAgainReset(gameConfigCurrent.gameConfigId);
            });

            questionSetActionRowElement.appendChild(playAgainButtonElement);
        }

    questionSetCardElement.appendChild(questionSetHeaderElement);
    questionSetCardElement.appendChild(questionSetStatsGridElement);
    questionSetCardElement.appendChild(questionSetActionRowElement);

    return questionSetCardElement;
}

function questionSetShowToUserListRender(gameConfigList:QuestionSetShowToUserItem[]):void
{
    const questionSetShowToUserListContainerElement=document.getElementById("question-set-show-to-user-list-container");

    if (!questionSetShowToUserListContainerElement)
        {
            return;
        }

    questionSetShowToUserListContainerElement.innerHTML="";

    if (gameConfigList.length===0)
        {
            const emptyStateElement=document.createElement("div");
            emptyStateElement.className="question-set-empty-state";
            emptyStateElement.textContent="No active question sets are available right now.";
            questionSetShowToUserListContainerElement.appendChild(emptyStateElement);
            return;
        }

    for (const gameConfigCurrent of gameConfigList)
        {
            const questionSetCardElement=questionSetShowToUserCardRender(gameConfigCurrent);
            questionSetShowToUserListContainerElement.appendChild(questionSetCardElement);
        }
}

async function questionSetShowToUserLoad():Promise<void>
{
    const uidCurrent=authUidGet();

    if (!uidCurrent)
        {
            window.location.href="login.html";
            return;
        }

    questionSetShowToUserMessageTextSet("Loading active question sets...");

    const response=await fetch(
        questionSetShowToUserApiUrl + "?uid=" + encodeURIComponent(uidCurrent),
        {
            method:"GET"
        }
    );

    const questionSetShowToUserResponse:QuestionSetShowToUserResponse=await response.json();

    if (questionSetShowToUserResponse.error)
        {
            questionSetShowToUserMessageTextSet(questionSetShowToUserResponse.error);
            return;
        }

    questionSetShowToUserListRender(questionSetShowToUserResponse.gameConfigs);
    questionSetShowToUserMessageTextSet("Choose a question set to start or continue.");
}

function questionSetShowToUserPageInitialize():void
{
    authNavbarUpdate();

    if (questionSetShowToUserAccessCheck()!==true)
        {
            window.location.href="login.html";
            return;
        }

    questionSetShowToUserLoad();
}

questionSetShowToUserPageInitialize();