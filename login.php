
<?php
// Backend login processing will be added above the HTML.
$pageTitle = "Log in";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $pageTitle; ?> | Campus Connect</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-page">

    <header class="auth-header">

        <a href="index.php" class="auth-logo">
            <span class="logo-icon">CC</span>
            <span>Campus Connect</span>
        </a>

        <a href="index.php" class="back-link">
            Back to site
        </a>

    </header>

    <main class="auth-main">

        <div class="auth-card">

            <div class="auth-card-header">
                Log in
            </div>

            <div class="auth-card-content">

                <h1>Welcome back to Campus Connect</h1>

                <?php if (isset($_GET["registered"]) && $_GET["registered"] === "1"): ?>
                    <div class="auth-success">
                        Account created successfully. You can now log in.
                    </div>
                <?php endif; ?>

                <div id="login-error" class="auth-error" role="alert" hidden></div>

                <form id="login-form" method="POST" action="login.php" novalidate>

                    <div class="auth-field">
                        <label for="login-email">Email</label>

                        <input
                            type="email"
                            id="login-email"
                            name="email"
                            placeholder="Enter your student email"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="login-password">Password</label>

                        <div class="password-container">
                            <input
                                type="password"
                                id="login-password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                            <button type="button" class="show-password"
                                    id="show-login-password">
                                Show
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit">
                        Continue
                    </button>

                </form>

                <div class="auth-divider">
                    <span>or</span>
                </div>

                <a href="register.php" class="auth-register-btn">
                    Create a student account
                </a>

                <p class="auth-note">
                    Admins log in here too and are taken to the admin area.
                </p>

            </div>

        </div>

    </main>

    <script src="js/script.js"></script>

</body>
</html>
