"use strict";

/* ================= FORM ELEMENTS ================= */

const loginForm = document.getElementById("loginForm");
const signupForm = document.getElementById("signupForm");

const showSignup = document.getElementById("showSignup");
const showLogin = document.getElementById("showLogin");

const login = document.getElementById("login");
const signup = document.getElementById("signup");


/* ================= CLEAR ERRORS ================= */

function clearFormErrors(form) {

    form.querySelectorAll(".input-group").forEach((group) => {
        group.classList.remove("has-error");
    });

    form.querySelectorAll("small").forEach((message) => {
        message.textContent = "";
    });

    form.querySelectorAll("[aria-invalid]").forEach((input) => {
        input.removeAttribute("aria-invalid");
    });
}


/* ================= SWITCH LOGIN / SIGNUP ================= */

function openForm(formName) {

    const showSignupForm = formName === "signup";

    loginForm.classList.toggle("hidden", showSignupForm);
    signupForm.classList.toggle("hidden", !showSignupForm);

    const visibleForm = showSignupForm
        ? signupForm.querySelector("form")
        : loginForm.querySelector("form");

    if (visibleForm) {

        const firstInput = visibleForm.querySelector(
            "input:not([type='hidden'])"
        );

        if (firstInput) {
            firstInput.focus();
        }
    }
}


if (showSignup) {

    showSignup.addEventListener("click", function (event) {

        event.preventDefault();

        clearFormErrors(login);

        openForm("signup");
    });
}


if (showLogin) {

    showLogin.addEventListener("click", function (event) {

        event.preventDefault();

        clearFormErrors(signup);

        openForm("login");
    });
}


/* ================= PASSWORD VISIBILITY ================= */

function setupPasswordToggle(buttonId, inputId) {

    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);

    if (!button || !input) {
        return;
    }

    button.addEventListener("click", function () {

        const isPassword = input.type === "password";

        if (isPassword) {

            input.type = "text";
            button.textContent = "visibility_off";
            button.setAttribute("aria-label", "Hide password");

        } else {

            input.type = "password";
            button.textContent = "visibility";
            button.setAttribute("aria-label", "Show password");
        }
    });
}


setupPasswordToggle("loginEye", "loginPassword");
setupPasswordToggle("signupEye", "signupPassword");
setupPasswordToggle("confirmEye", "confirmPassword");


/* ================= HELPER FUNCTIONS ================= */

function validateEmail(email) {

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return emailPattern.test(email);
}


function setError(input, errorElement, message) {

    if (errorElement) {
        errorElement.textContent = message;
    }

    if (input) {

        input.setAttribute("aria-invalid", "true");

        const inputGroup = input.closest(".input-group");

        if (inputGroup) {
            inputGroup.classList.add("has-error");
        }
    }
}


function removeError(input, errorElement) {

    if (errorElement) {
        errorElement.textContent = "";
    }

    if (input) {

        input.removeAttribute("aria-invalid");

        const inputGroup = input.closest(".input-group");

        if (inputGroup) {
            inputGroup.classList.remove("has-error");
        }
    }
}


/* ================= REMOVE ERROR WHILE TYPING ================= */

document
    .querySelectorAll(".input-group input, .input-group select")
    .forEach(function (input) {

        const eventType =
            input.tagName === "SELECT"
                ? "change"
                : "input";

        input.addEventListener(eventType, function () {

            const describedBy =
                input.getAttribute("aria-describedby");

            const errorElement = describedBy
                ? document.getElementById(describedBy)
                : null;

            removeError(input, errorElement);
        });
    });


/* ================= LOGIN VALIDATION ================= */

if (login) {

    login.addEventListener("submit", function (event) {

        const emailInput =
            document.getElementById("loginEmail");

        const passwordInput =
            document.getElementById("loginPassword");

        const roleInput =
            document.getElementById("loginRole");


        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const role = roleInput.value;


        const emailError =
            document.getElementById("loginEmailError");

        const passwordError =
            document.getElementById("loginPasswordError");

        const roleError =
            document.getElementById("loginRoleError");


        let valid = true;

        clearFormErrors(login);

        emailInput.value = email;


        /* ===== EMAIL ===== */

        if (email === "") {

            setError(
                emailInput,
                emailError,
                "Please enter your email."
            );

            valid = false;

        } else if (!validateEmail(email)) {

            setError(
                emailInput,
                emailError,
                "Please enter a valid email address."
            );

            valid = false;
        }


        /* ===== PASSWORD ===== */

        if (password === "") {

            setError(
                passwordInput,
                passwordError,
                "Please enter your password."
            );

            valid = false;
        }


        /* ===== ROLE ===== */

        if (!["customer", "manager"].includes(role)) {

            setError(
                roleInput,
                roleError,
                "Please select Customer or Manager."
            );

            valid = false;
        }


        /* Stop PHP submission if validation failed */

        if (!valid) {
            event.preventDefault();
        }
    });
}


/* ================= SIGNUP VALIDATION ================= */

if (signup) {

    signup.addEventListener("submit", function (event) {

        const nameInput =
            document.getElementById("signupName");

        const emailInput =
            document.getElementById("signupEmail");

        const passwordInput =
            document.getElementById("signupPassword");

        const confirmInput =
            document.getElementById("confirmPassword");

        const roleInput =
            document.getElementById("signupRole");

        const termsInput =
            document.getElementById("terms");


        const name = nameInput.value
            .trim()
            .replace(/\s+/g, " ");

        const email = emailInput.value.trim();

        const password = passwordInput.value;

        const confirmPassword = confirmInput.value;

        const role = roleInput.value;


        const nameError =
            document.getElementById("nameError");

        const emailError =
            document.getElementById("signupEmailError");

        const passwordError =
            document.getElementById("signupPasswordError");

        const confirmError =
            document.getElementById("confirmPasswordError");

        const roleError =
            document.getElementById("signupRoleError");

        const termsError =
            document.getElementById("termsError");


        let valid = true;

        clearFormErrors(signup);

        nameInput.value = name;
        emailInput.value = email;


        /* ===== FULL NAME ===== */

        if (name === "") {

            setError(
                nameInput,
                nameError,
                "Please enter your full name."
            );

            valid = false;

        } else if (name.length < 3) {

            setError(
                nameInput,
                nameError,
                "Name must be at least 3 characters."
            );

            valid = false;

        } else if (name.length > 100) {

            setError(
                nameInput,
                nameError,
                "Name must not be more than 100 characters."
            );

            valid = false;
        }


        /* ===== EMAIL ===== */

        if (email === "") {

            setError(
                emailInput,
                emailError,
                "Please enter your email."
            );

            valid = false;

        } else if (!validateEmail(email)) {

            setError(
                emailInput,
                emailError,
                "Please enter a valid email address."
            );

            valid = false;
        }


        /* ===== PASSWORD ===== */

        if (password === "") {

            setError(
                passwordInput,
                passwordError,
                "Please create a password."
            );

            valid = false;

        } else if (password.length < 8) {

            setError(
                passwordInput,
                passwordError,
                "Password must be at least 8 characters."
            );

            valid = false;

        } else if (
            !/[A-Za-z]/.test(password) ||
            !/\d/.test(password)
        ) {

            setError(
                passwordInput,
                passwordError,
                "Password must contain at least one letter and one number."
            );

            valid = false;
        }


        /* ===== CONFIRM PASSWORD ===== */

        if (confirmPassword === "") {

            setError(
                confirmInput,
                confirmError,
                "Please confirm your password."
            );

            valid = false;

        } else if (password !== confirmPassword) {

            setError(
                confirmInput,
                confirmError,
                "Passwords do not match."
            );

            valid = false;
        }


        /* ===== ACCOUNT TYPE ===== */

        if (!["customer", "manager"].includes(role)) {

            setError(
                roleInput,
                roleError,
                "Please select Customer or Manager."
            );

            valid = false;
        }


        /* ===== TERMS ===== */

        if (!termsInput.checked) {

            termsError.textContent =
                "Please accept the Terms & Conditions.";

            valid = false;
        }


        /* Stop submission if validation failed */

        if (!valid) {
            event.preventDefault();
        }
    });
}


/* ================= REMOVE TERMS ERROR ================= */

const terms = document.getElementById("terms");

if (terms) {

    terms.addEventListener("change", function () {

        const termsError =
            document.getElementById("termsError");

        if (termsError) {
            termsError.textContent = "";
        }
    });
}