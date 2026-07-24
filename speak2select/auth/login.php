<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user
    $stmt = $conn->prepare("SELECT id, name, password, email_verified, payment_status, access_expiry_date 
    FROM users 
    WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // 1️⃣ Email verification check
        if ($user['email_verified'] != 1) {
            $error_message = "Please verify your email before logging in.";
        } 
        else {

            /* =====================================================
               ✅ FIXED LOGIN LOGIC
               ===================================================== */

            if (strtoupper(trim($user['payment_status'])) === 'PAID') {

                if (!empty($user['access_expiry_date'])) {
                    $today = date('Y-m-d');

                    if ($today > $user['access_expiry_date']) {
                        $conn->prepare(
                            "UPDATE users SET payment_status = 'PENDING' WHERE id = ?"
                        )->execute([$user['id']]);

                        $_SESSION['user_id'] = $user['id'];
                        header("Location: payment.php");
                        exit();
                    }
                }

                session_regenerate_id(true);

                $session_token = bin2hex(random_bytes(32));
                $conn->prepare(
                    "UPDATE users SET session_token = ?, last_activity = NOW() WHERE id = ?"
                )->execute([$session_token, $user['id']]);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['session_token'] = $session_token;

                header("Location: practice.php");
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            header("Location: payment.php");
            exit();
        }
    } 
    else {
        $error_message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>

<style>

.auth-links{
    margin-top:20px;
    text-align:center;
}

.cta-text{
    font-size:14px;
    margin-bottom:10px;
    color:#444;
    font-weight:500;
}

/* REGISTER BUTTON (Blue) */
.primary-link{
    display:inline-block;
    padding:7px 16px;      /* ⬅ reduced */
    background:#343a40;
    color:#fff;
    text-decoration:none;
    border-radius:20px;   /* ⬅ smaller pill */
    font-weight:600;
    font-size:13px;       /* ⬅ reduced */
    transition:0.3s;
}

.primary-link:hover{
    filter: brightness(1.15);
    box-shadow: 0 6px 14px rgba(0,0,0,0.3);
    transform: translateY(-1px);
}

/* FORGOT PASSWORD BUTTON */
.forgot-btn{
    display:inline-block;
    margin-top:10px;      /* ⬅ reduced */
    padding:6px 16px;     /* ⬅ reduced */
    border:1.5px solid #6c757d;
    border-radius:20px;
    color:#343a40;
    text-decoration:none;
    font-weight:500;
    font-size:13px;       /* ⬅ reduced */
    transition:0.3s;
}

.forgot-btn:hover{
    background:#6c757d;
    color:#fff;
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
.login-container {
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
input[type="email"],
.password-wrapper input {
    width: 100%;
    padding: 10px;
    padding-right: 35px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1em;
    box-sizing: border-box;
}

/* Password toggle */
.password-wrapper {
    position: relative;
}
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
    width: 100%;
    padding: 12px;
    background-color: #28a745;
    color: white;
    font-size: 1.1em;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

/* Messages */
.error-message {
    color: #e74c3c;
    font-size: 1em;
    margin-top: 10px;
}

.register-link {
    margin-top: 15px;
    font-size: 1em;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 480px) {

    body {
        padding: 15px;
        align-items: flex-start;
    }

    .login-container {
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

    .register-link {
        font-size: 0.95em;
    }
}
</style>
</head>

<body>

<div class="login-container">
    <h2>Login</h2>

    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" required>
            <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
        </div>

        <button type="submit" name="login">Login</button>
    </form>

    <?php if (isset($error_message)): ?>
        <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

<div class="auth-links">
    <p class="cta-text">
        New here?
    </p>

    <a href="register.php" class="primary-link">
        Create Account
    </a>

    <a href="forgot_password.php" class="forgot-btn">
        Forgot Password?
    </a>
</div>


</div>

<script>
function togglePassword(el) {
    const passwordInput = document.getElementById("password");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        el.innerHTML = "🙈";
    } else {
        passwordInput.type = "password";
        el.innerHTML = "👁️";
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
