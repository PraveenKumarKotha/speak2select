<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}

$message = "";
$success = false;

if (isset($_GET['email']) && isset($_GET['token'])) {

    $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
    $token = preg_replace('/[^a-f0-9]/', '', $_GET['token']);


    // Check if user exists with matching email + token
    $stmt = $conn->prepare(
        "SELECT id, email_verified FROM users 
         WHERE email = ? AND token = ?"
    );
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        if ($user['email_verified'] == 1) {
            $message = "✅ Your email is already verified. You can login.";
            $success = true;
        } else {
            // Verify email and clear token
            $update = $conn->prepare(
            "UPDATE users 
            SET email_verified = 1, token = NULL 
            WHERE id = ?"
            );

            if ($update->execute([$user['id']])) {
                $message = "🎉 Your email has been verified successfully!";
                $success = true;
            } else {
                $message = "❌ Verification failed. Please try again.";
            }
        }

    } else {
        $message = "⚠️ Invalid or expired verification link.";
    }

} else {
    $message = "⚠️ Invalid verification request.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #f4f4f9;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            user-select: none;
        }
        .message-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
        }
        h2 {
            color: <?= $success ? "#28a745" : "#e74c3c" ?>;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        a:hover {
            background-color: #0056b3;
        }
        .emoji {
            font-size: 2em;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="message-container">
    <div class="emoji"><?= $success ? "🎉" : "❗" ?></div>
    <h2><?= htmlspecialchars($message) ?></h2>

    <?php if ($success): ?>
        <a href="login.php">Login Now</a>
    <?php endif; ?>
</div>
<script>
/* ===============================
   BASIC DEVTOOLS / COPY DETERRENT
   =============================== */

// Disable right click
document.addEventListener("contextmenu", e => e.preventDefault());

// Block common shortcuts
document.addEventListener("keydown", e => {
    if (
        e.key === "F12" ||
        (e.ctrlKey && e.shiftKey && ["I","C","J"].includes(e.key)) ||
        (e.ctrlKey && e.key === "U")
    ) {
        e.preventDefault();
        alert("⚠️ Source viewing is disabled.");
    }
});

// Detect DevTools open (size trick)
setInterval(() => {
    if (
        window.outerWidth - window.innerWidth > 160 ||
        window.outerHeight - window.innerHeight > 160
    ) {
        document.body.innerHTML =
            "<h2 style='text-align:center;margin-top:20%'>⚠️ DevTools Detected</h2>";
    }
}, 1500);
</script>
</body>
</html>
