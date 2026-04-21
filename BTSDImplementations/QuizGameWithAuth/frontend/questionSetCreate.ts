export {};

import {
    authUidGet,
    authPermissionGroupGet,
    authNavbarUpdate
} from "./auth.js";

const questionShowApiUrl="../backend/api/v1/questionShow.php";
const questionSetCreateApiUrl="../backend/api/v1/questionSetCreate.php";

type QuestionShowAnswerOption=
{
    id:number;
    text:string;
    type:string;
};

type QuestionShowQuestion=
{
    questionId:number;
    questionText:string;
    questionType:string;
    answerOptions:QuestionShowAnswerOption[];
};

type QuestionShowResponse=
{
    questions:QuestionShowQuestion[];
    nextCursor:number | null;
    hasMore:boolean;
    error?:string;
};

type QuestionSetCreateResponse=
{
    gameConfigId:number;
    gameConfigName:string;
    questionCountTarget:number;
    questionIdListAllowed:number[];
    isActive:boolean;
    isCreated:boolean;
    error?:string;
};

//this will store all selected question ids even when admin moves across pages
const questionSetCreateSelectedQuestionIdSet:Set<number>=new Set<number>();

//this will store cursor history for previous button handling
const questionSetCreateCursorHistory:number[]=[0];

let questionSetCreateCurrentCursor=0;
let questionSetCreateNextCursor:number | null=null;

function questionSetCreateMessageTextSet(message:string):void
{
    const questionSetCreateMessageTextElement=document.getElementById("question-set-create-message-text");

    if (questionSetCreateMessageTextElement)
        {
            questionSetCreateMessageTextElement.textContent=message;
        }
}

function questionSetCreateCountUpdate():void
{
    const questionSetCreateCountInputElement=document.getElementById("question-set-create-count-input") as HTMLInputElement | null;

    if (questionSetCreateCountInputElement)
        {
            questionSetCreateCountInputElement.value=String(questionSetCreateSelectedQuestionIdSet.size);
        }
}

function questionSetCreateAccessCheck():boolean
{
    const uidCurrent=authUidGet();
    const permissionGroupCurrent=authPermissionGroupGet();

    if (!uidCurrent)
        {
            questionSetCreateMessageTextSet("You must be logged in to access question set create.");
            return false;
        }

    if (permissionGroupCurrent!=="admin")
        {
            questionSetCreateMessageTextSet("Only admin user can create question sets.");
            return false;
        }

    return true;
}

function questionSetCreateQuestionSelectionToggle(questionId:number,isChecked:boolean):void
{
    if (isChecked===true)
        {
            questionSetCreateSelectedQuestionIdSet.add(questionId);
        }
    else
        {
            questionSetCreateSelectedQuestionIdSet.delete(questionId);
        }

    questionSetCreateCountUpdate();
}

function questionSetCreateQuestionListRender(questionList:QuestionShowQuestion[]):void
{
    const questionSetCreateQuestionListContainerElement=document.getElementById("question-set-create-question-list-container");

    if (!questionSetCreateQuestionListContainerElement)
        {
            return;
        }

    questionSetCreateQuestionListContainerElement.innerHTML="";

    if (questionList.length===0)
        {
            questionSetCreateQuestionListContainerElement.textContent="No questions found.";
            return;
        }

    for (const questionCurrent of questionList)
        {
            const questionWrapperElement=document.createElement("div");
            questionWrapperElement.className="game-question-container";

            const questionHeaderElement=document.createElement("h1");
            questionHeaderElement.textContent=
                "Question " +
                String(questionCurrent.questionId) +
                ": " +
                questionCurrent.questionText +
                " (" +
                questionCurrent.questionType +
                ")";

            questionWrapperElement.appendChild(questionHeaderElement);

            const questionCheckboxRowElement=document.createElement("div");
            questionCheckboxRowElement.className="form-row question-add-correct-row";

            const questionCheckboxLabelElement=document.createElement("label");
            const questionCheckboxInputElement=document.createElement("input");

            questionCheckboxInputElement.type="checkbox";
            questionCheckboxInputElement.checked=questionSetCreateSelectedQuestionIdSet.has(questionCurrent.questionId);

            questionCheckboxInputElement.addEventListener("change",() => {
                questionSetCreateQuestionSelectionToggle(
                    questionCurrent.questionId,
                    questionCheckboxInputElement.checked
                );
            });

            questionCheckboxLabelElement.appendChild(questionCheckboxInputElement);
            questionCheckboxLabelElement.append(" Select this question");

            questionCheckboxRowElement.appendChild(questionCheckboxLabelElement);
            questionWrapperElement.appendChild(questionCheckboxRowElement);

            for (const answerOptionCurrent of questionCurrent.answerOptions)
                {
                    const answerOptionElement=document.createElement("div");
                    answerOptionElement.className="form-row";
                    answerOptionElement.textContent=
                        "Option " +
                        String(answerOptionCurrent.id) +
                        ": " +
                        answerOptionCurrent.text +
                        " (" +
                        answerOptionCurrent.type +
                        ")";

                    questionWrapperElement.appendChild(answerOptionElement);
                }

            questionSetCreateQuestionListContainerElement.appendChild(questionWrapperElement);
        }
}

function questionSetCreatePaginationButtonUpdate(hasMore:boolean):void
{
    const questionSetCreatePrevButtonElement=document.getElementById("question-set-create-prev-button") as HTMLButtonElement | null;
    const questionSetCreateNextButtonElement=document.getElementById("question-set-create-next-button") as HTMLButtonElement | null;

    if (questionSetCreatePrevButtonElement)
        {
            questionSetCreatePrevButtonElement.disabled=questionSetCreateCursorHistory.length<=1;
        }

    if (questionSetCreateNextButtonElement)
        {
            questionSetCreateNextButtonElement.disabled=hasMore!==true;
        }
}

async function questionSetCreateQuestionPageLoad(cursor:number):Promise<void>
{
    const uidCurrent=authUidGet();

    if (!uidCurrent)
        {
            questionSetCreateMessageTextSet("User id is missing.");
            return;
        }

    questionSetCreateMessageTextSet("Loading questions...");

    const response=await fetch(
        questionShowApiUrl +
        "?uid=" +
        encodeURIComponent(uidCurrent) +
        "&cursor=" +
        encodeURIComponent(String(cursor)) +
        "&limit=5",
        {
            method:"GET"
        }
    );

    const questionShowResponse:QuestionShowResponse=await response.json();

    if (questionShowResponse.error)
        {
            questionSetCreateMessageTextSet(questionShowResponse.error);
            return;
        }

    questionSetCreateCurrentCursor=cursor;
    questionSetCreateNextCursor=questionShowResponse.nextCursor;

    questionSetCreateQuestionListRender(questionShowResponse.questions);
    questionSetCreatePaginationButtonUpdate(questionShowResponse.hasMore);
    questionSetCreateCountUpdate();
    questionSetCreateMessageTextSet("Questions loaded successfully.");
}

async function questionSetCreateNextPageLoad():Promise<void>
{
    if (questionSetCreateNextCursor===null)
        {
            return;
        }

    questionSetCreateCursorHistory.push(questionSetCreateNextCursor);
    await questionSetCreateQuestionPageLoad(questionSetCreateNextCursor);
}

async function questionSetCreatePreviousPageLoad():Promise<void>
{
    if (questionSetCreateCursorHistory.length<=1)
        {
            return;
        }

    questionSetCreateCursorHistory.pop();

    const questionSetCreatePreviousCursor=questionSetCreateCursorHistory[questionSetCreateCursorHistory.length-1];
    await questionSetCreateQuestionPageLoad(questionSetCreatePreviousCursor);
}

async function questionSetCreateSubmit(event:Event):Promise<void>
{
    event.preventDefault();

    if (questionSetCreateAccessCheck()!==true)
        {
            return;
        }

    const uidCurrent=authUidGet();

    const questionSetCreateNameInputElement=document.getElementById("question-set-create-name-input") as HTMLInputElement | null;
    const questionSetCreateMakeActiveInputElement=document.getElementById("question-set-create-make-active-input") as HTMLInputElement | null;

    if (
        !uidCurrent ||
        !questionSetCreateNameInputElement ||
        !questionSetCreateMakeActiveInputElement
    )
        {
            return;
        }

    const gameConfigName=questionSetCreateNameInputElement.value.trim();
    const makeActive=questionSetCreateMakeActiveInputElement.checked;
    const questionIdListAllowed=Array.from(questionSetCreateSelectedQuestionIdSet);

    if (gameConfigName==="")
        {
            questionSetCreateMessageTextSet("Question set name is required.");
            return;
        }

    if (questionIdListAllowed.length===0)
        {
            questionSetCreateMessageTextSet("Please select at least one question.");
            return;
        }

    questionSetCreateMessageTextSet("Creating question set...");

    const questionSetCreateFormData=new FormData();
    questionSetCreateFormData.append("uid",uidCurrent);
    questionSetCreateFormData.append("gameConfigName",gameConfigName);
    questionSetCreateFormData.append("questionIdListAllowed",JSON.stringify(questionIdListAllowed));
    questionSetCreateFormData.append("makeActive",makeActive ? "true" : "false");

    const response=await fetch(
        questionSetCreateApiUrl,
        {
            method:"POST",
            body:questionSetCreateFormData
        }
    );

    const questionSetCreateResponse:QuestionSetCreateResponse=await response.json();

    if (questionSetCreateResponse.error)
        {
            questionSetCreateMessageTextSet(questionSetCreateResponse.error);
            return;
        }

    questionSetCreateMessageTextSet(
        "Question set created successfully with id " +
        String(questionSetCreateResponse.gameConfigId) +
        "."
    );

    questionSetCreateNameInputElement.value="";
    questionSetCreateMakeActiveInputElement.checked=false;
    questionSetCreateSelectedQuestionIdSet.clear();
    questionSetCreateCountUpdate();

    await questionSetCreateQuestionPageLoad(0);
}

function questionSetCreatePageInitialize():void
{
    authNavbarUpdate();

    const questionSetCreateFormElement=document.getElementById("question-set-create-form") as HTMLFormElement | null;
    const questionSetCreatePrevButtonElement=document.getElementById("question-set-create-prev-button");
    const questionSetCreateNextButtonElement=document.getElementById("question-set-create-next-button");

    if (!questionSetCreateFormElement)
        {
            return;
        }

    if (questionSetCreateAccessCheck()!==true)
        {
            questionSetCreateFormElement.style.display="none";
            return;
        }

    questionSetCreateCountUpdate();
    questionSetCreateQuestionPageLoad(0);

    questionSetCreateFormElement.addEventListener("submit",questionSetCreateSubmit);

    if (questionSetCreatePrevButtonElement)
        {
            questionSetCreatePrevButtonElement.addEventListener("click",() => {
                questionSetCreatePreviousPageLoad();
            });
        }

    if (questionSetCreateNextButtonElement)
        {
            questionSetCreateNextButtonElement.addEventListener("click",() => {
                questionSetCreateNextPageLoad();
            });
        }
}

questionSetCreatePageInitialize();