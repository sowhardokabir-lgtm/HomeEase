<?php
session_start();
include 'db.php'; // assumes $conn is your DB connection

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Services - QuickNest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    :root{ --brand:#176B87; --brand-600:#0A4C68; --brand-700:#073347; --bg:#f6f8fc; --text:#0f172a; }
    body{ background: var(--bg); color: var(--text); }
    .navbar{ background: linear-gradient(90deg, var(--brand), var(--brand-700)); }
    .navbar .nav-link{ color:#fff; } .navbar .nav-link.active{ font-weight:600; }
    .page-hero{ background: radial-gradient(1200px 400px at 10% -10%, #ffffffaa, transparent), linear-gradient(180deg,#ffffff, #eef2ff); border-bottom:1px solid #e5e7eb; }
    .card.service-card{ border:1px solid #e5e7eb; transition:transform .18s, box-shadow .18s, border-color .18s; }
    .service-card:hover{ transform:translateY(-4px); box-shadow:0 12px 28px rgba(2,6,23,.08); border-color:#d9e0ff; }
    .service-card img{ height:200px; object-fit:cover; }
    .footer{ background: linear-gradient(90deg, var(--brand-700), var(--brand)); color:#fff;}
    .stars{ color:#FFD700; }
    .no-rating{ color:#888; }
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

  <!-- ✅ Hero Section -->
  <section class="page-hero py-5 text-center">
    <div class="container">
      <span class="badge rounded-pill text-dark" style="background:#FFD233;">Your city, your service — just one click away!</span>
      <h2 class="display-6 fw-semibold mt-3">Our Services</h2>
      <p class="text-muted mb-0">From professional cleaning to safe pest control, we’ve got you covered.</p>
    </div>
  </section>

  <!-- ✅ Services Grid -->
  <div class="container py-5">
    <div class="row g-4">
      <?php
      // Get all services
      $services = $conn->query("SELECT s.*, u.name as provider_name FROM services s JOIN users u ON s.provider_id = u.id");
      while ($service = $services->fetch_assoc()) {
          $service_id = $service['id'];
          $name = $service['name'];
          $price = $service['price'];
          $desc = $service['description'];
          $img = $service['image'] ?: "image/default.jpg"; // Fallback image
          $provider = $service['provider_name'];
          ?>
          <div class="col-md-4">
            <div class="card service-card h-100">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($name) ?></h5>
                <p class="text-muted small mb-1">Provided by: <strong><?= htmlspecialchars($provider) ?></strong></p>
                <p class="text-muted flex-grow-1"><?= htmlspecialchars($desc) ?></p>

                <?php
                // Rating summary
                $avg_result = $conn->query("SELECT AVG(rating) AS avg_rating, COUNT(*) AS count FROM reviews WHERE service_id = $service_id");
                $avg = $avg_result->fetch_assoc();
                $avg_rating = $avg['avg_rating'];
                $count = $avg['count'];

                if ($count > 0) {
                    $stars = round($avg_rating);
                    echo "<div class='stars mb-2'>";
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $stars ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                    }
                    echo " <small>($count reviews)</small></div>";
                } else {
                    echo "<div class='no-rating mb-2'>Not rated yet</div>";
                }

                // Review actions if logged in
                if ($user_id) {
                    $user_review = $conn->query("SELECT id FROM reviews WHERE service_id = $service_id AND user_id = $user_id")->fetch_assoc();
                    if ($user_review) {
                        echo "<a href='edit_review.php?service_id=$service_id' class='btn btn-warning btn-sm mb-2'>Edit Review</a>";
                    } else {
                        echo "<a href='add_review.php?service_id=$service_id' class='btn btn-outline-primary btn-sm mb-2'>Add Review</a>";
                    }
                }
                ?>

                <div class="d-flex justify-content-between align-items-center mt-auto">
                  <span class="fw-bold">৳<?= $price ?></span>
                  <div class="d-flex gap-2">
                    <a href="see_reviews.php?service_id=<?= $service_id ?>" class="btn btn-outline-secondary btn-sm">Reviews</a>
                    <a href="bookservice.php?service=<?= urlencode($name) ?>" class="btn btn-primary btn-sm">Book Now</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php
      }
      ?>
    </div>
  </div>

  <footer class="footer text-center p-3 mt-auto">
    <p class="mb-0">© 2025 HomeEase • All rights reserved</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>