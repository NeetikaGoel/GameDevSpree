export {};

import {
    authUidGet,
    authPermissionGroupGet,
    authNavbarUpdate
} from "./auth.js";

const questionSetCreateApiUrl="../backend/api/questionSetCreate.php";

type QuestionSetCreateResponse=
{
    gameConfigId:number;
    gameConfigName:string;
    questionCountTarget:number;
    questionIdListAllowed:number[];
    secretKey:string;
    isCreated:boolean;
    error?:string;
};

function questionSetCreateMessageTextSet(message:string):void
{
    const questionSetCreateMessageTextElement=document.getElementById("question-set-create-message-text");

    if (questionSetCreateMessageTextElement)
        {
            questionSetCreateMessageTextElement.textContent=message;
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

async function questionSetCreateSubmit(event:Event):Promise<void>
{
    event.preventDefault();

    if (questionSetCreateAccessCheck()!==true)
        {
            return;
        }

    const uidCurrent=authUidGet();

    const questionSetCreateNameInputElement=document.getElementById("question-set-create-name-input") as HTMLInputElement | null;
    const questionSetCreateCountInputElement=document.getElementById("question-set-create-count-input") as HTMLInputElement | null;
    const questionSetCreateIdListInputElement=document.getElementById("question-set-create-id-list-input") as HTMLInputElement | null;
    const questionSetCreateSecretKeyInputElement=document.getElementById("question-set-create-secret-key-input") as HTMLInputElement | null;

    if (
        !uidCurrent ||
        !questionSetCreateNameInputElement ||
        !questionSetCreateCountInputElement ||
        !questionSetCreateIdListInputElement ||
        !questionSetCreateSecretKeyInputElement
    )
        {
            return;
        }

    const gameConfigName=questionSetCreateNameInputElement.value.trim();
    const questionCountTargetRaw=questionSetCreateCountInputElement.value.trim();
    const questionIdListAllowedRaw=questionSetCreateIdListInputElement.value.trim();
    const secretKey=questionSetCreateSecretKeyInputElement.value.trim();

    if (gameConfigName==="" || questionCountTargetRaw==="" || questionIdListAllowedRaw==="" || secretKey==="")
        {
            questionSetCreateMessageTextSet("All fields are required.");
            return;
        }

    const questionCountTarget=parseInt(questionCountTargetRaw,10);

    if (Number.isNaN(questionCountTarget) || questionCountTarget<=0)
        {
            questionSetCreateMessageTextSet("Question count target must be a positive number.");
            return;
        }

    const questionIdListAllowed=questionIdListAllowedRaw
        .split(",")
        .map((questionIdCurrent) => parseInt(questionIdCurrent.trim(),10))
        .filter((questionIdCurrent) => !Number.isNaN(questionIdCurrent) && questionIdCurrent>0);

    if (questionIdListAllowed.length===0)
        {
            questionSetCreateMessageTextSet("Please enter at least one valid question id.");
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
            questionSetCreateMessageTextSet("Question count target cannot be more than number of allowed question ids.");
            return;
        }

    questionSetCreateMessageTextSet("Creating question set...");

    const questionSetCreateFormData=new FormData();
    questionSetCreateFormData.append("uid",uidCurrent);
    questionSetCreateFormData.append("gameConfigName",gameConfigName);
    questionSetCreateFormData.append("questionCountTarget",String(questionCountTarget));
    questionSetCreateFormData.append("questionIdListAllowed",JSON.stringify(questionIdListAllowedUnique));
    questionSetCreateFormData.append("secretKey",secretKey);

    const response=await fetch(questionSetCreateApiUrl,
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

    questionSetCreateMessageTextSet("Question set created successfully with id " + String(questionSetCreateResponse.gameConfigId) + ".");

    questionSetCreateNameInputElement.value="";
    questionSetCreateCountInputElement.value="";
    questionSetCreateIdListInputElement.value="";
    questionSetCreateSecretKeyInputElement.value="";
}

function questionSetCreatePageInitialize():void
{
    authNavbarUpdate();

    const questionSetCreateFormElement=document.getElementById("question-set-create-form") as HTMLFormElement | null;

    if (!questionSetCreateFormElement)
        {
            return;
        }

    if (questionSetCreateAccessCheck()!==true)
        {
            questionSetCreateFormElement.style.display="none";
            return;
        }

    questionSetCreateFormElement.addEventListener("submit",questionSetCreateSubmit);
}

questionSetCreatePageInitialize();