<?php
session_start();
session_unset();
session_destroy();

/* ✅ FIXED PATH */
header("Location: login.php");
exit();
?>
