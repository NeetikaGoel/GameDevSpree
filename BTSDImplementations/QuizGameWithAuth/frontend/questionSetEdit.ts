export {};

import {
    authUidGet,
    authPermissionGroupGet,
    authNavbarUpdate
} from "./auth.js";

const questionSetEditApiUrl="../backend/api/v1/questionSetEdit.php";
const questionSetShowApiUrl="../backend/api/v1/questionSetShow.php";
const questionShowApiUrl="../backend/api/v1/questionShow.php";

type QuestionSetEditResponse=
{
    gameConfigId:number;
    gameConfigName:string;
    questionCountTarget:number;
    questionIdListAllowed:number[];
    isActive:boolean;
    isUpdated:boolean;
    error?:string;
};

type QuestionSetShowItem=
{
    id:number;
    gameConfigName:string;
    questionCountTarget:number;
    questionIdListAllowed:number[];
    isActive:boolean;
    createdAt:string;
    updatedAt:string;
};

type QuestionSetShowResponse=
{
    gameConfigs:QuestionSetShowItem[];
    nextCursor:number | null;
    hasMore:boolean;
    error?:string;
};

type QuestionShowAnswerOption=
{
    id:number;
    text:string;
    type:string;
};

type QuestionShowItem=
{
    questionId:number;
    questionText:string;
    questionType:string;
    answerOptions:QuestionShowAnswerOption[];
};

type QuestionShowResponse=
{
    questions:QuestionShowItem[];
    nextCursor:number | null;
    hasMore:boolean;
    error?:string;
};

let questionSetEditConfigCursorCurrent=0;
let questionSetEditConfigCursorHistory:number[]=[];
let questionSetEditConfigNextCursor:number | null=null;

let questionSetEditQuestionCursorCurrent=0;
let questionSetEditQuestionCursorHistory:number[]=[];
let questionSetEditQuestionNextCursor:number | null=null;

let questionSetEditSelectedGameConfigId:number | null=null;
let questionSetEditSelectedQuestionIdSet:Set<number>=new Set<number>();

function questionSetEditMessageTextSet(message:string):void
{
    const questionSetEditMessageTextElement=document.getElementById("question-set-edit-message-text");

    if (questionSetEditMessageTextElement)
        {
            questionSetEditMessageTextElement.textContent=message;
        }
}

function questionSetEditAccessCheck():boolean
{
    const uidCurrent=authUidGet();
    const permissionGroupCurrent=authPermissionGroupGet();

    if (!uidCurrent)
        {
            questionSetEditMessageTextSet("You must be logged in to access question set edit.");
            return false;
        }

    if (permissionGroupCurrent!=="admin")
        {
            questionSetEditMessageTextSet("Only admin user can edit question sets.");
            return false;
        }

    return true;
}

function questionSetEditCountUpdate():void
{
    const questionSetEditCountTextElement=document.getElementById("question-set-edit-count-text");

    if (!questionSetEditCountTextElement)
        {
            return;
        }

    questionSetEditCountTextElement.textContent=String(questionSetEditSelectedQuestionIdSet.size);
}

function questionSetEditConfigSelectionFill(gameConfigCurrent:QuestionSetShowItem):void
{
    const questionSetEditIdTextElement=document.getElementById("question-set-edit-id-text");
    const questionSetEditNameInputElement=document.getElementById("question-set-edit-name-input") as HTMLInputElement | null;
    const questionSetEditMakeActiveInputElement=document.getElementById("question-set-edit-make-active-input") as HTMLInputElement | null;

    if (
        !questionSetEditIdTextElement ||
        !questionSetEditNameInputElement ||
        !questionSetEditMakeActiveInputElement
    )
        {
            return;
        }

    questionSetEditSelectedGameConfigId=gameConfigCurrent.id;
    questionSetEditSelectedQuestionIdSet=new Set<number>(gameConfigCurrent.questionIdListAllowed);

    questionSetEditIdTextElement.textContent="Set #" + String(gameConfigCurrent.id);
    questionSetEditNameInputElement.value=gameConfigCurrent.gameConfigName;
    questionSetEditMakeActiveInputElement.checked=gameConfigCurrent.isActive;

    questionSetEditCountUpdate();
    questionSetEditQuestionRenderRefreshSelectionOnly();
}

function questionSetEditQuestionRenderRefreshSelectionOnly():void
{
    const questionCheckboxElementList=document.querySelectorAll('input[name="questionSetEditQuestionSelect"]') as NodeListOf<HTMLInputElement>;

    questionCheckboxElementList.forEach((questionCheckboxElement) => {
        const questionIdCurrent=parseInt(questionCheckboxElement.value,10);

        if (!Number.isNaN(questionIdCurrent))
            {
                questionCheckboxElement.checked=questionSetEditSelectedQuestionIdSet.has(questionIdCurrent);
            }
    });

    questionSetEditCountUpdate();
}

function questionSetEditConfigPaginationButtonsUpdate(hasMore:boolean):void
{
    const questionSetEditConfigPrevButtonElement=document.getElementById("question-set-edit-config-prev-button") as HTMLButtonElement | null;
    const questionSetEditConfigNextButtonElement=document.getElementById("question-set-edit-config-next-button") as HTMLButtonElement | null;

    if (questionSetEditConfigPrevButtonElement)
        {
            questionSetEditConfigPrevButtonElement.disabled=questionSetEditConfigCursorHistory.length===0;
        }

    if (questionSetEditConfigNextButtonElement)
        {
            questionSetEditConfigNextButtonElement.disabled=!hasMore || questionSetEditConfigNextCursor===null;
        }
}

function questionSetEditQuestionPaginationButtonsUpdate(hasMore:boolean):void
{
    const questionSetEditQuestionPrevButtonElement=document.getElementById("question-set-edit-question-prev-button") as HTMLButtonElement | null;
    const questionSetEditQuestionNextButtonElement=document.getElementById("question-set-edit-question-next-button") as HTMLButtonElement | null;

    if (questionSetEditQuestionPrevButtonElement)
        {
            questionSetEditQuestionPrevButtonElement.disabled=questionSetEditQuestionCursorHistory.length===0;
        }

    if (questionSetEditQuestionNextButtonElement)
        {
            questionSetEditQuestionNextButtonElement.disabled=!hasMore || questionSetEditQuestionNextCursor===null;
        }
}

function questionSetEditConfigListRender(gameConfigList:QuestionSetShowItem[]):void
{
    const questionSetEditConfigListContainerElement=document.getElementById("question-set-edit-config-list-container");

    if (!questionSetEditConfigListContainerElement)
        {
            return;
        }

    questionSetEditConfigListContainerElement.innerHTML="";

    if (gameConfigList.length===0)
        {
            const emptyStateElement=document.createElement("div");
            emptyStateElement.className="question-set-empty-state";
            emptyStateElement.textContent="No question sets found.";
            questionSetEditConfigListContainerElement.appendChild(emptyStateElement);
            return;
        }

    for (const gameConfigCurrent of gameConfigList)
        {
            const configCardElement=document.createElement("div");
            configCardElement.className="question-set-config-card";

            const configHeaderElement=document.createElement("div");
            configHeaderElement.className="question-set-config-header";

            const configTitleWrapperElement=document.createElement("div");

            const configTitleElement=document.createElement("div");
            configTitleElement.className="question-set-config-title";
            configTitleElement.textContent=gameConfigCurrent.gameConfigName;

            const configMetaElement=document.createElement("div");
            configMetaElement.className="question-set-config-meta";
            configMetaElement.textContent=
                "Set #" +
                String(gameConfigCurrent.id) +
                " • " +
                String(gameConfigCurrent.questionCountTarget) +
                " questions • Active: " +
                (gameConfigCurrent.isActive ? "Yes" : "No");

            const configBadgeElement=document.createElement("div");
            configBadgeElement.className="question-set-config-badge";
            configBadgeElement.textContent=gameConfigCurrent.isActive ? "Active" : "Saved";

            configTitleWrapperElement.appendChild(configTitleElement);
            configTitleWrapperElement.appendChild(configMetaElement);

            configHeaderElement.appendChild(configTitleWrapperElement);
            configHeaderElement.appendChild(configBadgeElement);

            const configButtonRowElement=document.createElement("div");
            configButtonRowElement.className="question-set-config-action-row";

            const configButtonElement=document.createElement("button");
            configButtonElement.type="button";
            configButtonElement.textContent="Select This Set";
            configButtonElement.addEventListener("click",() => {
                questionSetEditConfigSelectionFill(gameConfigCurrent);
                questionSetEditMessageTextSet("Question set loaded. You can now update it.");
            });

            configButtonRowElement.appendChild(configButtonElement);

            configCardElement.appendChild(configHeaderElement);
            configCardElement.appendChild(configButtonRowElement);

            questionSetEditConfigListContainerElement.appendChild(configCardElement);
        }
}

function questionSetEditQuestionListRender(questionList:QuestionShowItem[]):void
{
    const questionSetEditQuestionListContainerElement=document.getElementById("question-set-edit-question-list-container");

    if (!questionSetEditQuestionListContainerElement)
        {
            return;
        }

    questionSetEditQuestionListContainerElement.innerHTML="";

    if (questionList.length===0)
        {
            const emptyStateElement=document.createElement("div");
            emptyStateElement.className="question-set-empty-state";
            emptyStateElement.textContent="No questions found.";
            questionSetEditQuestionListContainerElement.appendChild(emptyStateElement);
            return;
        }

    for (const questionCurrent of questionList)
        {
            const questionRowElement=document.createElement("div");
            questionRowElement.className="question-set-question-row";

            const questionSelectLabelElement=document.createElement("label");
            questionSelectLabelElement.className="question-set-outside-select";

            const questionCheckboxElement=document.createElement("input");
            questionCheckboxElement.type="checkbox";
            questionCheckboxElement.name="questionSetEditQuestionSelect";
            questionCheckboxElement.value=String(questionCurrent.questionId);
            questionCheckboxElement.checked=questionSetEditSelectedQuestionIdSet.has(questionCurrent.questionId);

            questionCheckboxElement.addEventListener("change",() => {
                if (questionCheckboxElement.checked)
                    {
                        questionSetEditSelectedQuestionIdSet.add(questionCurrent.questionId);
                    }
                else
                    {
                        questionSetEditSelectedQuestionIdSet.delete(questionCurrent.questionId);
                    }

                questionSetEditCountUpdate();
            });

            questionSelectLabelElement.appendChild(questionCheckboxElement);

            const questionCardElement=document.createElement("div");
            questionCardElement.className="question-set-question-card";

            const questionHeaderElement=document.createElement("div");
            questionHeaderElement.className="question-set-question-header";

            const questionTitleWrapperElement=document.createElement("div");

            const questionTitleElement=document.createElement("div");
            questionTitleElement.className="question-set-question-title";
            questionTitleElement.textContent=questionCurrent.questionText;

            const questionMetaElement=document.createElement("div");
            questionMetaElement.className="question-set-question-meta";
            questionMetaElement.textContent=
                "Question #" +
                String(questionCurrent.questionId) +
                " • Type: " +
                questionCurrent.questionType;

            const questionBadgeElement=document.createElement("div");
            questionBadgeElement.className="question-set-question-badge";
            questionBadgeElement.textContent=questionCurrent.questionType;

            questionTitleWrapperElement.appendChild(questionTitleElement);
            questionTitleWrapperElement.appendChild(questionMetaElement);

            questionHeaderElement.appendChild(questionTitleWrapperElement);
            questionHeaderElement.appendChild(questionBadgeElement);

            const answerOptionListElement=document.createElement("div");
            answerOptionListElement.className="question-set-question-options-inline";

            const answerOptionTextList:string[]=[];

            for (const answerOptionCurrent of questionCurrent.answerOptions)
                {
                    answerOptionTextList.push(answerOptionCurrent.text);
                }

            answerOptionListElement.textContent="• " + answerOptionTextList.join("      •      ");

            questionCardElement.appendChild(questionHeaderElement);
            questionCardElement.appendChild(answerOptionListElement);

            questionRowElement.appendChild(questionSelectLabelElement);
            questionRowElement.appendChild(questionCardElement);

            questionSetEditQuestionListContainerElement.appendChild(questionRowElement);
        }
}

async function questionSetEditConfigPageLoad():Promise<void>
{
    const uidCurrent=authUidGet();

    if (!uidCurrent)
        {
            return;
        }

    const response=await fetch(
        questionSetShowApiUrl +
        "?uid=" + encodeURIComponent(uidCurrent) +
        "&cursor=" + encodeURIComponent(String(questionSetEditConfigCursorCurrent)) +
        "&limit=5",
        {
            method:"GET"
        }
    );

    const questionSetShowResponse:QuestionSetShowResponse=await response.json();

    if (questionSetShowResponse.error)
        {
            questionSetEditMessageTextSet(questionSetShowResponse.error);
            return;
        }

    questionSetEditConfigNextCursor=questionSetShowResponse.nextCursor;
    questionSetEditConfigListRender(questionSetShowResponse.gameConfigs);
    questionSetEditConfigPaginationButtonsUpdate(questionSetShowResponse.hasMore);
}

async function questionSetEditQuestionPageLoad():Promise<void>
{
    const uidCurrent=authUidGet();

    if (!uidCurrent)
        {
            return;
        }

    const response=await fetch(
        questionShowApiUrl +
        "?uid=" + encodeURIComponent(uidCurrent) +
        "&cursor=" + encodeURIComponent(String(questionSetEditQuestionCursorCurrent)) +
        "&limit=5",
        {
            method:"GET"
        }
    );

    const questionShowResponse:QuestionShowResponse=await response.json();

    if (questionShowResponse.error)
        {
            questionSetEditMessageTextSet(questionShowResponse.error);
            return;
        }

    questionSetEditQuestionNextCursor=questionShowResponse.nextCursor;
    questionSetEditQuestionListRender(questionShowResponse.questions);
    questionSetEditQuestionPaginationButtonsUpdate(questionShowResponse.hasMore);
}

async function questionSetEditSubmit(event:Event):Promise<void>
{
    event.preventDefault();

    if (questionSetEditAccessCheck()!==true)
        {
            return;
        }

    const uidCurrent=authUidGet();

    const questionSetEditNameInputElement=document.getElementById("question-set-edit-name-input") as HTMLInputElement | null;
    const questionSetEditMakeActiveInputElement=document.getElementById("question-set-edit-make-active-input") as HTMLInputElement | null;

    if (
        !uidCurrent ||
        !questionSetEditNameInputElement ||
        !questionSetEditMakeActiveInputElement
    )
        {
            return;
        }

    if (questionSetEditSelectedGameConfigId===null)
        {
            questionSetEditMessageTextSet("Please select a question set first.");
            return;
        }

    const gameConfigIdRaw=String(questionSetEditSelectedGameConfigId);
    const gameConfigName=questionSetEditNameInputElement.value.trim();
    const makeActive=questionSetEditMakeActiveInputElement.checked;

    if (gameConfigName==="")
        {
            questionSetEditMessageTextSet("Question set name is required.");
            return;
        }

    const questionIdListAllowed=Array.from(questionSetEditSelectedQuestionIdSet);

    if (questionIdListAllowed.length===0)
        {
            questionSetEditMessageTextSet("Please select at least one question.");
            return;
        }

    questionSetEditMessageTextSet("Editing question set...");

    const questionSetEditFormData=new FormData();
    questionSetEditFormData.append("uid",uidCurrent);
    questionSetEditFormData.append("gameConfigId",gameConfigIdRaw);
    questionSetEditFormData.append("gameConfigName",gameConfigName);
    questionSetEditFormData.append("questionIdListAllowed",JSON.stringify(questionIdListAllowed));
    questionSetEditFormData.append("makeActive",makeActive ? "true" : "false");

    const response=await fetch(questionSetEditApiUrl,
        {
            method:"POST",
            body:questionSetEditFormData
        }
    );

    const questionSetEditResponse:QuestionSetEditResponse=await response.json();

    if (questionSetEditResponse.error)
        {
            questionSetEditMessageTextSet(questionSetEditResponse.error);
            return;
        }

    questionSetEditMessageTextSet("Question set updated successfully for " + questionSetEditResponse.gameConfigName + ".");

    await questionSetEditConfigPageLoad();
}

function questionSetEditConfigPaginationInitialize():void
{
    const questionSetEditConfigPrevButtonElement=document.getElementById("question-set-edit-config-prev-button");
    const questionSetEditConfigNextButtonElement=document.getElementById("question-set-edit-config-next-button");

    if (questionSetEditConfigPrevButtonElement)
        {
            questionSetEditConfigPrevButtonElement.addEventListener("click",async () => {
                if (questionSetEditConfigCursorHistory.length===0)
                    {
                        return;
                    }

                const cursorPrevious=questionSetEditConfigCursorHistory.pop();

                if (cursorPrevious===undefined)
                    {
                        return;
                    }

                questionSetEditConfigCursorCurrent=cursorPrevious;
                await questionSetEditConfigPageLoad();
            });
        }

    if (questionSetEditConfigNextButtonElement)
        {
            questionSetEditConfigNextButtonElement.addEventListener("click",async () => {
                if (questionSetEditConfigNextCursor===null)
                    {
                        return;
                    }

                questionSetEditConfigCursorHistory.push(questionSetEditConfigCursorCurrent);
                questionSetEditConfigCursorCurrent=questionSetEditConfigNextCursor;
                await questionSetEditConfigPageLoad();
            });
        }
}

function questionSetEditQuestionPaginationInitialize():void
{
    const questionSetEditQuestionPrevButtonElement=document.getElementById("question-set-edit-question-prev-button");
    const questionSetEditQuestionNextButtonElement=document.getElementById("question-set-edit-question-next-button");

    if (questionSetEditQuestionPrevButtonElement)
        {
            questionSetEditQuestionPrevButtonElement.addEventListener("click",async () => {
                if (questionSetEditQuestionCursorHistory.length===0)
                    {
                        return;
                    }

                const cursorPrevious=questionSetEditQuestionCursorHistory.pop();

                if (cursorPrevious===undefined)
                    {
                        return;
                    }

                questionSetEditQuestionCursorCurrent=cursorPrevious;
                await questionSetEditQuestionPageLoad();
            });
        }

    if (questionSetEditQuestionNextButtonElement)
        {
            questionSetEditQuestionNextButtonElement.addEventListener("click",async () => {
                if (questionSetEditQuestionNextCursor===null)
                    {
                        return;
                    }

                questionSetEditQuestionCursorHistory.push(questionSetEditQuestionCursorCurrent);
                questionSetEditQuestionCursorCurrent=questionSetEditQuestionNextCursor;
                await questionSetEditQuestionPageLoad();
            });
        }
}

async function questionSetEditPageInitialize():Promise<void>
{
    authNavbarUpdate();

    const questionSetEditFormElement=document.getElementById("question-set-edit-form") as HTMLFormElement | null;
    const questionSetEditConfigListContainerElement=document.getElementById("question-set-edit-config-list-container");
    const questionSetEditQuestionListContainerElement=document.getElementById("question-set-edit-question-list-container");

    if (
        !questionSetEditFormElement ||
        !questionSetEditConfigListContainerElement ||
        !questionSetEditQuestionListContainerElement
    )
        {
            return;
        }

    if (questionSetEditAccessCheck()!==true)
        {
            questionSetEditFormElement.style.display="none";
            questionSetEditConfigListContainerElement.style.display="none";
            questionSetEditQuestionListContainerElement.style.display="none";
            return;
        }

    questionSetEditFormElement.addEventListener("submit",questionSetEditSubmit);

    questionSetEditConfigPaginationInitialize();
    questionSetEditQuestionPaginationInitialize();

    await questionSetEditConfigPageLoad();
    await questionSetEditQuestionPageLoad();
}

questionSetEditPageInitialize();