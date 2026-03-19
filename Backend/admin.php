<?php
session_start();
require 'db.php';

// Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: admin.php");
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel – HomeEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sky-bright: #0ea5e9; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(at 100% 100%, #e0f2fe 0%, #ffffff 100%); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; }
        .bg-active { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4 shadow-sm border-0" style="border-radius:20px;">
            <h4 class="fw-bold mb-4">Manage Registered Users</h4>
            <table class="table align-middle">
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><span class="status-badge bg-active"><?php echo $row['status']; ?></span></td>
                        <td>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>