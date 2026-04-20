<?php
require "config/config.php"; //need to load config file to get vars
//will also start session

if (isset($_SESSION['useremail'])) //user logged in????
    {
        header("Location: dashboard.php"); //go to dashboard
        exit(); //no need to go further
    }

$error = $_SESSION['error'] ?? null; //any error is there??
unset($_SESSION['error']); //reset that error now
?>


<!-- HTML SECTION FOR FRONTEND -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication System</title>
    <link rel="stylesheet" href="../public/style.css">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <div class="auth-card">
            <div class="brand-block">
                <div class="brand-badge">Secure Access</div>
                <h1>Welcome back</h1>
                <p class="subtitle">Sign in to continue to your dashboard.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="auth-form">
                <div class="input-group">
                    <label for="email">Email address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="test@gmail.com"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div class="auth-footer">
                Demo credentials: <strong>test@gmail.com</strong> / <strong>1234</strong>
            </div>
        </div>
    </div>
</body>
</html>