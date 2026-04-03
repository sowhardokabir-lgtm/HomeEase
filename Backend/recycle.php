<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('Please log in first!'); window.location='login.php';</script>";
    exit();
}

$user_id = $_SESSION["user_id"];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Handle New Request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_request'])) {
    $item_type = trim($_POST['item_type']);
    $description = trim($_POST['description']);
    $image_path = "image/default.jpg"; // Default if no upload

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "image/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    if ($item_type) {
        $stmt = $conn->prepare("INSERT INTO recycling_requests (user_id, item_type, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $item_type, $description, $image_path);
        if ($stmt->execute()) {
            echo "<script>alert('Request submitted! Wait for admin offer.'); window.location='recycle.php';</script>";
        } else {
            echo "<script>alert('Error submitting request.');</script>";
        }
    }
}

// Handle User Decision (Accept/Decline)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['decision'])) {
    $req_id = (int)$_POST['req_id'];
    $decision = $_POST['decision']; // 'accept' or 'decline'
    
    // Verify request belongs to user and is in 'Offer Pending' state
    $check = $conn->query("SELECT * FROM recycling_requests WHERE id=$req_id AND user_id=$user_id AND status='Offer Pending'");
    if ($check->num_rows > 0) {
        $req = $check->fetch_assoc();
        $offer = $req['admin_offer'];
        
        if ($decision === 'accept') {
            $conn->query("UPDATE recycling_requests SET status='Accepted', points_awarded=$offer WHERE id=$req_id");
            $conn->query("UPDATE users SET reward_points = reward_points + $offer WHERE id=$user_id");
            echo "<script>alert('Offer Accepted! Points added.'); window.location='recycle.php';</script>";
        } elseif ($decision === 'decline') {
            $conn->query("UPDATE recycling_requests SET status='Declined' WHERE id=$req_id");
            echo "<script>alert('Offer Declined.'); window.location='recycle.php';</script>";
        }
    }
}

// Fetch history
$requests = $conn->query("SELECT * FROM recycling_requests WHERE user_id = $user_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Recycle & Earn - HomeEase</title>
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
    <a class="navbar-brand fw-bold" href="home.php"><i class="bi bi-stars me-1"></i> HomeEase</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="recycle.php">Recycle</a></li>
        <li class="nav-item"><a class="nav-link" href="bookservice.php">Book Service</a></li>
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row">
    <div class="col-md-4">
      <div class="card p-4 text-center mb-4">
        <h4>Your Balance</h4>
        <h1 class="display-4 fw-bold text-success"><?= $user['reward_points'] ?></h1>
        <p class="text-muted">Points</p>
        <small>1 Point = 1 Taka Discount</small>
      </div>
      
      <div class="card p-4">
        <h4>Submit Request</h4>
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Item Type</label>
            <select name="item_type" class="form-select" required>
              <option value="Plastic">Plastic</option>
              <option value="Old Furniture">Old Furniture</option>
              <option value="Glass">Glass</option>
              <option value="Paper">Paper</option>
              <option value="E-Waste">E-Waste</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Description (Quantity/Condition)</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Upload Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
          </div>
          <button type="submit" name="submit_request" class="btn btn-primary w-100" style="background-color:#176B87">Submit Request</button>
        </form>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card p-4">
        <h4>Request History</h4>
        <?php if ($requests->num_rows === 0): ?>
          <p class="text-muted">No requests yet.</p>
        <?php else: ?>
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Image</th>
                <th>Item</th>
                <th>Status</th>
                <th>Offer</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while($r = $requests->fetch_assoc()): ?>
                <tr>
                  <td><img src="<?= htmlspecialchars($r['image_path']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;"></td>
                  <td>
                    <strong><?= htmlspecialchars($r['item_type']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($r['description']) ?></small>
                  </td>
                  <td>
                    <?php 
                    if($r['status']=='Accepted') echo '<span class="badge bg-success">Accepted</span>';
                    elseif($r['status']=='Declined') echo '<span class="badge bg-secondary">Declined</span>';
                    elseif($r['status']=='Rejected') echo '<span class="badge bg-danger">Rejected</span>';
                    elseif($r['status']=='Offer Pending') echo '<span class="badge bg-info text-dark">Offer Received</span>';
                    else echo '<span class="badge bg-warning text-dark">Pending</span>'; 
                    ?>
                  </td>
                  <td><?= $r['status'] == 'Offer Pending' || $r['status'] == 'Accepted' ? $r['admin_offer'] . ' pts' : '-' ?></td>
                  <td>
                    <?php if ($r['status'] === 'Offer Pending'): ?>
                      <form method="POST" class="d-flex gap-2">
                        <input type="hidden" name="req_id" value="<?= $r['id'] ?>">
                        <button type="submit" name="decision" value="accept" class="btn btn-success btn-sm">Accept</button>
                        <button type="submit" name="decision" value="decline" class="btn btn-outline-danger btn-sm">Decline</button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
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
