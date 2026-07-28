<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$amount  = 99;
$error = "";

/* ===============================
   1️⃣ CHECK IF ALREADY PAID
================================ */
$check = $conn->prepare("
    SELECT payment_status 
    FROM users 
    WHERE id = ?
");
$check->execute([$user_id]);
$user = $check->fetch(PDO::FETCH_ASSOC);

if ($user && $user['payment_status'] === 'PAID') {
    header("Location: ../index.php");
    exit();
}

/* ===============================
   2️⃣ HANDLE FORM SUBMISSION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $utr_number = trim($_POST['utr_number']);

    if ($utr_number === '') {
        $error = "Please enter your Payment Reference.";
    } 
    else if (!preg_match('/^[A-Za-z0-9]{8,22}$/', $utr_number)) {
        $error = "Invalid Payment Reference. Enter the UTR, Transaction ID, or Reference Number shown in your UPI app.";
    }
    else {

        /* ===============================
           🔒 BLOCK DUPLICATE UTR
        ================================ */
        // 🔒 Validate UTR format (server-side)

            $checkPayment = $conn->prepare("
                SELECT id FROM payments 
                WHERE user_id = ? AND status = 'PENDING'
            ");
            $checkPayment->execute([$user_id]);

            if ($checkPayment->rowCount() == 0) {

                $stmt = $conn->prepare("
                    INSERT INTO payments 
                    (user_id, utr_number, amount, status, paid_at) 
                    VALUES (?, ?, ?, 'PENDING', NOW())
                ");
                $stmt->execute([$user_id, $utr_number, $amount]);
            }
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Payment Submitted</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body{
                        font-family: Arial;
                        background:#f4f4f9;
                        display:flex;
                        justify-content:center;
                        align-items:center;
                        min-height:100vh;
                        padding:15px;
                        user-select: none;
                    }
                    .box{
                        background:white;
                        padding:30px;
                        width:100%;
                        max-width:420px;
                        border-radius:10px;
                        text-align:center;
                        box-shadow:0 4px 12px rgba(0,0,0,0.1);
                    }
                    .btn{
                        display:block;
                        margin-top:15px;
                        padding:12px;
                        background:#007bff;
                        color:white;
                        text-decoration:none;
                        border-radius:5px;
                        font-size:16px;
                    }

                    @media (max-width:480px){
                        .box{padding:22px;}
                        h2{font-size:20px;}
                        p{font-size:14px;}
                        .btn{font-size:15px;}
                    }
                </style>
            </head>
            <body>

            <div class="box">
                <h2>✅ Payment Request Submitted</h2>

                <p>
                    Your UTR number has been recorded.<br>
                    Admin approval may take up to <strong>48 hours</strong>.
                </p>

                <p style="color:gray;">
                    You will get access immediately after approval.
                </p>

                <a href="../index.php" class="btn">Go Back</a>
            </div>

            </body>
            </html>
            <?php
            exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter UTR Number</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{
            font-family:Arial;
            background:#f4f4f9;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            padding:15px;
            user-select: none;
        }
        .box{
            background:white;
            padding:30px;
            width:100%;
            max-width:420px;
            border-radius:10px;
            text-align:center;
            box-shadow:0 4px 12px rgba(0,0,0,0.1);
        }
        input{
            width:100%;
            padding:12px;
            margin-top:10px;
            font-size:15px;
        }
        button{
            margin-top:15px;
            padding:12px;
            width:100%;
            background:#28a745;
            color:white;
            border:none;
            font-size:16px;
            cursor:pointer;
            border-radius:5px;
        }
        .error{
            color:red;
            margin-top:10px;
            font-size:14px;
        }

        @media (max-width:480px){
            .box{padding:22px;}
            h2{font-size:20px;}
            p{font-size:14px;}
            input,button{font-size:15px;}
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🔐 Enter Payment Reference</h2>

    <p>
    After paying ₹<?= htmlspecialchars($amount) ?>, enter your
    <strong>Payment Reference</strong> below.
	</p>

    <form method="POST">
        <input
    type="text"
    name="utr_number"
    placeholder="Enter Payment Reference (UTR / Transaction ID / Reference No.)"
    required
>
        <button type="submit">Submit</button>
    </form>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
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
