<?php
session_start();
include "db.php";

// --- 1) Block guests ---
if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('Please log in first'); window.location='login.php';</script>";
    exit();
}
$user_id = $_SESSION["user_id"];
$user_id = $_SESSION['user_id'] ?? null;
$user_name = '';
if ($user_id) {
    $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    if ($user) $user_name = $user['name'];
}

// --- 2) Handle cancel action (POST) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_id"])) {
    $cancel_id = (int)$_POST["cancel_id"];

    $stmt = $conn->prepare("UPDATE bookings SET status='Cancelled' 
                            WHERE id=? AND user_id=? AND status='Pending'");
    $stmt->bind_param("ii", $cancel_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Booking cancelled'); window.location='mybookings.php';</script>";
    } else {
        echo "<script>alert('Cannot cancel: already processed or not found'); window.location='mybookings.php';</script>";
    }
    $stmt->close();
    $conn->close();
    exit();
}

// --- 3) Fetch user bookings ---
$stmt = $conn->prepare("
    SELECT b.id, s.name AS service, s.price, b.date, b.time, b.address, b.phone, b.status, b.created_at
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>My Bookings - HomeEase</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
<style>
:root{ --brand:#176B87; --brand-600:#0A4C68; --brand-700:#073347; --bg:#f6f8fc; --text:#0f172a; }
body{ background:var(--bg); color:var(--text); }
.navbar{ background: linear-gradient(90deg, var(--brand), var(--brand-700)); }
.navbar .nav-link{ color:#fff; } .navbar .nav-link.active{ font-weight:600; }
.card{ border-radius:12px; box-shadow:0 6px 16px rgba(2,6,23,.08); }
.btn-brand{ background:var(--brand); color:#fff; border:0; }
.btn-brand:hover{ background:var(--brand-600); }
.badge-pending{ background:#f59e0b; }   /* amber */
.badge-confirmed{ background:#22c55e; } /* green */
.badge-completed{ background:#0ea5e9; } /* sky */
.badge-cancelled{ background:#ef4444; } /* red */
</style>
<link rel="icon" type="image/x-icon" href="image/favicon.png">
</head>
<body class="d-flex flex-column min-vh-100">
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
           <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="recycle.php">Recycle</a></li>
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
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0">My Bookings</h3>
    <a href="bookservice.php" class="btn btn-brand"><i class="bi bi-plus-circle me-1"></i> New Booking</a>
  </div>

  <div class="card p-3">
    <?php if ($result->num_rows === 0): ?>
      <div class="alert alert-info m-2">
        You have no bookings yet. <a href="bookservice.php" class="alert-link">Book your first service</a>.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Service</th>
              <th>Price</th>
              <th>Date</th>
              <th>Time</th>
              <th>Address</th>
              <th>Phone</th>
              <th>Status</th>
              <th style="width:140px;">Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while($row = $result->fetch_assoc()): 
            $badgeClass = 'badge-pending';
            if ($row['status'] === 'Confirmed')  $badgeClass = 'badge-confirmed';
            if ($row['status'] === 'Completed')  $badgeClass = 'badge-completed';
            if ($row['status'] === 'Cancelled')  $badgeClass = 'badge-cancelled';
          ?>
            <tr>
              <td>#<?= (int)$row['id'] ?></td>
              <td><?= htmlspecialchars($row['service']) ?></td>
              <td>৳<?= (int)$row['price'] ?></td>
              <td><?= htmlspecialchars($row['date']) ?></td>
              <td><?= htmlspecialchars(substr($row['time'],0,5)) ?></td>
              <td><?= htmlspecialchars($row['address']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
              <td>
                <?php if ($row['status'] === 'Pending'): ?>
                  <form method="POST" onsubmit="return confirm('Cancel this booking?');" class="d-inline">
                    <input type="hidden" name="cancel_id" value="<?= (int)$row['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Cancel</button>
                  </form>
                <?php else: ?>
                  <button class="btn btn-sm btn-outline-secondary" disabled>No actions</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer class="text-center p-3 mt-auto" style="background:linear-gradient(90deg,var(--brand-700),var(--brand)); color:#fff;">
  <p class="mb-0">© 2026 HomeEase • All rights reserved</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>