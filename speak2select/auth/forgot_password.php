<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}

require '../includes/phpmailer/PHPMailer.php';
require '../includes/phpmailer/SMTP.php';
require '../includes/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare(
            "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?"
        );
        $update->execute([$token, $expires, $email]);

        $reset_link = APP_URL . "/auth/reset_password.php?email="
        . urlencode($email) . "&token=" . urlencode($token);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USER;
            $mail->Password = MAIL_PASS;

            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('speak2select@gmail.com', 'Speak2Select');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password - Speak2Select';
            $mail->Body = "
                <p>You requested a password reset for <strong>Speak2Select</strong>.</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='$reset_link'>Reset Password</a></p>
                <p>This link will expire in <strong>1 hour</strong>.</p>
                <p>If you did not request this, please ignore this email.</p>
            ";

            $mail->send();
            $success_message = 'Password reset link has been sent. Please check your Inbox or Spam folder.';
        } catch (Exception $e) {
            $error_message = "Email could not be sent.";
        }

    } else {
        $error_message = "Email not found in our records.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    user-select: none;
}

/* Container */
.form-container {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
}

/* Inputs */
input {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    box-sizing: border-box;
    font-size: 1em;
}

/* Button */
button {
    padding: 10px;
    width: 100%;
    background-color: green;
    color: white;
    border: none;
    margin-top: 15px;
    cursor: pointer;
    font-size: 1em;
}

/* Messages */
.message {
    margin-top: 10px;
    color: green;
    font-size: 1em;
}
.error {
    color: red;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 480px) {

    body {
        padding: 15px;
        align-items: flex-start;
    }

    .form-container {
        margin-top: 40px;
        padding: 22px;
    }

    h2 {
        font-size: 1.5em;
    }

    label {
        font-size: 0.95em;
    }

    input {
        font-size: 0.95em;
    }

    button {
        font-size: 0.95em;
        padding: 11px;
    }
}
</style>
</head>

<body>

<div class="form-container">
    <h2>Forgot Password</h2>

    <form method="POST">
        <label>Enter your registered email:</label>
        <input type="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <?php if ($success_message): ?>
        <p class="message"><?= htmlspecialchars($success_message) ?></p>
    <?php elseif ($error_message): ?>
        <p class="message error"><?= htmlspecialchars($error_message) ?></p>
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
