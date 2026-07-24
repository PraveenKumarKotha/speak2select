<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}


$error_message = "";

if (isset($_POST['login']) && isset($_POST['email'])) {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE email = ? AND role = 'ADMIN'
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {

        session_regenerate_id(true);

        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role']  = 'ADMIN';

        header("Location: dashboard.php");
        exit();

    } else {
        $error_message = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
}

.login-container {
    width: 100%;
    max-width: 400px;
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #555;
}

input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin: 10px 0 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #28a745;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #218838;
}

.error-message {
    text-align: center;
    color: red;
    font-size: 14px;
    margin-top: 10px;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 480px) {

    body {
        padding: 15px;
    }

    .login-container {
        padding: 25px;
    }

    h2 {
        font-size: 1.4em;
    }

    label {
        font-size: 0.95em;
    }

    button {
        font-size: 15px;
        padding: 11px;
    }
}
</style>
</head>

<body>

<div class="login-container">
    <h2>Admin Login</h2>

    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
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
