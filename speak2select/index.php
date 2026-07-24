<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Speak2Select</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="author" content="Praveen Kumar Kotha – Speak2Select">
<meta name="copyright" content="© 2026 Speak2Select">
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

/* Box */
.box {
    background: white;
    padding: 35px;
    border-radius: 10px;
    width: 100%;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    box-sizing: border-box;
}

/* Links */
a {
    display: block;
    margin: 12px 0;
    padding: 12px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 16px;
}
a.register {
    background: #28a745;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 480px) {

    body {
        padding: 15px;
        align-items: flex-start;
    }

    .box {
        margin-top: 40px;
        padding: 25px;
    }

    h2 {
        font-size: 1.5em;
    }

    p {
        font-size: 0.95em;
    }

    a {
        font-size: 15px;
        padding: 11px;
    }
}
</style>
</head>

<body>

<div class="box">
    <h2>🎯 Speak2Select</h2>
    <p>Listen better. Speak confidently. Get selected.</p>

    <a href="auth/login.php">Login</a>
    <a href="auth/register.php" class="register">Register</a>
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
