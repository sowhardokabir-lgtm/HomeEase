<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('Please log in first!'); window.location='login.php';</script>";
    exit();
}

$role = $_SESSION["user_role"] ?? 'user';

if ($role === 'admin') {
    header("Location: admin.php");
} elseif ($role === 'provider') {
    header("Location: provider_dashboard.php");
} else {
    // For regular users, 'My Bookings' acts as their dashboard for now
    header("Location: mybookings.php");
}
exit();
?>
