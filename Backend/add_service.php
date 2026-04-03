<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "provider") {
    echo "<script>alert('Access denied!'); window.location='login.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $image = trim($_POST['image']);
    $provider_id = $_SESSION['user_id'];

    if ($name && $price) {
        $stmt = $conn->prepare("INSERT INTO services (provider_id, name, description, price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issds", $provider_id, $name, $description, $price, $image);
        
        if ($stmt->execute()) {
            echo "<script>alert('Service added successfully!'); window.location='provider_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error adding service.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Service - QuickNest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body{ background:#f6f8fc; }
    .card{ max-width:500px; margin:50px auto; border-radius:12px; box-shadow:0 6px 16px rgba(2,6,23,.08); }
  </style>
</head>
<body>

<div class="container">
  <div class="card p-4">
    <h3 class="text-center mb-4">Add New Service</h3>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Service Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Price (৳)</label>
        <input type="number" name="price" class="form-control" step="0.01" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Image URL</label>
        <input type="url" name="image" class="form-control" placeholder="https://example.com/image.jpg">
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary" style="background-color:#176B87">Add Service</button>
        <a href="provider_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
