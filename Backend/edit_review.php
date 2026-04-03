<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_GET['service_id'] ?? null;

if (!$service_id) {
    echo "Service not specified.";
    exit;
}

// Fetch service info
$service = $conn->query("SELECT * FROM services WHERE id = $service_id")->fetch_assoc();
if (!$service) {
    echo "Service not found.";
    exit;
}

// Fetch user's review (if exists)
$review = $conn->query("SELECT * FROM reviews WHERE service_id = $service_id AND user_id = $user_id")->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        if ($review) {
            $conn->query("DELETE FROM reviews WHERE id = {$review['id']}");
        }
        header("Location: service.php");
        exit;
    } else {
        $rating = intval($_POST['rating']);
        $comment = $conn->real_escape_string($_POST['comment']);

        if ($review) {
            $conn->query("UPDATE reviews SET rating = $rating, comment = '$comment', created_at = NOW() WHERE id = {$review['id']}");
        } else {
            $conn->query("INSERT INTO reviews (service_id, user_id, rating, comment) VALUES ($service_id, $user_id, $rating, '$comment')");
        }
        header("Location: service.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/x-icon" href="image/favicon.png">
<meta charset="UTF-8">
<title>Edit Review - <?= htmlspecialchars($service['name']) ?></title>
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
body{
  background: var(--bg); 
  color: var(--text);
}
.container{
  max-width: 600px;
  margin-top: 50px;
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
h2{
  color: var(--brand-600);
  margin-bottom: 25px;
  text-align: center;
}
.stars{
  display:flex;
  gap: 5px;
  font-size: 1.8rem;
  cursor: pointer;
  margin-bottom: 15px;
}
.stars i{
  color: #ccc;
  transition: color 0.2s;
}
.stars i.selected,
.stars i.hover{
  color: #FFD700;
}
textarea{
  resize: none;
}
button, .btn-back{
  border-radius: 8px;
}
.btn-back{
  background: var(--brand-600);
  color: #fff;
  border:none;
}
.btn-back:hover{
  background: var(--brand-700);
  color:#fff;
}
</style>
</head>
<body>
<div class="container">
    <h2><?= $review ? "Edit" : "Add" ?> Review for "<?= htmlspecialchars($service['name']) ?>"</h2>
    <form method="POST" id="reviewForm">
        <div class="mb-4">
            <label class="form-label">Your Rating:</label>
            <div class="stars" id="starRating">
                <?php 
                $currentRating = $review['rating'] ?? 0;
                for ($i = 1; $i <= 5; $i++): 
                    $class = ($i <= $currentRating) ? "fas selected" : "far";
                ?>
                    <i class="<?= $class ?> fa-star" data-value="<?= $i ?>"></i>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="<?= $currentRating ?>" required>
        </div>
        <div class="mb-4">
            <label for="comment" class="form-label">Your Comment:</label>
            <textarea name="comment" id="comment" class="form-control" rows="4" required><?= $review['comment'] ?? '' ?></textarea>
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary"><?= $review ? "Update Review" : "Add Review" ?></button>
            <?php if ($review): ?>
                <button type="submit" name="delete" class="btn btn-danger">Delete Review</button>
            <?php endif; ?>
            <a href="service.php" class="btn btn-back">Back</a>
        </div>
    </form>
</div>

<script>
// Star rating interaction
const stars = document.querySelectorAll("#starRating i");
const ratingInput = document.getElementById("ratingInput");

stars.forEach(star => {
    star.addEventListener("mouseover", () => highlightStars(star.dataset.value));
    star.addEventListener("mouseout", () => highlightStars(ratingInput.value));
    star.addEventListener("click", () => {
        ratingInput.value = star.dataset.value;
        highlightStars(star.dataset.value);
    });
});

function highlightStars(rating){
    stars.forEach(star => {
        star.classList.remove("selected","hover","fas","far");
        if(star.dataset.value <= rating){
            star.classList.add("fas","selected");
        } else {
            star.classList.add("far");
        }
    });
}
</script>

</body>
</html>
