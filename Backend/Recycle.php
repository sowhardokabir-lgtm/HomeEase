<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = $_POST['item_type'];
    $weight = $_POST['weight'];
    $pts = $weight * 10; // Simple logic: 10 points per kg
    
    $stmt = $conn->prepare("INSERT INTO recycle_logs (user_id, item, points) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $_SESSION['user_id'], $item, $pts);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Recycle & Earn - HomeEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .page-header { padding: 5rem 0; background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%); text-align: center; }
        .btn-sky { background: #0ea5e9; color: white; font-weight: 700; border-radius: 12px; padding: 12px; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Recycle & <span>Earn Points</span></h1>
    </div>
    <div class="container">
        <div class="card p-4 shadow-sm border-0">
            <form method="POST">
                <label>Item Category</label>
                <select name="item_type" class="form-select mb-3">
                    <option>Plastic Bottles</option>
                    <option>E-Waste</option>
                </select>
                <input type="number" name="weight" class="form-control mb-3" placeholder="Weight in KG">
                <button type="submit" class="btn-sky w-100">Submit Pickup Request</button>
            </form>
        </div>
    </div>
</body>
</html>