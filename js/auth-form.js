// auth-form.js - client-side validation for login.php and register.php
// The PHP in those pages checks everything again; this just gives instant feedback.

const authForm = document.querySelector("form.js-validate");

if (authForm) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isRegister = authForm.querySelector("#confirm_password") !== null;

    // One rule per field id: returns an error message, or "" when the value is fine
    const rules = {
        full_name: function (v) {
            return v.trim() === "" ? "Enter your full name." : "";
        },
        student_number: function (v) {
            return /^\d{9}$/.test(v.trim()) ? "" : "Student number must be 9 digits.";
        },
        email: function (v) {
            if (v.trim() === "") return "Enter your email address.";
            return emailPattern.test(v.trim()) ? "" : "Enter a valid email address.";
        },
        password: function (v) {
            if (v === "") return "Enter your password.";
            if (isRegister && v.length < 8) return "Password must be at least 8 characters.";
            return "";
        },
        confirm_password: function (v) {
            return v !== document.getElementById("password").value ? "Passwords do not match." : "";
        }
    };

    function check(input) {
        const rule = rules[input.id];
        const box = document.getElementById(input.id + "-error");
        if (!rule || !box) {
            return true;
        }
        const message = rule(input.value);
        box.textContent = message;
        box.hidden = message === "";
        if (message) {
            input.setAttribute("aria-invalid", "true");
        } else {
            input.removeAttribute("aria-invalid");
        }
        return message === "";
    }

    const inputs = authForm.querySelectorAll("input");

    for (const input of inputs) {
        // Re-check a field as soon as the user fixes it
        input.addEventListener("input", function () {
            if (input.getAttribute("aria-invalid") === "true") {
                check(input);
            }
        });
    }

    authForm.addEventListener("submit", function (event) {
        let firstBad = null;
        for (const input of inputs) {
            if (!check(input) && !firstBad) {
                firstBad = input;
            }
        }
        if (firstBad) {
            event.preventDefault();
            firstBad.focus();
        }
    });
}
