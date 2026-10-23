
const menuToggle = document.getElementById("menu-toggle");
const siteNav = document.getElementById("site-nav");

if (menuToggle && siteNav) {
    menuToggle.addEventListener("click", function() {
        siteNav.classList.toggle("open");

        const isOpen = siteNav.classList.contains("open");
        menuToggle.setAttribute("aria-expanded", isOpen);
    });
}

// Show and hide login password
const showLoginPassword = document.getElementById("show-login-password");
const loginPassword = document.getElementById("login-password");

if (showLoginPassword && loginPassword) {

    showLoginPassword.addEventListener("click", function() {

        if (loginPassword.type === "password") {
            loginPassword.type = "text";
            showLoginPassword.textContent = "Hide";
        } else {
            loginPassword.type = "password";
            showLoginPassword.textContent = "Show";
        }

    });

}

// Login form validation
const loginForm = document.getElementById("login-form");

if (loginForm) {

    loginForm.addEventListener("submit", function(event) {

        const emailInput = document.getElementById("login-email");
        const passwordInput = document.getElementById("login-password");
        const errorBox = document.getElementById("login-error");

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Clear previous error messages
        errorBox.textContent = "";
        errorBox.hidden = true;

        // Check email
        if (email === "") {
            event.preventDefault();
            errorBox.textContent = "Please enter your email address.";
            errorBox.hidden = false;
            return;
        }

        if (!emailInput.validity.valid) {
            event.preventDefault();
            errorBox.textContent = "Please enter a valid email address.";
            errorBox.hidden = false;
            return;
        }

        // Check password
        if (password === "") {
            event.preventDefault();
            errorBox.textContent = "Please enter your password.";
            errorBox.hidden = false;
            return;
        }

            
        if (password.length < 8) {
            event.preventDefault();
            errorBox.textContent = "Password must be at least 8 characters long.";
            errorBox.hidden = false;
            return;
        }


    });

}

 // Registration password visibility
function setupPasswordToggle(buttonId, inputId) {

    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);

    if (button && input) {

        button.addEventListener("click", function() {

            if (input.type === "password") {
                input.type = "text";
                button.textContent = "Hide";
            } else {
                input.type = "password";
                button.textContent = "Show";
            }

        });

    }
}

setupPasswordToggle("show-register-password", "register-password");
setupPasswordToggle("show-confirm-password", "confirm-password");

// Registration form validation
const registerForm = document.getElementById("register-form");

if (registerForm) {

    registerForm.addEventListener("submit", function(event) {

        const fullName = document.getElementById("full-name");
        const studentNumber = document.getElementById("student-number");
        const email = document.getElementById("register-email");
        const password = document.getElementById("register-password");
        const confirmPassword = document.getElementById("confirm-password");
        const agreement = document.getElementById("register-agreement");
        const errorBox = document.getElementById("register-error");

        // Clear previous errors
        errorBox.textContent = "";
        errorBox.hidden = true;

        let errorMessage = "";
        let invalidField = null;

        // Validate full name
        if (fullName.value.trim() === "") {
            errorMessage = "Please enter your full name.";
            invalidField = fullName;
        }

        // Validate student number
        else if (!/^\d{9}$/.test(studentNumber.value.trim())) {
            errorMessage = "Student number must contain exactly 9 digits.";
            invalidField = studentNumber;
        }

        // Validate email
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
            errorMessage = "Please enter a valid email address.";
            invalidField = email;
        }

        // Validate password length
        else if (password.value.length < 8) {
            errorMessage = "Password must be at least 8 characters long.";
            invalidField = password;
        }

        // Confirm both passwords match
        else if (password.value !== confirmPassword.value) {
            errorMessage = "Passwords do not match.";
            invalidField = confirmPassword;
        }

        // Validate terms agreement
        else if (!agreement.checked) {
            errorMessage = "Please accept the terms before continuing.";
            invalidField = agreement;
        }

        // Display the error if validation fails
        if (errorMessage !== "") {

            event.preventDefault();

            errorBox.textContent = errorMessage;
            errorBox.hidden = false;

            invalidField.focus();
        }

    });

}
