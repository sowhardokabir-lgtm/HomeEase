<?php
session_start();
include 'db.php';

$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
$service = $conn->query("SELECT * FROM services WHERE id = $service_id")->fetch_assoc();
if (!$service) {
    echo "<p>Service not found.</p>";
    exit;
}

$reviews = $conn->query("
    SELECT r.*, u.name AS user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.service_id = $service_id 
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/x-icon" href="image/favicon.png">
<meta charset="UTF-8">
<title>All Reviews for <?= htmlspecialchars($service['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
:root{
  --brand:#176B87; 
  --brand-600:#0A4C68; 
  --brand-700:#073347; 
  --bg:#f6f8fc; 
  --text:#0f172a; 
}
body{ background: var(--bg); color: var(--text); font-family: Arial, sans-serif; }
.container{ max-width: 800px; margin: 50px auto; }
h1{ color: var(--brand-600); margin-bottom: 30px; text-align:center; }
.review-card{
    background:#fff; padding:20px; margin-bottom:20px; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.08);
}
.review-header{ font-weight:bold; margin-bottom:10px; }
.stars{ color:#FFD700; margin-bottom:10px; }
.no-reviews{ text-align:center; color:#888; font-size:1.1rem; margin:20px 0; }
.btn-back{
    display:block; text-align:center; width:150px; margin:20px auto; padding:10px;
    background: var(--brand-600); color:#fff; border:none; border-radius:8px; text-decoration:none;
}
.btn-back:hover{ background: var(--brand-700); color:#fff; }
</style>
</head>
<body>
<div class="container">
    <h1>All Reviews for "<?= htmlspecialchars($service['name']) ?>"</h1>

    <?php if ($reviews->num_rows > 0): ?>
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-header"><?= htmlspecialchars($review['user_name']) ?> <small class="text-muted"><?= $review['created_at'] ?></small></div>
                <div class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
                <div class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-reviews">No reviews yet for this service.</div>
    <?php endif; ?>

    <a href="service.php" class="btn-back">Back to Service Page</a>
</div>
</body>
</html>
