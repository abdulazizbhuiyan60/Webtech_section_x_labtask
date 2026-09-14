"use strict";

const loginForm = document.getElementById("loginForm");
const signupForm = document.getElementById("signupForm");
const showSignup = document.getElementById("showSignup");
const showLogin = document.getElementById("showLogin");

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

function openForm(formName) {
    const showSignupForm = formName === "signup";

    loginForm.classList.toggle("hidden", showSignupForm);
    signupForm.classList.toggle("hidden", !showSignupForm);

    const visibleForm = showSignupForm
        ? signupForm.querySelector("form")
        : loginForm.querySelector("form");

    const firstInput = visibleForm.querySelector(
        "input:not([type='hidden'])"
    );

    if (firstInput) {
        firstInput.focus();
    }
}

showSignup.addEventListener("click", (event) => {
    event.preventDefault();
    clearFormErrors(document.getElementById("login"));
    openForm("signup");
});

showLogin.addEventListener("click", (event) => {
    event.preventDefault();
    clearFormErrors(document.getElementById("signup"));
    openForm("login");
});


/* ================= PASSWORD VISIBILITY ================= */

function setupPasswordToggle(buttonId, inputId) {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);

    button.addEventListener("click", () => {
        const passwordIsVisible = input.type === "text";

        input.type = passwordIsVisible ? "password" : "text";

        button.textContent = passwordIsVisible
            ? "visibility"
            : "visibility_off";

        button.setAttribute(
            "aria-label",
            passwordIsVisible ? "Show password" : "Hide password"
        );
    });
}

setupPasswordToggle("loginEye", "loginPassword");
setupPasswordToggle("signupEye", "signupPassword");
setupPasswordToggle("confirmEye", "confirmPassword");


/* ================= HELPER FUNCTIONS ================= */

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function setError(input, errorElement, message) {
    errorElement.textContent = message;
    input.setAttribute("aria-invalid", "true");

    const inputGroup = input.closest(".input-group");

    if (inputGroup) {
        inputGroup.classList.add("has-error");
    }
}

function removeError(input, errorElement) {
    errorElement.textContent = "";
    input.removeAttribute("aria-invalid");

    const inputGroup = input.closest(".input-group");

    if (inputGroup) {
        inputGroup.classList.remove("has-error");
    }
}


/* Remove error when user corrects input */

document
    .querySelectorAll(".input-group input, .input-group select")
    .forEach((input) => {

        input.addEventListener("input", () => {

            const describedBy =
                input.getAttribute("aria-describedby");

            const errorElement = describedBy
                ? document.getElementById(describedBy)
                : null;

            if (errorElement) {
                removeError(input, errorElement);
            }
        });
    });


/* ================= LOGIN VALIDATION ================= */

const login = document.getElementById("login");

login.addEventListener("submit", (event) => {

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


    /* Email validation */

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


    /* Password validation */

    if (password === "") {

        setError(
            passwordInput,
            passwordError,
            "Please enter your password."
        );

        valid = false;
    }


    /* Role validation */

    if (
        !["customer", "admin", "manager"]
        .includes(role)
    ) {

        setError(
            roleInput,
            roleError,
            "Please select Customer, Admin, or Manager."
        );

        valid = false;
    }


    /*
     * If validation fails,
     * stop form from going to PHP.
     */

    if (!valid) {
        event.preventDefault();
    }
});


/* ================= SIGN-UP VALIDATION ================= */

const signup = document.getElementById("signup");

signup.addEventListener("submit", (event) => {

    const nameInput =
        document.getElementById("signupName");

    const emailInput =
        document.getElementById("signupEmail");

    const passwordInput =
        document.getElementById("signupPassword");

    const confirmInput =
        document.getElementById("confirmPassword");

    const termsInput =
        document.getElementById("terms");

    const roleInput =
        document.getElementById("signupRole");


    const name = nameInput.value
        .trim()
        .replace(/\s+/g, " ");

    const email =
        emailInput.value.trim();

    const password =
        passwordInput.value;

    const confirmPassword =
        confirmInput.value;

    const role =
        roleInput.value;


    const nameError =
        document.getElementById("nameError");

    const emailError =
        document.getElementById(
            "signupEmailError"
        );

    const passwordError =
        document.getElementById(
            "signupPasswordError"
        );

    const confirmError =
        document.getElementById(
            "confirmPasswordError"
        );

    const termsError =
        document.getElementById(
            "termsError"
        );

    const roleError =
        document.getElementById(
            "signupRoleError"
        );


    let valid = true;

    clearFormErrors(signup);

    nameInput.value = name;
    emailInput.value = email;


    /* ================= NAME ================= */

    if (name === "") {

        setError(
            nameInput,
            nameError,
            "Please enter your full name."
        );

        valid = false;

    } else if (
        name.length < 3 ||
        name.length > 100
    ) {

        setError(
            nameInput,
            nameError,
            "Name must be between 3 and 100 characters."
        );

        valid = false;
    }


    /* ================= EMAIL ================= */

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


    /* ================= PASSWORD ================= */

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


    /* ================= CONFIRM PASSWORD ================= */

    if (confirmPassword === "") {

        setError(
            confirmInput,
            confirmError,
            "Please confirm your password."
        );

        valid = false;

    } else if (
        password !== confirmPassword
    ) {

        setError(
            confirmInput,
            confirmError,
            "Passwords do not match."
        );

        valid = false;
    }


    /* ================= ROLE ================= */

    if (
        !["customer", "admin", "manager"]
        .includes(role)
    ) {

        setError(
            roleInput,
            roleError,
            "Please select Customer, Admin, or Manager."
        );

        valid = false;
    }


    /* ================= TERMS ================= */

    if (!termsInput.checked) {

        termsError.textContent =
            "Please accept the Terms & Conditions.";

        valid = false;
    }


    /* Stop form if validation fails */

    if (!valid) {
        event.preventDefault();
    }
});


/* Remove Terms error */

document
    .getElementById("terms")
    .addEventListener(
        "change",
        () => {

            document
                .getElementById("termsError")
                .textContent = "";
        }
    );