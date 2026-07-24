<?php
require_once '../includes/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$checkPending = $conn->prepare(
    "SELECT id FROM payments WHERE user_id = ? AND status = 'PENDING'"
);
$checkPending->execute([$user_id]);
$pendingCount = $checkPending->rowCount();

if ($pendingCount > 0) {
    echo "<script>
        alert('You already have a pending payment. Please wait for admin approval.');
        window.location.href = '../index.php';
    </script>";
    exit();
}

$upi_id = "9381182346@axl";
$name   = "Speak2Select";
$amount = "100";
$note   = "Speak2Select Subscription";

$upi_string = "upi://pay?pa=$upi_id&pn=$name&am=$amount&cu=INR&tn=" . urlencode($note);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($upi_string);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Speak2Select – Payment</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    padding: 30px;
    width: 100%;
    max-width: 400px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    box-sizing: border-box;
}

/* Button */
.pay-btn {
    background: #28a745;
    color: white;
    padding: 12px;
    border: none;
    width: 100%;
    font-size: 16px;
    cursor: pointer;
    border-radius: 5px;
}
.pay-btn:hover {
    background: #218838;
}

/* QR image */
img {
    margin: 15px 0;
    max-width: 100%;
    height: auto;
}

/* Input */
input {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 1em;
    box-sizing: border-box;
}

p {
    margin: 8px 0;
}

/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 480px) {

    body {
        padding: 15px;
        align-items: flex-start;
    }

    .box {
        margin-top: 30px;
        padding: 22px;
    }

    h2 {
        font-size: 1.4em;
    }

    h3 {
        font-size: 1.2em;
    }

    p {
        font-size: 0.95em;
    }

    .pay-btn {
        font-size: 1em;
        padding: 11px;
    }

    input {
        font-size: 0.95em;
    }
}
</style>
</head>

<body>

<div class="box">
    <h2>🚀 Invest in Your Success</h2>

    <p>
        “Clear your communication round with confidence.<br>
        One small step today can change your career tomorrow.”
    </p>

    <h3>💰 Amount: ₹100</h3>

    <img src="<?= $qr_url ?>" alt="UPI QR Code">

    <p>Scan & Pay ₹100</p>

    <form action="payment_request.php" method="post" onsubmit="return validateUTR();">
    <input
        type="text"
        name="utr_number"
        id="utr_number"
        placeholder="Enter UTR (8–22 characters)"
        required
        minlength="8"
        maxlength="22"
        pattern="[A-Za-z0-9]{8,22}"
        title="Enter valid UTR (letters & numbers only)"
    >


        <button type="submit" class="pay-btn">I Have Paid</button>
    </form>

    <p style="margin-top:10px;color:gray;">
        Admin approval may take up to <strong>48 hours</strong>
    </p>
</div>

<script>
function validateUTR() {
    const utr = document.getElementById('utr_number').value.trim();
    const regex = /^[A-Za-z0-9]{8,22}$/;

    if (!regex.test(utr)) {
        alert("Invalid UTR.\nPlease enter the correct UTR shown in your UPI app.");
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
