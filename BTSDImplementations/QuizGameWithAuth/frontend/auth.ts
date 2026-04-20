export {}; 



// shared auth helper file!!

// set/get/delete cookies
// save/clear logged-in user info

//COOKIE NAMES FOR ALL AUTH THINGS
const authUidCookieName="uid";
const authUserIdCookieName="userId";
const authLoginTypeCookieName="loginType";
const authPermissionGroupCookieName="permissionGroup";
const authNameCookieName="name";
const authEmailCookieName="email";

//COOKIE SET FUNCTION
export function authCookieSet(cookieName:string,cookieValue:string):void
{
    document.cookie=
        encodeURIComponent(cookieName) +
        "=" +
        encodeURIComponent(cookieValue) +
        "; path=/";
}

//COOKIE GET FUNCTION
export function authCookieGet(cookieName:string):string | null
{
    const authCookieNameEncoded=encodeURIComponent(cookieName) + "=";
    const authCookieParts=document.cookie.split(";");

    for (const authCookiePartCurrent of authCookieParts)
        {
            const authCookiePartTrimmed=authCookiePartCurrent.trim();

            if (authCookiePartTrimmed.startsWith(authCookieNameEncoded))
                {
                    return decodeURIComponent(authCookiePartTrimmed.substring(authCookieNameEncoded.length));
                }
        }

    return null;
}

//COOKIE DELETE FUNCTION
export function authCookieDelete(cookieName:string):void
{
    document.cookie=
        encodeURIComponent(cookieName) +
        "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
}

//SAVE COMPLETE USER SESSION IN COOKIES
export function authUserSessionSave(
    uid:string,
    userId:string,
    loginType:string,
    permissionGroup:string,
    name:string="",
    email:string=""
):void
{
    authCookieSet(authUidCookieName,uid);
    authCookieSet(authUserIdCookieName,userId);
    authCookieSet(authLoginTypeCookieName,loginType);
    authCookieSet(authPermissionGroupCookieName,permissionGroup);

    if (name!=="")
        {
            authCookieSet(authNameCookieName,name);
        }

    if (email!=="")
        {
            authCookieSet(authEmailCookieName,email);
        }
}

//CLEAR COMPLETE USER SESSION
export function authUserSessionClear():void
{
    authCookieDelete(authUidCookieName);
    authCookieDelete(authUserIdCookieName);
    authCookieDelete(authLoginTypeCookieName);
    authCookieDelete(authPermissionGroupCookieName);
    authCookieDelete(authNameCookieName);
    authCookieDelete(authEmailCookieName);
}

//GETTERS
export function authUidGet():string | null
{
    return authCookieGet(authUidCookieName);
}

export function authUserIdGet():string | null
{
    return authCookieGet(authUserIdCookieName);
}

export function authLoginTypeGet():string | null
{
    return authCookieGet(authLoginTypeCookieName);
}

export function authPermissionGroupGet():string | null
{
    return authCookieGet(authPermissionGroupCookieName);
}

export function authNameGet():string | null
{
    return authCookieGet(authNameCookieName);
}

export function authEmailGet():string | null
{
    return authCookieGet(authEmailCookieName);
}

//CHECKS
export function authIsLoggedIn():boolean
{
    const uid=authUidGet();

    if (!uid || uid.trim()==="")
        {
            return false;
        }

    return true;
}

export function authIsGuest():boolean
{
    return authLoginTypeGet()==="guest";
}

export function authIsAdmin():boolean
{
    return authPermissionGroupGet()==="admin";
}

export function authCurrentUserDisplayTextGet():string
{
    const loginType=authLoginTypeGet();
    const userId=authUserIdGet();
    const name=authNameGet();

    if (!authIsLoggedIn())
        {
            return "Not logged in";
        }

    if (loginType==="guest")
        {
            return "Guest User: " + (userId ?? "");
        }

    if (name && name.trim()!=="")
        {
            return "User: " + name;
        }

    return "User: " + (userId ?? "");
}

//NAVBAR UPDATE FUNCTION
export function authNavbarUpdate():void
{
    const navLoginLinkElement=document.getElementById("nav-login-link") as HTMLAnchorElement | null;
    const navRegisterLinkElement=document.getElementById("nav-register-link") as HTMLAnchorElement | null;
    const navLogoutButtonElement=document.getElementById("nav-logout-button") as HTMLButtonElement | null;
    const navAddQuestionLinkElement=document.getElementById("nav-add-question-link") as HTMLAnchorElement | null;
    const navUserTextElement=document.getElementById("nav-user-text");

    if (navUserTextElement)
        {
            navUserTextElement.textContent=authCurrentUserDisplayTextGet();
        }

    if (navLoginLinkElement)
        {
            navLoginLinkElement.style.display=authIsLoggedIn() ? "none" : "inline-block";
        }

    if (navRegisterLinkElement)
        {
            navRegisterLinkElement.style.display=authIsLoggedIn() ? "none" : "inline-block";
        }

    if (navLogoutButtonElement)
        {
            navLogoutButtonElement.style.display=authIsLoggedIn() ? "inline-block" : "none";

            navLogoutButtonElement.onclick=():void =>
            {
                authUserSessionClear();
                window.location.href="index.html";
            };
        }

    if (navAddQuestionLinkElement)
        {
            navAddQuestionLinkElement.style.display=authIsAdmin() ? "inline-block" : "none";
        }
}