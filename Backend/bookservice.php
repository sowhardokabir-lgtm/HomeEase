<?php
session_start();
include "db.php";

// Make sure user is logged in
if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('Please log in first!'); window.location='login.php';</script>";
    exit();
}

$success = false;
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $service_ids = $_POST["service_id"] ?? [];
    $date = $_POST["date"] ?? null;
    $time = $_POST["time"] ?? null;
    $address = $_POST["address"] ?? null;
    $phone = $_POST["phone"] ?? null;

    $payment_method = $_POST["payment_method"] ?? "COD";
    $payment_status = ($payment_method === "Card") ? "Paid" : "Pending";

    // Validate fields
    if (empty($service_ids) || !$date || !$time || !$address || !$phone) {
        $error = "All fields are required!";
    } else {
        $datetime = strtotime("$date $time");
        if ($datetime < time()) {
            $error = "You cannot select a past date or time!";
        } else {
            // Calculate Price & Discount (Mocking price fetching for simplicity, ideally fetch from DB)
            // For this mock, we assume each service is 500 if not fetched. 
            // BUT we are inserting multiple services. Let's assume we just track the discount on the FIRST booking for simplicity or split it.
            // BETTER: Just apply discount to the batch.
            
            $redeem = isset($_POST['redeem_points']);
            $discount = 0;
            
            if ($redeem) {
                // Fetch current points again to be safe
                $u_res = $conn->query("SELECT reward_points FROM users WHERE id=$user_id");
                $u_row = $u_res ? $u_res->fetch_assoc() : null;
                $points = $u_row ? $u_row['reward_points'] : 0;
                $discount = $points; // 1 Point = 1 Taka
            }

            // Insert bookings
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, date, time, address, phone, status, payment_method, payment_status, discount_amount, final_price) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?)");
            
            foreach ($service_ids as $index => $sid) {
                // Fetch service price
                $s_row = $conn->query("SELECT price FROM services WHERE id=$sid")->fetch_assoc();
                $price = $s_row['price'];
                
                // Apply discount only to the first service in the batch to avoid double dipping, or split it.
                // Strategy: Apply full discount to first item, if discount > price, carry over? 
                // SIMPLIFICATION: Apply max discount up to price on first item.
                
                $current_discount = 0;
                if ($discount > 0) {
                    if ($discount >= $price) {
                        $current_discount = $price; // Free service
                        $discount -= $price;
                    } else {
                        $current_discount = $discount;
                        $discount = 0;
                    }
                }
                
                $final_price = $price - $current_discount;

                $stmt->bind_param("iissssssdd", $user_id, $sid, $date, $time, $address, $phone, $payment_method, $payment_status, $current_discount, $final_price);
                $stmt->execute();
            }
            $stmt->close();

            // Deduct points if used
            if ($redeem) {
                $points_used = $u_row['reward_points'] - $discount; // Total used
                $conn->query("UPDATE users SET reward_points = reward_points - $points_used WHERE id=$user_id");
            }

            $success = true;
        }
    }
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_name = '';
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
if ($user) $user_name = $user['name'];

// Fetch services for buttons
$services = $conn->query("SELECT * FROM services");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Book Service -HomeEase</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<style>
:root{--brand:#176B87;--brand-600:#0A4C68;--brand-700:#073347;--bg:#f6f8fc;--text:#0f172a;}
body{background:var(--bg);color:var(--text);}
.navbar{background: linear-gradient(90deg, var(--brand), var(--brand-700));}
.navbar .nav-link{color:#fff;}
.navbar .nav-link.active{font-weight:600;}
.card{border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 6px 16px rgba(2,6,23,.08);}
.btn-brand{background:var(--brand);border-color:var(--brand);color:#fff;}
.btn-brand:hover{background:var(--brand-600);border-color:var(--brand-600);}
.btn-check:checked + .btn-outline-primary{background-color:var(--brand); color:#fff; border-color:var(--brand);}
.footer{background: linear-gradient(90deg, var(--brand-700), var(--brand));color:#fff;text-align:center;padding:1rem;margin-top:3rem;}
</style>
</head>
<body>

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
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><?= htmlspecialchars($user_name) ?></a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="mybookings.php">My Bookings</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="logout.php">Logout</a></li>
</ul>
</li>
</ul>
</div>
</div>
</nav>

<main class="container py-5 d-flex flex-column align-items-center">
<div class="col-md-8 col-lg-6">
<div class="card shadow-sm">
<div class="card-body p-4">
<h3 class="card-title text-center mb-4">Book a Service</h3>
<?php if($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST">
<div class="mb-3">
<label class="form-label">Select Services</label>
<div class="d-flex flex-wrap gap-2">
<?php while($s = $services->fetch_assoc()): ?>
<input type="checkbox" class="btn-check" name="service_id[]" id="service<?= $s['id'] ?>" value="<?= $s['id'] ?>">
<label class="btn btn-outline-primary" for="service<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></label>
<?php endwhile; ?>
</div>
</div>

<div class="mb-3">
<label for="date" class="form-label">Preferred Date</label>
<input type="date" class="form-control" name="date" id="date" required min="<?= date('Y-m-d') ?>"/>
</div>
<div class="mb-3">
<label for="time" class="form-label">Preferred Time</label>
<input type="time" class="form-control" name="time" id="time" required/>
</div>
<div class="mb-3">
<label for="address" class="form-label">Service Address</label>
<textarea class="form-control" name="address" id="address" rows="3" required></textarea>
</div>
<div class="mb-3">
<label for="phone" class="form-label">Contact Number</label>
<input type="tel" class="form-control" name="phone" id="phone" required/>
</div>

<div class="mb-3">
  <label class="form-label">Payment Method</label>
  <div class="form-check">
    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked onclick="togglePayment('cod')">
    <label class="form-check-label" for="cod">Cash on Delivery</label>
  </div>
  <div class="form-check">
    <input class="form-check-input" type="radio" name="payment_method" id="card" value="Card" onclick="togglePayment('card')">
    <label class="form-check-label" for="card">Credit/Debit Card</label>
  </div>
</div>

<div id="card-details" class="mb-3 p-3 border rounded bg-light" style="display:none;">
  <h6>Enter Card Details (Mock)</h6>
  <div class="row g-2">
    <div class="col-12">
      <input type="text" class="form-control" placeholder="Card Number" maxlength="16">
    </div>
    <div class="col-6">
      <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
    </div>
    <div class="col-6">
      <input type="text" class="form-control" placeholder="CVC" maxlength="3">
    </div>
  </div>
</div>

<script>
function togglePayment(method) {
  const cardDetails = document.getElementById('card-details');
  if (method === 'card') {
    cardDetails.style.display = 'block';
    document.querySelectorAll('#card-details input').forEach(i => i.required = true);
  } else {
    cardDetails.style.display = 'none';
    document.querySelectorAll('#card-details input').forEach(i => i.required = false);
  }
}
</script>
<?php
// Fetch user points
$user_points = 0;
if ($user_id) {
    $u_res = $conn->query("SELECT reward_points FROM users WHERE id=$user_id");
    if ($u_res) {
        $row = $u_res->fetch_assoc();
        $user_points = $row ? $row['reward_points'] : 0;
    }
}
?>

<div class="mb-3 p-3 bg-light border rounded">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <strong>Redeem Rewards</strong><br>
      <small class="text-muted">Available Points: <span class="badge bg-warning text-dark"><?= $user_points ?></span></small>
    </div>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="redeem_points" value="1" id="redeemSwitch" <?= $user_points == 0 ? 'disabled' : '' ?>>
      <label class="form-check-label" for="redeemSwitch">Use Points</label>
    </div>
  </div>
  <small class="text-success" id="discount-msg" style="display:none;">You will save ৳<?= $user_points ?></small>
</div>

<script>
document.getElementById('redeemSwitch').addEventListener('change', function() {
    document.getElementById('discount-msg').style.display = this.checked ? 'block' : 'none';
});
</script>

<div class="d-grid gap-2">
<button class="btn btn-brand" type="submit">Confirm Booking</button>
</div>
</form>
</div>
</div>
</div>
</main>

<footer class="footer"><p class="mb-0">© 2025 QuickNest • All rights reserved</p></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if($success): ?>
<script>alert("✅ Service(s) booked successfully!");</script>
<?php endif; ?>
</body>
</html>
