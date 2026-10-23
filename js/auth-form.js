
// Login form validation
const authForm = document.querySelector("form.js-validate");

if (authForm) {

    const emailInput = authForm.querySelector('input[name="email"]');
    const passwordInput = authForm.querySelector('input[name="password"]');

    const emailError = document.getElementById("email-error");
    const passwordError = document.getElementById("password-error");

    function showError(element, message) {
        if (element) {
            element.textContent = message;
            element.hidden = false;
        }
    }

    function clearError(element) {
        if (element) {
            element.textContent = "";
            element.hidden = true;
        }
    }

    authForm.addEventListener("submit", function(event) {

        clearError(emailError);
        clearError(passwordError);

        let invalidField = null;

        if (emailInput.value.trim() === "") {
            showError(emailError, "Please enter your email address.");
            invalidField = emailInput;
        }
        else if (!emailInput.validity.valid) {
            showError(emailError, "Please enter a valid email address.");
            invalidField = emailInput;
        }

        if (passwordInput.value === "") {
            showError(passwordError, "Please enter your password.");

            if (!invalidField) {
                invalidField = passwordInput;
            }
        }
        else if (passwordInput.value.length < 8) {
            showError(passwordError, "Password must be at least 8 characters.");

            if (!invalidField) {
                invalidField = passwordInput;
            }
        }

        if (invalidField) {
            event.preventDefault();
            invalidField.focus();
        }
    });

    // Password visibility
    const toggleButton = document.createElement("button");
    toggleButton.type = "button";
    toggleButton.className = "auth-password-toggle";
    toggleButton.textContent = "Show";

    passwordInput.insertAdjacentElement("afterend", toggleButton);

    toggleButton.addEventListener("click", function() {

        const isHidden = passwordInput.type === "password";

        passwordInput.type = isHidden ? "text" : "password";
        toggleButton.textContent = isHidden ? "Hide" : "Show";
    });
}
