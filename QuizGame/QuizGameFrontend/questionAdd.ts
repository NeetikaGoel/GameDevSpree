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
    const questionAddMessageBoxElement=document.getElementById("question-add-message-box");
    const questionAddMessageTextElement=document.getElementById("question-add-message-text");

    if (!questionAddMessageBoxElement || !questionAddMessageTextElement)
        {
            return;
        }

    if (message.trim()==="")
        {
            questionAddMessageBoxElement.classList.add("ui-hidden");
            questionAddMessageTextElement.textContent="";
            return;
        }

    questionAddMessageBoxElement.classList.remove("ui-hidden");
    questionAddMessageTextElement.textContent=message;
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

function questionAddTypeUiUpdate():void
{
    const questionTypeInputElement=document.getElementById("question-type-input") as HTMLSelectElement | null;
    const questionAddMcqSectionElement=document.getElementById("question-add-mcq-section");
    const questionAddTrueFalseSectionElement=document.getElementById("question-add-true-false-section");

    if (
        !questionTypeInputElement ||
        !questionAddMcqSectionElement ||
        !questionAddTrueFalseSectionElement
    )
        {
            return;
        }

    if (questionTypeInputElement.value==="true/false")
        {
            questionAddMcqSectionElement.classList.add("ui-hidden");
            questionAddTrueFalseSectionElement.classList.remove("ui-hidden");
        }
    else
        {
            questionAddMcqSectionElement.classList.remove("ui-hidden");
            questionAddTrueFalseSectionElement.classList.add("ui-hidden");
        }
}

function questionAddFormReset():void
{
    const questionTextInputElement=document.getElementById("question-text-input") as HTMLInputElement | null;
    const questionTypeInputElement=document.getElementById("question-type-input") as HTMLSelectElement | null;

    const answerOption1TextInputElement=document.getElementById("answer-option-1-text-input") as HTMLInputElement | null;
    const answerOption2TextInputElement=document.getElementById("answer-option-2-text-input") as HTMLInputElement | null;
    const answerOption3TextInputElement=document.getElementById("answer-option-3-text-input") as HTMLInputElement | null;
    const answerOption4TextInputElement=document.getElementById("answer-option-4-text-input") as HTMLInputElement | null;

    if (questionTextInputElement)
        {
            questionTextInputElement.value="";
        }

    if (questionTypeInputElement)
        {
            questionTypeInputElement.value="mcq";
        }

    if (answerOption1TextInputElement)
        {
            answerOption1TextInputElement.value="";
        }

    if (answerOption2TextInputElement)
        {
            answerOption2TextInputElement.value="";
        }

    if (answerOption3TextInputElement)
        {
            answerOption3TextInputElement.value="";
        }

    if (answerOption4TextInputElement)
        {
            answerOption4TextInputElement.value="";
        }

    const answerOptionCorrectRadioElementList=document.querySelectorAll('input[name="answerOptionCorrectIndex"]') as NodeListOf<HTMLInputElement>;

    answerOptionCorrectRadioElementList.forEach((answerOptionCorrectRadioElement) => {
        answerOptionCorrectRadioElement.checked=false;
    });

    const trueFalseCorrectRadioElementList=document.querySelectorAll('input[name="trueFalseCorrectValue"]') as NodeListOf<HTMLInputElement>;

    trueFalseCorrectRadioElementList.forEach((trueFalseCorrectRadioElement) => {
        trueFalseCorrectRadioElement.checked=false;
    });

    questionAddTypeUiUpdate();
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
    const questionTypeInputElement=document.getElementById("question-type-input") as HTMLSelectElement | null;

    const answerOption1TextInputElement=document.getElementById("answer-option-1-text-input") as HTMLInputElement | null;
    const answerOption2TextInputElement=document.getElementById("answer-option-2-text-input") as HTMLInputElement | null;
    const answerOption3TextInputElement=document.getElementById("answer-option-3-text-input") as HTMLInputElement | null;
    const answerOption4TextInputElement=document.getElementById("answer-option-4-text-input") as HTMLInputElement | null;

    if (
        !uidCurrent ||
        !questionTextInputElement ||
        !questionTypeInputElement ||
        !answerOption1TextInputElement ||
        !answerOption2TextInputElement ||
        !answerOption3TextInputElement ||
        !answerOption4TextInputElement
    )
        {
            return;
        }

    const questionText=questionTextInputElement.value.trim();
    const questionType=questionTypeInputElement.value.trim();

    if (questionText==="" || questionType==="")
        {
            questionAddMessageTextSet("Question text and question type are required.");
            return;
        }

    const answerOptions:
    {
        text:string;
        type:string;
        isCorrect:boolean;
    }[]=[];

    if (questionType==="true/false")
        {
            const trueFalseCorrectValueElement=document.querySelector('input[name="trueFalseCorrectValue"]:checked') as HTMLInputElement | null;

            if (!trueFalseCorrectValueElement)
                {
                    questionAddMessageTextSet("Please choose whether True or False is the correct answer.");
                    return;
                }

            const isTrueCorrect=trueFalseCorrectValueElement.value==="true";

            answerOptions.push(
                {
                    text:"True",
                    type:"true/false",
                    isCorrect:isTrueCorrect
                }
            );

            answerOptions.push(
                {
                    text:"False",
                    type:"true/false",
                    isCorrect:!isTrueCorrect
                }
            );
        }
    else
        {
            const answerOptionCorrectIndexElement=document.querySelector('input[name="answerOptionCorrectIndex"]:checked') as HTMLInputElement | null;

            if (!answerOptionCorrectIndexElement)
                {
                    questionAddMessageTextSet("Please mark exactly one correct answer option.");
                    return;
                }

            const answerOptionCorrectIndex=parseInt(answerOptionCorrectIndexElement.value,10);

            const answerOptionTextList=[
                answerOption1TextInputElement.value.trim(),
                answerOption2TextInputElement.value.trim(),
                answerOption3TextInputElement.value.trim(),
                answerOption4TextInputElement.value.trim()
            ];

            for (let index=0; index<answerOptionTextList.length; index++)
                {
                    if (answerOptionTextList[index]==="")
                        {
                            questionAddMessageTextSet("All MCQ option fields are required.");
                            return;
                        }

                    answerOptions.push(
                        {
                            text:answerOptionTextList[index],
                            type:"mcq",
                            isCorrect:index===answerOptionCorrectIndex
                        }
                    );
                }
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
    questionAddFormReset();
}

function questionAddPageInitialize():void
{
    authNavbarUpdate();

    const questionAddFormElement=document.getElementById("question-add-form") as HTMLFormElement | null;
    const questionTypeInputElement=document.getElementById("question-type-input") as HTMLSelectElement | null;

    if (!questionAddFormElement)
        {
            return;
        }

    if (questionAddAccessCheck()!==true)
        {
            questionAddFormElement.style.display="none";
            return;
        }

    if (questionTypeInputElement)
        {
            questionTypeInputElement.addEventListener("change",() => {
                questionAddTypeUiUpdate();
            });
        }

    questionAddTypeUiUpdate();
    questionAddFormElement.addEventListener("submit",questionAddSubmit);
}

questionAddPageInitialize();