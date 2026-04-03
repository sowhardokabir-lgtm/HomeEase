<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // You should check if user is logged in and get user_id from session
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $service_id = intval($_POST['service_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    if ($rating >= 1 && $rating <= 5 && $comment !== '') {
        $stmt = $conn->prepare("INSERT INTO reviews (service_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiis', $service_id, $user_id, $rating, $comment);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: reviews.php');
    exit;
}
?>
