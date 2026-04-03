<?php
session_start();
include "db.php";

// Check if user is logged in and is a provider
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "provider") {
    echo "<script>alert('Access denied!'); window.location='login.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $bio = trim($_POST['bio']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE users SET bio=?, phone=?, address=? WHERE id=?");
    $stmt->bind_param("sssi", $bio, $phone, $address, $user_id);
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}

// Handle Service Deletion
if (isset($_GET['delete_service'])) {
    $service_id = (int)$_GET['delete_service'];
    $stmt = $conn->prepare("DELETE FROM services WHERE id=? AND provider_id=?");
    $stmt->bind_param("ii", $service_id, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('Service deleted!'); window.location='provider_dashboard.php';</script>";
    }
}

// Fetch User Details
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Fetch Services
$services = $conn->query("SELECT * FROM services WHERE provider_id=$user_id");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Provider Dashboard - HomeEase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    :root{ --brand:#176B87; --brand-600:#0A4C68; --brand-700:#073347; --bg:#f6f8fc; }
    body{ background:var(--bg); }
    .navbar{ background: linear-gradient(90deg, var(--brand), var(--brand-700)); }
    .navbar .nav-link{ color:#fff; }
    .card{ border-radius:12px; box-shadow:0 6px 16px rgba(2,6,23,.08); }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-stars me-1"></i> HomeEase Provider</a>
    <div class="ms-auto">
      <span class="text-white me-3">Welcome, <?= htmlspecialchars($user['name']) ?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <?php if($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
  <?php endif; ?>

  <div class="row">
    <!-- Profile Section -->
    <div class="col-md-4">
      <div class="card p-4 mb-4">
        <h4>Edit Profile</h4>
        <form method="POST">
          <div class="mb-3">
            <label>Bio</label>
            <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label>Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
          </div>
          <button type="submit" name="update_profile" class="btn btn-primary w-100" style="background-color:#176B87">Update Profile</button>
        </form>
      </div>
    </div>

    <!-- Services Section -->
    <div class="col-md-8">
      <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4>My Services</h4>
          <a href="add_service.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Service</a>
        </div>
        
        <?php if ($services->num_rows === 0): ?>
          <p class="text-muted">You haven't added any services yet.</p>
        <?php else: ?>
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while($s = $services->fetch_assoc()): ?>
                <tr>
                  <td><img src="<?= htmlspecialchars($s['image']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;"></td>
                  <td><?= htmlspecialchars($s['name']) ?></td>
                  <td>৳<?= htmlspecialchars($s['price']) ?></td>
                  <td>
                    <a href="?delete_service=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
