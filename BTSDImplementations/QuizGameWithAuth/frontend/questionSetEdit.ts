export {};

import {
    authUidGet,
    authPermissionGroupGet,
    authNavbarUpdate
} from "./auth.js";

const questionSetEditApiUrl="../backend/api/v1/questionSetEdit.php";

type QuestionSetEditResponse=
{
    gameConfigName:string;
    questionCountTarget:number;
    questionIdListAllowed:number[];
    secretKey:string;
    isUpdated:boolean;
    error?:string;
};

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

async function questionSetEditSubmit(event:Event):Promise<void>
{
    event.preventDefault();

    if (questionSetEditAccessCheck()!==true)
        {
            return;
        }

    const uidCurrent=authUidGet();

    const questionSetEditNameInputElement=document.getElementById("question-set-edit-name-input") as HTMLInputElement | null;
    const questionSetEditCountInputElement=document.getElementById("question-set-edit-count-input") as HTMLInputElement | null;
    const questionSetEditIdListInputElement=document.getElementById("question-set-edit-id-list-input") as HTMLInputElement | null;
    const questionSetEditSecretKeyInputElement=document.getElementById("question-set-edit-secret-key-input") as HTMLInputElement | null;

    if (
        !uidCurrent ||
        !questionSetEditNameInputElement ||
        !questionSetEditCountInputElement ||
        !questionSetEditIdListInputElement ||
        !questionSetEditSecretKeyInputElement
    )
        {
            return;
        }

    const gameConfigName=questionSetEditNameInputElement.value.trim();
    const questionCountTargetRaw=questionSetEditCountInputElement.value.trim();
    const questionIdListAllowedRaw=questionSetEditIdListInputElement.value.trim();
    const secretKey=questionSetEditSecretKeyInputElement.value.trim();

    if (gameConfigName==="" || questionCountTargetRaw==="" || questionIdListAllowedRaw==="" || secretKey==="")
        {
            questionSetEditMessageTextSet("All fields are required.");
            return;
        }

    const questionCountTarget=parseInt(questionCountTargetRaw,10);

    if (Number.isNaN(questionCountTarget) || questionCountTarget<=0)
        {
            questionSetEditMessageTextSet("Question count target must be a positive number.");
            return;
        }

    const questionIdListAllowed=questionIdListAllowedRaw
        .split(",")
        .map((questionIdCurrent) => parseInt(questionIdCurrent.trim(),10))
        .filter((questionIdCurrent) => !Number.isNaN(questionIdCurrent) && questionIdCurrent>0);

    if (questionIdListAllowed.length===0)
        {
            questionSetEditMessageTextSet("Please enter at least one valid question id.");
            return;
        }

    const questionIdListAllowedUnique:number[]=[];
    const questionIdListAllowedSeen:Set<number>=new Set<number>();

    for (const questionIdCurrent of questionIdListAllowed)
        {
            if (!questionIdListAllowedSeen.has(questionIdCurrent))
                {
                    questionIdListAllowedSeen.add(questionIdCurrent);
                    questionIdListAllowedUnique.push(questionIdCurrent);
                }
        }

    if (questionCountTarget>questionIdListAllowedUnique.length)
        {
            questionSetEditMessageTextSet("Question count target cannot be more than number of allowed question ids.");
            return;
        }

    questionSetEditMessageTextSet("Editing question set...");

    const questionSetEditFormData=new FormData();
    questionSetEditFormData.append("uid",uidCurrent);
    questionSetEditFormData.append("gameConfigName",gameConfigName);
    questionSetEditFormData.append("questionCountTarget",String(questionCountTarget));
    questionSetEditFormData.append("questionIdListAllowed",JSON.stringify(questionIdListAllowedUnique));
    questionSetEditFormData.append("secretKey",secretKey);

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

    questionSetEditNameInputElement.value="";
    questionSetEditCountInputElement.value="";
    questionSetEditIdListInputElement.value="";
    questionSetEditSecretKeyInputElement.value="";
}

function questionSetEditPageInitialize():void
{
    authNavbarUpdate();

    const questionSetEditFormElement=document.getElementById("question-set-edit-form") as HTMLFormElement | null;

    if (!questionSetEditFormElement)
        {
            return;
        }

    if (questionSetEditAccessCheck()!==true)
        {
            questionSetEditFormElement.style.display="none";
            return;
        }

    questionSetEditFormElement.addEventListener("submit",questionSetEditSubmit);
}

questionSetEditPageInitialize();