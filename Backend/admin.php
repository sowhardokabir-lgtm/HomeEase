 <?php
// admin.php
session_start();
include "db.php";

// Only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
  echo "<script>alert('Access denied! Admins only.'); window.location='login.php';</script>";
  exit();
}

// Fetch all bookings
$result = $conn->query("
  SELECT b.id, u.name AS user_name, u.email, s.name AS service, b.date, b.time, b.address, b.phone, b.status, b.created_at
  FROM bookings b
  JOIN users u ON b.user_id = u.id
  JOIN services s ON b.service_id = s.id
  ORDER BY b.created_at DESC
");

$user_id = $_SESSION['user_id'] ?? null;
$user_name = '';
if ($user_id) {
    $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    if ($user) $user_name = $user['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - HomeEase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    :root{ --brand:#176B87; --brand-600:#0A4C68; --brand-700:#073347; --bg:#f6f8fc; }
    body{ background:var(--bg); }
    .navbar{ background: linear-gradient(90deg, var(--brand), var(--brand-700)); }
    .navbar .nav-link{ color:#fff; } .navbar .nav-link.active{ font-weight:600; }
    .card{ border-radius:12px; box-shadow:0 6px 16px rgba(2,6,23,.08); }
  </style>
  <link rel="icon" type="image/x-icon" href="image/favicon.png">
</head>
<body>
 <!-- ✅ Navbar -->
 <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="home.php"><i class="bi bi-stars me-1"></i> HomeEase</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
      <div id="nav" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="home.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="service.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="bookservice.php">Book Service</a></li>
          <li class="nav-item"><a class="nav-link" href="recycle.php">Recycle</a></li>
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <?php if(!$user_id): ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
          <?php else: ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><?= htmlspecialchars($user_name) ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="mybookings.php">My Bookings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </li>
          <?php endif; ?>
          
        </ul>
      </div>
    </div>
  </nav>


<div class="container py-5">
  <h3 class="mb-4">Admin Dashboard</h3>
  <button class="btn btn-primary mb-3" style="background-color:#176B87" onclick="window.location.href='user.php'">Manage Users</button>
  <div class="card p-3">
    <?php if ($result->num_rows === 0): ?>
      <div class="alert alert-info m-2">No bookings found.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Email</th>
              <th>Service</th>
              <th>Date</th>
              <th>Time</th>
              <th>Address</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td>#<?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars($row['user_name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['service']) ?></td>
              <td><?= htmlspecialchars($row['date']) ?></td>
              <td><?= htmlspecialchars(substr($row['time'],0,5)) ?></td>
              <td><?= htmlspecialchars($row['address']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>


  <!-- Recycling Requests Section -->
  <h3 class="mb-4 mt-5">Recycling Requests</h3>
  <div class="card p-3">
    <?php
    // Handle Actions
    if (isset($_POST['action_recycle'])) {
        $req_id = (int)$_POST['req_id'];
        $action = $_POST['action_recycle'];
        $points = (int)($_POST['points'] ?? 0);

        if ($action === 'offer') {
            $conn->query("UPDATE recycling_requests SET status='Offer Pending', admin_offer=$points WHERE id=$req_id");
            echo "<div class='alert alert-success'>Offer sent to user!</div>";
        } elseif ($action === 'reject') {
            $conn->query("UPDATE recycling_requests SET status='Rejected' WHERE id=$req_id");
            echo "<div class='alert alert-warning'>Request Rejected.</div>";
        }
    }

    $recycle_requests = $conn->query("SELECT r.*, u.name FROM recycling_requests r JOIN users u ON r.user_id = u.id WHERE r.status='Pending' ORDER BY r.created_at DESC");
    ?>

    <?php if ($recycle_requests->num_rows === 0): ?>
      <div class="alert alert-info m-2">No pending recycling requests.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th>User</th>
              <th>Image</th>
              <th>Item</th>
              <th>Description</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while($row = $recycle_requests->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><a href="<?= htmlspecialchars($row['image_path']) ?>" target="_blank"><img src="<?= htmlspecialchars($row['image_path']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;"></a></td>
              <td><?= htmlspecialchars($row['item_type']) ?></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
              <td>
                <form method="POST" class="d-flex gap-2">
                  <input type="hidden" name="req_id" value="<?= $row['id'] ?>">
                  <input type="number" name="points" class="form-control form-control-sm" placeholder="Offer Pts" style="width:80px;" required>
                  <button type="submit" name="action_recycle" value="offer" class="btn btn-primary btn-sm">Make Offer</button>
                  <button type="submit" name="action_recycle" value="reject" class="btn btn-danger btn-sm" formnovalidate>Reject</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer class="text-center p-3 mt-4" style="background:linear-gradient(90deg,var(--brand-700),var(--brand)); color:#fff;">
  <p class="mb-0">© 2025 QuickNest • All rights reserved</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
