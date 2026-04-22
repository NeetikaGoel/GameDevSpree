export {};  //SO IT DOESNT GIVE ERRORRRRRRRRRR

import {
    authUserSessionSave,
    authUserSessionClear,
    authUidGet,
    authLoginTypeGet,
    authIsLoggedIn,
    authNavbarUpdate
} from "./auth.js";

const loginGuestApiUrl="../backend/api/v1/loginGuest.php";
const loginUserApiUrl="../backend/api/v1/loginUser.php";

type LoginGuestResponse=
{
    uid:number;
    userId:string;
    loginType:string;
    permissionGroup:string;
    error?:string;
};

type LoginUserResponse=
{
    uid:number;
    userId:string;
    email:string;
    name:string;
    loginType:string;
    permissionGroup:string;
    error?:string;
};

function loginMessageTextSet(message:string):void
{
    const guestLoginMessageTextElement=document.getElementById("guest-login-message-text");
    const loginMessageTextElement=document.getElementById("login-message-text");

    if (guestLoginMessageTextElement)
        {
            guestLoginMessageTextElement.textContent=message;
        }

    if (loginMessageTextElement)
        {
            loginMessageTextElement.textContent=message;
        }
}

async function loginGuestSubmit():Promise<void>
{
    const uidCurrent=authUidGet();
    const loginTypeCurrent=authLoginTypeGet();

    if (uidCurrent && loginTypeCurrent==="guest")
        {
            window.location.href="quiz.html";
            return;
        }

    loginMessageTextSet("Logging in as guest...");

    authUserSessionClear();

    const response=await fetch(loginGuestApiUrl,
        {
            method:"POST"
        }
    );

    const loginGuestResponse:LoginGuestResponse=await response.json();

    if (loginGuestResponse.error)
        {
            loginMessageTextSet(loginGuestResponse.error);
            return;
        }

    authUserSessionSave(
        String(loginGuestResponse.uid),
        loginGuestResponse.userId,
        loginGuestResponse.loginType,
        loginGuestResponse.permissionGroup
    );

    window.location.href="quiz.html";
}

async function loginUserSubmit(event:Event):Promise<void>
{
    event.preventDefault();

    const loginEmailInputElement=document.getElementById("login-email-input") as HTMLInputElement | null;
    const loginPasswordInputElement=document.getElementById("login-password-input") as HTMLInputElement | null;

    if (!loginEmailInputElement || !loginPasswordInputElement)
        {
            return;
        }

    const email=loginEmailInputElement.value.trim();
    const password=loginPasswordInputElement.value.trim();

    if (email==="" || password==="")
        {
            loginMessageTextSet("Email and password are required.");
            return;
        }

    loginMessageTextSet("Logging in...");

    const loginFormData=new FormData();
    loginFormData.append("email",email);
    loginFormData.append("password",password);

    authUserSessionClear();

    const response=await fetch(loginUserApiUrl,
        {
            method:"POST",
            body:loginFormData
        }
    );

    const loginUserResponse:LoginUserResponse=await response.json();

    if (loginUserResponse.error)
        {
            loginMessageTextSet(loginUserResponse.error);
            return;
        }

    authUserSessionSave(
        String(loginUserResponse.uid),
        loginUserResponse.userId,
        loginUserResponse.loginType,
        loginUserResponse.permissionGroup,
        loginUserResponse.name,
        loginUserResponse.email
    );

    window.location.href="quiz.html";
}

function indexStartQuizSubmit():void
{
    if (authIsLoggedIn())
        {
            window.location.href="quiz.html";
            return;
        }

    window.location.href="login.html";
}

async function loadQuestionCount():Promise<void>
{
    const totalQuestionsCountElement=document.getElementById("total-questions-count");

    if (!totalQuestionsCountElement)
        {
            return;
        }

    try
    {
        const uid=authUidGet();

        if (!uid)
            {
                totalQuestionsCountElement.textContent="5";
                return;
            }

        const response=await fetch(`../backend/api/v1/quizLoad.php?uid=${encodeURIComponent(uid)}`);
        const data=await response.json();

        if (data.questionCountTotal)
            {
                totalQuestionsCountElement.textContent=data.questionCountTotal;
            }
        else
            {
                totalQuestionsCountElement.textContent="5";
            }
    }
    catch (error)
    {
        totalQuestionsCountElement.textContent="5";
    }
}

function loginPageInitialize():void
{
    authNavbarUpdate();
    loadQuestionCount();

    const guestLoginButtonElement=document.getElementById("guest-login-button");
    const loginUserFormElement=document.getElementById("login-user-form") as HTMLFormElement | null;
    const indexStartQuizButtonElement=document.getElementById("index-start-quiz-button");

    if (guestLoginButtonElement)
        {
            guestLoginButtonElement.addEventListener("click",() => {
                loginGuestSubmit();
            });
        }

    if (loginUserFormElement)
        {
            loginUserFormElement.addEventListener("submit",loginUserSubmit);
        }

    if (indexStartQuizButtonElement)
        {
            indexStartQuizButtonElement.addEventListener("click",() => {
                indexStartQuizSubmit();
            });
        }
}

loginPageInitialize();