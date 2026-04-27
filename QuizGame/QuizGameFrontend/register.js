import { authUserSessionSave, authUserSessionClear, authUidGet, authLoginTypeGet, authNavbarUpdate, authUserQuestionSetPageRedirect } from "./auth.js";
const registerUserApiUrl = "../backend/api/v1/registerUser.php";
const loginGuestApiUrl = "../backend/api/v1/loginGuest.php";
function registerMessageTextSet(message) {
    const registerMessageTextElement = document.getElementById("register-message-text");
    if (registerMessageTextElement) {
        registerMessageTextElement.textContent = message;
    }
}
async function registerGuestLoginSubmit() {
    const uidCurrent = authUidGet();
    const loginTypeCurrent = authLoginTypeGet();
    if (uidCurrent && loginTypeCurrent === "guest") {
        authUserQuestionSetPageRedirect();
        return;
    }
    registerMessageTextSet("Logging in as guest...");
    authUserSessionClear();
    const response = await fetch(loginGuestApiUrl, {
        method: "POST"
    });
    const loginGuestResponse = await response.json();
    if (loginGuestResponse.error) {
        registerMessageTextSet(loginGuestResponse.error);
        return;
    }
    authUserSessionSave(String(loginGuestResponse.uid), loginGuestResponse.userId, loginGuestResponse.loginType, loginGuestResponse.permissionGroup);
    authUserQuestionSetPageRedirect();
}
async function registerUserSubmit(event) {
    event.preventDefault();
    const registerNameInputElement = document.getElementById("register-name-input");
    const registerEmailInputElement = document.getElementById("register-email-input");
    const registerPasswordInputElement = document.getElementById("register-password-input");
    if (!registerNameInputElement || !registerEmailInputElement || !registerPasswordInputElement) {
        return;
    }
    const name = registerNameInputElement.value.trim();
    const email = registerEmailInputElement.value.trim();
    const password = registerPasswordInputElement.value.trim();
    const uidCurrent = authUidGet();
    if (name === "" || email === "" || password === "") {
        registerMessageTextSet("Name, email and password are required.");
        return;
    }
    registerMessageTextSet("Registering user...");
    const registerFormData = new FormData();
    registerFormData.append("name", name);
    registerFormData.append("email", email);
    registerFormData.append("password", password);
    if (uidCurrent) {
        registerFormData.append("uid", uidCurrent);
    }
    const response = await fetch(registerUserApiUrl, {
        method: "POST",
        body: registerFormData
    });
    const registerUserResponse = await response.json();
    if (registerUserResponse.error) {
        registerMessageTextSet(registerUserResponse.error);
        return;
    }
    authUserSessionSave(String(registerUserResponse.uid), registerUserResponse.userId, registerUserResponse.loginType, registerUserResponse.permissionGroup, registerUserResponse.name, registerUserResponse.email);
    authUserQuestionSetPageRedirect();
}
function registerPageInitialize() {
    authNavbarUpdate();
    const registerUserFormElement = document.getElementById("register-user-form");
    const registerGuestLoginButtonElement = document.getElementById("register-guest-login-button");
    if (registerUserFormElement) {
        registerUserFormElement.addEventListener("submit", registerUserSubmit);
    }
    if (registerGuestLoginButtonElement) {
        registerGuestLoginButtonElement.addEventListener("click", () => {
            registerGuestLoginSubmit();
        });
    }
}
registerPageInitialize();
