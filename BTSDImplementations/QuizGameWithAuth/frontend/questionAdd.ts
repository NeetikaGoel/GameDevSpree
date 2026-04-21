export {};

import {
    authUidGet,
    authPermissionGroupGet,
    authNavbarUpdate
} from "./auth.js";

const questionAddApiUrl="../backend/api/v1/questionAdd.php";

type QuestionAddResponse=
{
    questionId:number;
    questionText:string;
    questionType:string;
    answerOptionCount:number;
    isCreated:boolean;
    error?:string;
};

function questionAddMessageTextSet(message:string):void
{
    const questionAddMessageTextElement=document.getElementById("question-add-message-text");

    if (questionAddMessageTextElement)
        {
            questionAddMessageTextElement.textContent=message;
        }
}

function questionAddAccessCheck():boolean
{
    const uidCurrent=authUidGet();
    const permissionGroupCurrent=authPermissionGroupGet();

    if (!uidCurrent)
        {
            questionAddMessageTextSet("You must be logged in to access question add.");
            return false;
        }

    if (permissionGroupCurrent!=="admin")
        {
            questionAddMessageTextSet("Only admin user can add questions.");
            return false;
        }

    return true;
}

async function questionAddSubmit(event:Event):Promise<void>
{
    event.preventDefault();

    if (questionAddAccessCheck()!==true)
        {
            return;
        }

    const uidCurrent=authUidGet();

    const questionTextInputElement=document.getElementById("question-text-input") as HTMLInputElement | null;
    const questionTypeInputElement=document.getElementById("question-type-input") as HTMLInputElement | null;

    const answerOption1TextInputElement=document.getElementById("answer-option-1-text-input") as HTMLInputElement | null;
    const answerOption1TypeInputElement=document.getElementById("answer-option-1-type-input") as HTMLInputElement | null;

    const answerOption2TextInputElement=document.getElementById("answer-option-2-text-input") as HTMLInputElement | null;
    const answerOption2TypeInputElement=document.getElementById("answer-option-2-type-input") as HTMLInputElement | null;

    const answerOption3TextInputElement=document.getElementById("answer-option-3-text-input") as HTMLInputElement | null;
    const answerOption3TypeInputElement=document.getElementById("answer-option-3-type-input") as HTMLInputElement | null;

    const answerOption4TextInputElement=document.getElementById("answer-option-4-text-input") as HTMLInputElement | null;
    const answerOption4TypeInputElement=document.getElementById("answer-option-4-type-input") as HTMLInputElement | null;

    const answerOptionCorrectIndexElement=document.querySelector('input[name="answerOptionCorrectIndex"]:checked') as HTMLInputElement | null;

    if (
        !uidCurrent ||
        !questionTextInputElement ||
        !questionTypeInputElement ||
        !answerOption1TextInputElement ||
        !answerOption1TypeInputElement ||
        !answerOption2TextInputElement ||
        !answerOption2TypeInputElement ||
        !answerOption3TextInputElement ||
        !answerOption3TypeInputElement ||
        !answerOption4TextInputElement ||
        !answerOption4TypeInputElement
    )
        {
            return;
        }

    const questionText=questionTextInputElement.value.trim();
    const questionType=questionTypeInputElement.value.trim();

    const answerOptionTextList=[
        answerOption1TextInputElement.value.trim(),
        answerOption2TextInputElement.value.trim(),
        answerOption3TextInputElement.value.trim(),
        answerOption4TextInputElement.value.trim()
    ];

    const answerOptionTypeList=[
        answerOption1TypeInputElement.value.trim(),
        answerOption2TypeInputElement.value.trim(),
        answerOption3TypeInputElement.value.trim(),
        answerOption4TypeInputElement.value.trim()
    ];

    if (questionText==="" || questionType==="")
        {
            questionAddMessageTextSet("Question text and question type are required.");
            return;
        }

    if (!answerOptionCorrectIndexElement)
        {
            questionAddMessageTextSet("Please mark exactly one correct answer option.");
            return;
        }

    const answerOptionCorrectIndex=parseInt(answerOptionCorrectIndexElement.value,10);

    const answerOptions=[];

    for (let index=0; index<answerOptionTextList.length; index++)
        {
            if (answerOptionTextList[index]==="" || answerOptionTypeList[index]==="")
                {
                    questionAddMessageTextSet("All answer option text and type fields are required.");
                    return;
                }

            answerOptions.push(
                {
                    text:answerOptionTextList[index],
                    type:answerOptionTypeList[index],
                    isCorrect:index===answerOptionCorrectIndex
                }
            );
        }

    questionAddMessageTextSet("Adding question...");

    const questionAddFormData=new FormData();
    questionAddFormData.append("uid",uidCurrent);
    questionAddFormData.append("questionText",questionText);
    questionAddFormData.append("questionType",questionType);
    questionAddFormData.append("answerOptions",JSON.stringify(answerOptions));

    const response=await fetch(questionAddApiUrl,
        {
            method:"POST",
            body:questionAddFormData
        }
    );

    const questionAddResponse:QuestionAddResponse=await response.json();

    if (questionAddResponse.error)
        {
            questionAddMessageTextSet(questionAddResponse.error);
            return;
        }

    questionAddMessageTextSet("Question added successfully with id " + String(questionAddResponse.questionId) + ".");

    questionTextInputElement.value="";
    questionTypeInputElement.value="mcq";

    answerOption1TextInputElement.value="";
    answerOption1TypeInputElement.value="mcq";

    answerOption2TextInputElement.value="";
    answerOption2TypeInputElement.value="mcq";

    answerOption3TextInputElement.value="";
    answerOption3TypeInputElement.value="mcq";

    answerOption4TextInputElement.value="";
    answerOption4TypeInputElement.value="mcq";

    const answerOptionCorrectRadioElementList=document.querySelectorAll('input[name="answerOptionCorrectIndex"]') as NodeListOf<HTMLInputElement>;

    answerOptionCorrectRadioElementList.forEach((answerOptionCorrectRadioElement) => {
        answerOptionCorrectRadioElement.checked=false;
    });
}

function questionAddPageInitialize():void
{
    authNavbarUpdate();

    const questionAddFormElement=document.getElementById("question-add-form") as HTMLFormElement | null;

    if (!questionAddFormElement)
        {
            return;
        }

    if (questionAddAccessCheck()!==true)
        {
            questionAddFormElement.style.display="none";
            return;
        }

    questionAddFormElement.addEventListener("submit",questionAddSubmit);
}

questionAddPageInitialize();