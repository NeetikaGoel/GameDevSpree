<?php
//files requirement fulfil first
require "config/config.php";
require "classes/User.php";
require "classes/Feedback.php";

//object creations now user and feedabcks
$user = new User();
$feedbackobj = new Feedback();

if (!$user->loggedinornot()) 
    {
        //ofc if not logged in yet bring to index.php
        header("Location: index.php");
        exit();
    }

$message = $feedbackobj->msg(); //get the feedback msg
$currentFeedback = $feedbackobj->get(); //take curr feedback
$userEmail = $user->getUserEmail(); //taking email of user 
?>

<!-- HTML PORTION FOR FRONTEND PURPOSE -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/style.css">
    <meta name="node-api-base" content="<?php echo htmlspecialchars(NODE_API_BASE); ?>">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-badge">Dashboard</div>
                <h2>Auth Panel</h2>
            </div>

            <div class="profile-card">
                <p class="profile-label">Signed in as</p>
                <p class="profile-value"><?php echo htmlspecialchars($userEmail); ?></p>
            </div>

            <a href="logout.php" class="btn btn-ghost full-width">Logout</a>
        </aside>

        <main class="content-area">
            <section class="hero-card">
                <p class="hero-kicker">Overview</p>
                <h1><?php echo htmlspecialchars($message); ?></h1>
                <p class="hero-text">
                    Share your experience below -> Your feedback is sent to the Node service and saved to your PHP session!!
                </p>
            </section>

            <section class="panel-card">
                <div class="panel-header">
                    <div>
                        <h3>Feedback Form</h3>
                        <p>Help us improve your experience!</p>
                    </div>
                </div>

                <form id="feedbackform" class="feedback-form">
                    <div class="input-group">
                        <label for="rating">Your rating</label>
                        <select name="rating" id="rating" required>
                            <option value="good" <?php echo $currentFeedback === 'good' ? 'selected' : ''; ?>>Good</option>
                            <option value="okay" <?php echo $currentFeedback === 'okay' ? 'selected' : ''; ?>>Okay</option>
                            <option value="bad" <?php echo $currentFeedback === 'bad' ? 'selected' : ''; ?>>Bad</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Submit Feedback
                    </button>
                </form>

                <p id="responsemsg" class="response-message"></p>
            </section>
        </main>
    </div>

    <script src="../public/script.js"></script>
<!-- FOR FEEDBACK SUBMISSION LOGIC PURPOSE!!! -->
</body>
</html>