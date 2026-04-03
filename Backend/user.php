<?php
session_start();
include "db.php";
// --- Admin check ---

if (!isset($_SESSION['user_role']) || strtolower(trim($_SESSION['user_role'])) !== 'admin') {
  echo "<script>alert('Access denied. Admins only.'); window.location='home.php';</script>";
  exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$user_name = '';
if ($user_id) {
    $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    if ($user) $user_name = $user['name'];
}

// --- Handle delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    if ($delete_id === $_SESSION['user_id']) {
        echo "<script>alert('You cannot delete yourself'); window.location='user.php';</script>";
        exit();
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('User deleted'); window.location='user.php';</script>";
    exit();
}

// --- Handle edit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = (int)$_POST['edit_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $edit_id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('User updated'); window.location='user.php';</script>";
    exit();
}

// --- Fetch all users ---
$result = $conn->query("SELECT id, name, email, role FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Manage Users - QuickNest Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
<style>
:root{ --brand:#176B87; --brand-600:#0A4C68; --brand-700:#073347; --bg:#f6f8fc; --text:#0f172a; }
body{ background:var(--bg); color:var(--text); }
.navbar{ background: linear-gradient(90deg, var(--brand), var(--brand-700)); }
.navbar .nav-link{ color:#fff; } .navbar .nav-link.active{ font-weight:600; }
.card{ border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 4px 10px rgba(2,6,23,.06); }
.btn-brand{ background:var(--brand); border-color:var(--brand); color:#fff; }
.btn-brand:hover{ background:var(--brand-600); border-color:var(--brand-600); }
.footer{ background: linear-gradient(90deg, var(--brand-700), var(--brand)); color:#fff; }
</style>
</head>
<body>

 <!-- ✅ Navbar -->
 <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="home.php"><i class="bi bi-stars me-1"></i> QuickNest</a>
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
  <h3 class="mb-3">Manage Users</h3>
  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th><th>Name</th><th>Email</th><th>Role</th><th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($user = $result->fetch_assoc()): ?>
          <tr>
            <td>#<?= (int)$user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
            <td class="text-center">
              <!-- Edit button -->
              <button class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#editModal<?= $user['id'] ?>">
                <i class="bi bi-pencil"></i> Edit
              </button>
              <!-- Delete button -->
              <?php if ($user['id'] !== $_SESSION['user_id']): ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                <input type="hidden" name="delete_id" value="<?= (int)$user['id'] ?>">
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <!-- Edit Modal -->
          <div class="modal fade" id="editModal<?= $user['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <form method="POST" class="modal-content">
                <input type="hidden" name="edit_id" value="<?= (int)$user['id'] ?>">
                <div class="modal-header">
                  <h5 class="modal-title">Edit User #<?= (int)$user['id'] ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                      <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
                      <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-brand">Save Changes</button>
                </div>
              </form>
            </div>
          </div>

        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<footer class="footer text-center p-3"><p class="mb-0">© 2025 QuickNest • All rights reserved</p></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
