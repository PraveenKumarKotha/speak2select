<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}


if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$today = date('Y-m-d');
$five_minutes_ago = date('Y-m-d H:i:s', strtotime('-5 minutes'));

$total_users = $conn->query("
    SELECT COUNT(*) FROM users 
    WHERE role = 'USER'
")->fetchColumn();

$active_stmt = $conn->prepare("
    SELECT COUNT(*) FROM users
    WHERE role = 'USER'
    AND payment_status = 'PAID'
    AND access_expiry_date >= ?
");
$active_stmt->execute([$today]);
$active_users = $active_stmt->fetchColumn();

$expired_stmt = $conn->prepare("
    SELECT COUNT(*) FROM users
    WHERE role = 'USER'
    AND payment_status = 'PAID'
    AND access_expiry_date < ?
");
$expired_stmt->execute([$today]);
$expired_users = $expired_stmt->fetchColumn();

$pending_users = $conn->query("
    SELECT COUNT(*) FROM users
    WHERE role = 'USER'
    AND payment_status = 'PENDING'
")->fetchColumn();

$live_stmt = $conn->prepare("
    SELECT COUNT(*) FROM users
    WHERE role = 'USER'
    AND last_activity >= ?
");
$live_stmt->execute([$five_minutes_ago]);
$live_users = $live_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    margin: 0;
    padding: 30px;
    user-select: none;
}

.dashboard {
    max-width: 900px;
    margin: auto;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-align: center;
}

.card h2 {
    margin: 0;
    font-size: 2em;
}

.card p {
    margin-top: 10px;
    color: #555;
}

.actions {
    margin-top: 30px;
    text-align: center;
}

.actions a {
    display: inline-block;
    margin: 10px;
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 6px;
    color: white;
    font-weight: bold;
}

.approve { background: #007bff; }
.logout { background: #dc3545; }

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 768px) {

    body {
        padding: 15px;
    }

    h1 {
        text-align: center;
        font-size: 1.6em;
    }

    .cards {
        gap: 15px;
    }

    .card {
        padding: 20px;
    }

    .card h2 {
        font-size: 1.6em;
    }

    .card p {
        font-size: 0.95em;
    }

    .actions {
        margin-top: 20px;
    }

    .actions a {
        display: block;
        width: 100%;
        max-width: 300px;
        margin: 10px auto;
        font-size: 1em;
        padding: 12px;
    }
}
</style>
</head>

<body>

<div class="dashboard">
    <h1>Admin Dashboard</h1>

    <div class="cards">
        <div class="card">
            <h2><?= (int)$total_users ?></h2>
            <p>Total Users</p>
        </div>

        <div class="card">
            <h2><?= (int)$active_users ?></h2>
            <p>Active Users</p>
        </div>

        <div class="card">
            <h2><?= (int)$expired_users ?></h2>
            <p>Expired Users</p>
        </div>

        <div class="card">
            <h2><?= (int)$pending_users ?></h2>
            <p>Pending Payments</p>
        </div>

        <div class="card">
            <h2><?= (int)$live_users ?></h2>
            <p>Live Users (last 5 mins)</p>
        </div>
    </div>

    <div class="actions">
        <a href="approve_payments.php" class="approve">Approve Payments</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
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
