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

// Show success message after redirect
if (isset($_GET['success'])) {
    $success_message = 'A verification email has been sent. Please check your <span style="color:black;font-weight:600;">Inbox</span> or <span style="color:black;font-weight:600;">Spam</span> folder and verify your email.';
}

if (isset($_POST['register'])) {

    $name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(16));

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        if ($user['email_verified'] == 0) {
            $error_message = "This email is already registered. Please check your Inbox or Spam folder and verify your email first.";
        } else {
            $error_message = "Email is already registered!";
        }

    } else {

        $stmt = $conn->prepare("
            INSERT INTO users
            (name, email, password, email_verified, payment_status, access_expiry_date, token)
            VALUES (?, ?, ?, 0, 'PENDING', NULL, ?)
        ");

        $stmt->execute([$name, $email, $password, $token]);

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
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - Speak2Select';

            $mail->Body = "
                <h3>Hello {$name},</h3>

                <p>Welcome to <strong>Speak2Select</strong>.</p>

                <p>Please verify your email address by clicking the button below.</p>

                <p>
                    <a href='" . APP_URL . "/auth/verify.php?email=" . urlencode($email) . "&token=$token'
                    style='background:#28a745;color:#fff;padding:10px 18px;text-decoration:none;border-radius:5px;'>
                        Verify Email
                    </a>
                </p>

                <p>If the button doesn't work, copy and paste this link into your browser:</p>

                <p>" . APP_URL . "/auth/verify.php?email=" . urlencode($email) . "&token=$token</p>

                <br>

                <p>Regards,<br><strong>Speak2Select Team</strong></p>
            ";

            $mail->send();

            // Redirect to prevent duplicate form submission
            header("Location: register.php?success=1");
            exit();

        } catch (Exception $e) {

            $error_message = "Email could not be sent. Please try again.";

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>

<style>
.redirect-box{
    margin-top:20px;
    text-align:center;
    font-size:14px;
}

.redirect-btn{
    margin-left:6px;
    padding:8px 16px;
    background:#343a40;
    color:#fff;
    text-decoration:none;
    border-radius:20px;
    font-weight:600;
    transition:0.3s;
}

.redirect-btn:hover{
    filter: brightness(1.15);
    box-shadow: 0 6px 14px rgba(0,0,0,0.3);
    transform: translateY(-1px);
}


body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    user-select: none;
}

/* Container */
.register-container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
    box-sizing: border-box;
}

/* Headings */
h2 {
    color: #333;
    margin-bottom: 20px;
}

/* Labels */
label {
    font-size: 1.1em;
    margin-bottom: 5px;
    display: block;
    text-align: left;
}

/* Inputs */
input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1em;
    box-sizing: border-box;
}

/* Password eye */
.password-wrapper {
    position: relative;
}
.eye-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    cursor: pointer;
    user-select: none;
}

/* Button */
button {
    width: 100%;
    padding: 12px;
    background-color: #28a745;
    color: white;
    font-size: 1.1em;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background-color: #218838;
}

/* Messages */
.error-message,
.success-message {
    margin-top: 10px;
    font-size: 1em;
}
.error-message { color: #e74c3c; }
.success-message { color: #28a745; }

.login-link {
    margin-top: 15px;
    font-size: 1em;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 480px) {

    body {
        padding: 15px;
        align-items: flex-start;
    }

    .register-container {
        margin-top: 40px;
        padding: 22px;
    }

    h2 {
        font-size: 1.5em;
    }

    label {
        font-size: 1em;
    }

    button {
        font-size: 1em;
        padding: 11px;
    }

    .login-link {
        font-size: 0.95em;
    }
}
</style>
</head>

<body>

<div class="register-container">
    <h2>Register</h2>

    <form method="POST" onsubmit="return checkPasswordsMatch();">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" required>
            <span class="eye-icon" onclick="togglePassword('password', this)">👁️</span>
        </div>

        <label>Confirm Password:</label>
        <div class="password-wrapper">
            <input type="password" id="confirm_password" required>
            <span class="eye-icon" onclick="togglePassword('confirm_password', this)">👁️</span>
        </div>

        <button type="submit" name="register">Register</button>
    </form>

    <?php if (isset($error_message)): ?>
        <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
    <?php elseif (isset($success_message)): ?>
        <p class="success-message"><?= $success_message; ?></p>
    <?php endif; ?>

    <div class="redirect-box">
    <span>Already have an account?</span>
    <a href="login.php" class="redirect-btn">Login</a>
    </div>

</div>

<script>
function togglePassword(id, el) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        el.textContent = "🙈";
    } else {
        input.type = "password";
        el.textContent = "👁️";
    }
}

function checkPasswordsMatch() {
    const pass = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;

    if (pass !== confirm) {
        alert("Passwords do not match!");
        return false;
    }
    return true;
}
</script>
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
