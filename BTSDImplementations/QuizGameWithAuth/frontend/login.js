import { authUserSessionSave, authUserSessionClear, authUidGet, authLoginTypeGet, authIsLoggedIn, authNavbarUpdate } from "./auth.js";
const loginGuestApiUrl = "../backend/api/loginGuest.php";
const loginUserApiUrl = "../backend/api/loginUser.php";
function loginMessageTextSet(message) {
    const guestLoginMessageTextElement = document.getElementById("guest-login-message-text");
    const loginMessageTextElement = document.getElementById("login-message-text");
    if (guestLoginMessageTextElement) {
        guestLoginMessageTextElement.textContent = message;
    }
    if (loginMessageTextElement) {
        loginMessageTextElement.textContent = message;
    }
}
async function loginGuestSubmit() {
    const uidCurrent = authUidGet();
    const loginTypeCurrent = authLoginTypeGet();
    if (uidCurrent && loginTypeCurrent === "guest") {
        window.location.href = "quiz.html";
        return;
    }
    loginMessageTextSet("Logging in as guest...");
    authUserSessionClear();
    const response = await fetch(loginGuestApiUrl, {
        method: "POST"
    });
    const loginGuestResponse = await response.json();
    if (loginGuestResponse.error) {
        loginMessageTextSet(loginGuestResponse.error);
        return;
    }
    authUserSessionSave(String(loginGuestResponse.uid), loginGuestResponse.userId, loginGuestResponse.loginType, loginGuestResponse.permissionGroup);
    window.location.href = "quiz.html";
}
async function loginUserSubmit(event) {
    event.preventDefault();
    const loginEmailInputElement = document.getElementById("login-email-input");
    const loginPasswordInputElement = document.getElementById("login-password-input");
    if (!loginEmailInputElement || !loginPasswordInputElement) {
        return;
    }
    const email = loginEmailInputElement.value.trim();
    const password = loginPasswordInputElement.value.trim();
    if (email === "" || password === "") {
        loginMessageTextSet("Email and password are required.");
        return;
    }
    loginMessageTextSet("Logging in...");
    const loginFormData = new FormData();
    loginFormData.append("email", email);
    loginFormData.append("password", password);
    authUserSessionClear();
    const response = await fetch(loginUserApiUrl, {
        method: "POST",
        body: loginFormData
    });
    const loginUserResponse = await response.json();
    if (loginUserResponse.error) {
        loginMessageTextSet(loginUserResponse.error);
        return;
    }
    authUserSessionSave(String(loginUserResponse.uid), loginUserResponse.userId, loginUserResponse.loginType, loginUserResponse.permissionGroup, loginUserResponse.name, loginUserResponse.email);
    window.location.href = "quiz.html";
}
function indexStartQuizSubmit() {
    if (authIsLoggedIn()) {
        window.location.href = "quiz.html";
        return;
    }
    window.location.href = "login.html";
}
function loginPageInitialize() {
    authNavbarUpdate();
    const guestLoginButtonElement = document.getElementById("guest-login-button");
    const loginUserFormElement = document.getElementById("login-user-form");
    const indexStartQuizButtonElement = document.getElementById("index-start-quiz-button");
    if (guestLoginButtonElement) {
        guestLoginButtonElement.addEventListener("click", () => {
            loginGuestSubmit();
        });
    }
    if (loginUserFormElement) {
        loginUserFormElement.addEventListener("submit", loginUserSubmit);
    }
    if (indexStartQuizButtonElement) {
        indexStartQuizButtonElement.addEventListener("click", () => {
            indexStartQuizSubmit();
        });
    }
}
loginPageInitialize();
