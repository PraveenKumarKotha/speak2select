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

/* ===============================
   ADMIN LOGIN CHECK
================================ */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/* ===============================
   HANDLE APPROVE / REJECT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_id = $_POST['payment_id'];
    $action     = $_POST['action'];

    $fetch = $conn->prepare("
        SELECT 
            p.user_id,
            p.utr_number,
            p.amount,
            u.email,
            u.name
        FROM payments p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $fetch->execute([$payment_id]);
    $data = $fetch->fetch(PDO::FETCH_ASSOC);

    if ($data) {

        $user_id = $data['user_id'];
        $email   = $data['email'];
        $name    = $data['name'];
        $utr     = $data['utr_number'];
        $amount  = $data['amount'];

        /* ========= APPROVE ========= */
        if ($action === 'approve') {
            // 🔒 BLOCK DUPLICATE APPROVAL FOR SAME UTR
            $check = $conn->prepare("
            SELECT COUNT(*) 
            FROM payments 
            WHERE utr_number = ? AND status = 'APPROVED'
            ");
            $check->execute([$utr]);

        if ($check->fetchColumn() > 0) {
        // UTR already approved earlier
        header("Location: approve_payments.php?error=utr_used");
        exit();
        }

            $expiry = date('Y-m-d', strtotime('+1 year'));

            $update = $conn->prepare("
                UPDATE payments 
                SET status = 'APPROVED'
                WHERE id = ? AND status = 'PENDING'
            ");
            $update->execute([$payment_id]);

            // 🔒 Only if payment was approved JUST NOW
            if ($update->rowCount() === 1) {

                $conn->prepare("
                    UPDATE users 
                    SET payment_status = 'PAID',
                        access_expiry_date = ?
                    WHERE id = ?
                ")->execute([$expiry, $user_id]);

                sendMail($email, $name, $utr, $amount, true);
            }

        } 
        /* ========= REJECT ========= */
        else {

            $update = $conn->prepare("
                UPDATE payments 
                SET status = 'REJECTED'
                WHERE id = ? AND status = 'PENDING'
            ");
            $update->execute([$payment_id]);

            if ($update->rowCount() === 1) {
                sendMail($email, $name, $utr, $amount, false);
            }
        }

        header("Location: approve_payments.php");
        exit();
    }
}

/* ===============================
   FETCH PENDING PAYMENTS
================================ */
$stmt = $conn->prepare("
    SELECT 
        p.id,
        p.utr_number,
        p.amount,
        u.name,
        u.email
    FROM payments p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'PENDING'
    ORDER BY p.id DESC
");
$stmt->execute();
$pending_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   EMAIL FUNCTION
================================ */
function sendMail($email, $name, $utr, $amount, $approved) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('speak2select@gmail.com', 'Speak2Select');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);

        if ($approved) {
            $mail->Subject = 'Payment Approved - Speak2Select';
            $mail->Body = "
                <p>Hi <b>$name</b>,</p>
                <p>Your payment has been <b>approved</b>.</p>
                <p><b>UTR:</b> $utr</p>
                <p><b>Amount:</b> ₹$amount</p>
                <p>Your access is active for <b>1 year</b>.</p>
            ";
        } else {
            $mail->Subject = 'Payment Rejected - Speak2Select';
            $mail->Body = "
                <p>Hi <b>$name</b>,</p>
                <p>Your payment has been <b>rejected</b>.</p>
                <p><b>UTR:</b> $utr</p>
                <p>Please contact support if needed.</p>
            ";
        }

        $mail->send();
    } catch (Exception $e) {
        // silent fail
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Approve Payments</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    padding: 20px;
    user-select: none;
}

.search-box {
    max-width: 400px;
    margin-bottom: 15px;
}

.search-box input {
    width: 100%;
    padding: 10px;
    font-size: 15px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    min-width: 700px;
    background: #fff;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border: 1px solid #ccc;
    text-align: center;
}

th {
    background: #343a40;
    color: #fff;
}

button {
    width: 100%;
    padding: 8px;
    border: none;
    color: white;
    cursor: pointer;
    border-radius: 4px;
}

.approve { background: green; }
.reject  { background: red; }

@media (max-width: 768px) {
    h2 { text-align: center; }
}
</style>

<script>
function searchUTR() {
    const input = document.getElementById("utrSearch").value.toUpperCase();
    const rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
        const utrCell = row.querySelector(".utr");

        // safety check
        if (!utrCell) {
            row.style.display = "";
            return;
        }

        const utr = utrCell.textContent.toUpperCase();
        row.style.display = utr.includes(input) ? "" : "none";
    });
}
</script>

</head>

<body>

<h2>Pending Payments</h2>
<?php if (isset($_GET['error']) && $_GET['error'] === 'utr_used'): ?>
    <p style="color:red; font-weight:bold;">
        ⚠ This UTR is already approved earlier. Approval blocked.
    </p>
<?php endif; ?>
<div class="search-box">
    <input 
        type="text" 
        id="utrSearch" 
        placeholder="Search by UTR"
        onkeyup="searchUTR()"
    >
</div>

<?php if (count($pending_payments) === 0): ?>
    <p>No pending payments.</p>
<?php else: ?>
<div class="table-wrapper">
<table>
<thead>
<tr>
    <th>User</th>
    <th>Email</th>
    <th>UTR</th>
    <th>Amount</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($pending_payments as $p): ?>
<tr>
    <td><?= htmlspecialchars($p['name']) ?></td>
    <td><?= htmlspecialchars($p['email']) ?></td>
    <td class="utr"><?= htmlspecialchars($p['utr_number']) ?></td>
    <td>₹<?= htmlspecialchars($p['amount']) ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
            <button class="approve" name="action" value="approve">Approve</button>
            <button class="reject" name="action" value="reject">Reject</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<div style="text-align:center; margin-top:25px;">
    <a href="dashboard.php" style="
        padding:12px 25px;
        background:#0d6efd;
        color:white;
        text-decoration:none;
        border-radius:6px;
        font-weight:bold;
    ">
        ⬅ Back to Dashboard
    </a>
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
