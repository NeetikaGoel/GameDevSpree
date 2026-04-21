export {};  //SO IT DOESNT GIVE ERRORRRRRRRRRR

import {
    authUserSessionSave,
    authUserSessionClear,
    authUidGet,
    authLoginTypeGet,
    authNavbarUpdate
} from "./auth.js";

const registerUserApiUrl="../backend/api/v1/registerUser.php";
const loginGuestApiUrl="../backend/api/v1/loginGuest.php";

type RegisterUserResponse=
{
    uid:number;
    userId:string;
    email:string;
    name:string;
    loginType:string;
    permissionGroup:string;
    error?:string;
};

type LoginGuestResponse=
{
    uid:number;
    userId:string;
    loginType:string;
    permissionGroup:string;
    error?:string;
};

function registerMessageTextSet(message:string):void
{
    const registerMessageTextElement=document.getElementById("register-message-text");

    if (registerMessageTextElement)
        {
            registerMessageTextElement.textContent=message;
        }
}

async function registerGuestLoginSubmit():Promise<void>
{
    const uidCurrent=authUidGet();
    const loginTypeCurrent=authLoginTypeGet();

    if (uidCurrent && loginTypeCurrent==="guest")
        {
            window.location.href="quiz.html";
            return;
        }

    registerMessageTextSet("Logging in as guest...");

    authUserSessionClear();

    const response=await fetch(loginGuestApiUrl,
        {
            method:"POST"
        }
    );

    const loginGuestResponse:LoginGuestResponse=await response.json();

    if (loginGuestResponse.error)
        {
            registerMessageTextSet(loginGuestResponse.error);
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

async function registerUserSubmit(event:Event):Promise<void>
{
    event.preventDefault();

    const registerNameInputElement=document.getElementById("register-name-input") as HTMLInputElement | null;
    const registerEmailInputElement=document.getElementById("register-email-input") as HTMLInputElement | null;
    const registerPasswordInputElement=document.getElementById("register-password-input") as HTMLInputElement | null;

    if (!registerNameInputElement || !registerEmailInputElement || !registerPasswordInputElement)
    {
        return;
    }

    const name=registerNameInputElement.value.trim();
    const email=registerEmailInputElement.value.trim();
    const password=registerPasswordInputElement.value.trim();
    const uidCurrent=authUidGet();

    if (name==="" || email==="" || password==="")
    {
        registerMessageTextSet("Name, email and password are required.");
        return;
    }

    registerMessageTextSet("Registering user...");

    const registerFormData=new FormData();
    registerFormData.append("name",name);
    registerFormData.append("email",email);
    registerFormData.append("password",password);

    if (uidCurrent)
    {
        registerFormData.append("uid",uidCurrent);
    }

    const response=await fetch(registerUserApiUrl,
    {
        method:"POST",
        body:registerFormData
    });

    const registerUserResponse:RegisterUserResponse=await response.json();

    if (registerUserResponse.error)
    {
        registerMessageTextSet(registerUserResponse.error);
        return;
    }

    authUserSessionSave(
        String(registerUserResponse.uid),
        registerUserResponse.userId,
        registerUserResponse.loginType,
        registerUserResponse.permissionGroup,
        registerUserResponse.name,
        registerUserResponse.email
    );

    window.location.href="quiz.html";
}

function registerPageInitialize():void
{
    authNavbarUpdate();

    const registerUserFormElement=document.getElementById("register-user-form") as HTMLFormElement | null;
    const registerGuestLoginButtonElement=document.getElementById("register-guest-login-button");

    if (registerUserFormElement)
        {
            registerUserFormElement.addEventListener("submit",registerUserSubmit);
        }

    if (registerGuestLoginButtonElement)
        {
            registerGuestLoginButtonElement.addEventListener("click",() => {
                registerGuestLoginSubmit();
            });
        }
}

registerPageInitialize();