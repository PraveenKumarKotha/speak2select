<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}

$success_message = "";
$error_message = "";
$show_form = true;

// Validate reset link
if (isset($_GET['email']) && isset($_GET['token'])) {

    $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
    $token = preg_replace('/[^a-f0-9]/', '', $_GET['token']);

    $stmt = $conn->prepare(
        "SELECT reset_expires FROM users WHERE email = ? AND reset_token = ?"
    );
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || strtotime($user['reset_expires']) < time()) {
        $error_message = "Invalid or expired password reset link.";
        $show_form = false;
    }

} else {
    $error_message = "Invalid request!";
    $show_form = false;
}

// Handle password update
if ($show_form && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error_message = "Passwords do not match.";
    } else {

        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $update = $conn->prepare(
            "UPDATE users 
             SET password = ?, reset_token = NULL, reset_expires = NULL 
             WHERE email = ?"
        );
        $update->execute([$new_password, $email]);

        $success_message = "
            ✅ <strong>Password reset successful!</strong><br><br>
            <a href='login.php' style='
                display: inline-block;
                padding: 10px 20px;
                background-color: #28a745;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                transition: background-color 0.3s ease;
            ' onmouseover=\"this.style.backgroundColor='#218838'\"
               onmouseout=\"this.style.backgroundColor='#28a745'\">
                Login Now
            </a>
        ";

        $show_form = false;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>

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
.reset-container {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
}

/* Heading */
.reset-container h2 {
    margin-bottom: 20px;
    text-align: center;
    color: #333;
}

/* Labels */
label {
    display: block;
    margin-top: 10px;
    margin-bottom: 5px;
    font-weight: bold;
}

/* Input group */
.input-group {
    position: relative;
    width: 100%;
}

input[type="password"],
input[type="text"] {
    width: 100%;
    padding: 10px 35px 10px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 1em;
}

/* Eye icon */
.toggle-password {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 16px;
    color: #555;
    user-select: none;
}

/* Button */
button {
    margin-top: 15px;
    padding: 10px;
    width: 100%;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
}
button:hover {
    background-color: #0056b3;
}

/* Messages */
.message {
    margin-top: 15px;
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

    .reset-container {
        margin-top: 40px;
        padding: 22px;
    }

    .reset-container h2 {
        font-size: 1.5em;
    }

    label {
        font-size: 0.95em;
    }

    input[type="password"],
    input[type="text"] {
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

<div class="reset-container">
    <h2>Reset Password</h2>

    <?php if ($error_message): ?>
        <p class="message error"><?= htmlspecialchars($error_message) ?></p>

    <?php elseif ($success_message): ?>
        <p class="message"><?= $success_message ?></p>

    <?php elseif ($show_form): ?>
        <form method="POST">
            <label>New Password:</label>
            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>

            <label>Confirm Password:</label>
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
            </div>

            <button type="submit">Update Password</button>
        </form>
    <?php endif; ?>
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
