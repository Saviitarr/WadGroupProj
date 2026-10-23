// contact-form.js - client-side validation for contact.php (Member 3)
// The server repeats these checks in contact.php.

const contactForm = document.getElementById("contactForm");

if (contactForm) {
    const MESSAGE_MAX = 2000;
    const nameInput = document.getElementById("name");
    const emailInput = document.getElementById("email");
    const subjectInput = document.getElementById("subject");
    const messageInput = document.getElementById("message");
    const messageCount = document.getElementById("message-count");

    // A simple email pattern: something@something.something
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function showError(input, message) {
        const box = document.getElementById(input.id + "-error");
        input.setAttribute("aria-invalid", "true");
        box.textContent = message;
        box.hidden = false;
    }

    function clearError(input) {
        const box = document.getElementById(input.id + "-error");
        input.removeAttribute("aria-invalid");
        box.textContent = "";
        box.hidden = true;
    }

    // Each rule: the field, and a function returning an error message (or "" when fine)
    const rules = [
        {
            input: nameInput,
            check: function (value) {
                return value === "" ? "Enter your name." : "";
            }
        },
        {
            input: emailInput,
            check: function (value) {
                if (value === "") return "Enter your email address.";
                if (!emailPattern.test(value)) return "Enter a valid email address, like name@example.com.";
                return "";
            }
        },
        {
            input: subjectInput,
            check: function (value) {
                return value === "" ? "Choose what your message is about." : "";
            }
        },
        {
            input: messageInput,
            check: function (value) {
                if (value.length < 10) return "Write a message of at least 10 characters.";
                if (value.length > MESSAGE_MAX) return "Keep your message under 2000 characters.";
                return "";
            }
        }
    ];

    function validate(rule) {
        const message = rule.check(rule.input.value.trim());
        if (message) {
            showError(rule.input, message);
            return false;
        }
        clearError(rule.input);
        return true;
    }

    // Check a field when the user leaves it, and re-check as they fix it
    for (const rule of rules) {
        rule.input.addEventListener("blur", function (event) {
            // If the user is clicking Send, let the submit handler check everything instead.
            // (Showing an error here would push the button down and the click would miss it.)
            if (event.relatedTarget && event.relatedTarget.type === "submit") {
                return;
            }
            validate(rule);
        });
        rule.input.addEventListener("input", function () {
            if (rule.input.getAttribute("aria-invalid") === "true") {
                validate(rule);
            }
        });
    }

    messageInput.addEventListener("input", function () {
        const length = messageInput.value.length;
        messageCount.textContent = length + " / " + MESSAGE_MAX;
        messageCount.classList.toggle("near-limit", length > MESSAGE_MAX - 100);
    });

    contactForm.addEventListener("submit", function (event) {
        let firstBad = null;

        for (const rule of rules) {
            if (!validate(rule) && !firstBad) {
                firstBad = rule.input;
            }
        }

        if (firstBad) {
            event.preventDefault();
            firstBad.focus();
            return;
        }

        const button = contactForm.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = "Sending…";
    });
}
