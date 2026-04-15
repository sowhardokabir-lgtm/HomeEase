<?php
session_start();
include "db.php";

/* ACCESS CONTROL */
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* ADMIN ACTIONS */
$message = "";

if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    if ($id != $_SESSION["user_id"]) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "User deleted!";
    } else {
        $message = "You cannot delete yourself!";
    }
}

if (isset($_POST['update_booking_status'])) {
    $id = (int)$_POST['booking_id'];
    $status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $message = "Booking updated!";
}

if (isset($_POST['make_offer'])) {
    $id = (int)$_POST['req_id'];
    $offer = (int)$_POST['offer_points'];
    $stmt = $conn->prepare("UPDATE recycling_requests SET status='Offer Pending', admin_offer=? WHERE id=?");
    $stmt->bind_param("ii", $offer, $id);
    $stmt->execute();
    $message = "Offer sent!";
}

if (isset($_GET['reject_recycle'])) {
    $id = (int)$_GET['reject_recycle'];
    $stmt = $conn->prepare("UPDATE recycling_requests SET status='Rejected' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "Request rejected!";
}

/* FETCH DATA */
$user_count = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$booking_count = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];
$service_count = $conn->query("SELECT COUNT(*) as c FROM services")->fetch_assoc()['c'];
$recycle_count = $conn->query("SELECT COUNT(*) as c FROM recycling_requests WHERE status='Pending'")->fetch_assoc()['c'];

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

$bookings = $conn->query("
    SELECT b.*, u.name as user_name, s.name as service_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    ORDER BY b.id DESC
");

$recycles = $conn->query("
    SELECT r.*, u.name, u.email
    FROM recycling_requests r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --brand: #176B87;
            --brand-700: #073347;
            --sky: #f0f9ff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(180deg, var(--sky), #fff);
        }

        .navbar {
            background: var(--brand-700);
        }

        .navbar-brand {
            color: #fff !important;
            font-weight: 800;
        }

        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .table {
            border-radius: 15px;
            overflow: hidden;
        }

        .btn-brand {
            background: var(--brand);
            color: #fff;
            border-radius: 10px;
        }

        .btn-brand:hover {
            background: var(--brand-700);
            color: #fff;
        }

        h4 {
            margin-top: 40px;
            margin-bottom: 15px;
            font-weight: 700;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg p-3">
    <div class="container">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <a href="logout.php" class="btn btn-light">Logout</a>
    </div>
</nav>

<div class="container mt-4">

    <h2 class="fw-bold mb-4">Admin Dashboard</h2>

    <?php if($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="row text-center g-3 mb-4">
        <div class="col-md-3"><div class="card p-3">Users<br><h4><?= $user_count ?></h4></div></div>
        <div class="col-md-3"><div class="card p-3">Bookings<br><h4><?= $booking_count ?></h4></div></div>
        <div class="col-md-3"><div class="card p-3">Services<br><h4><?= $service_count ?></h4></div></div>
        <div class="col-md-3"><div class="card p-3">Recycle<br><h4><?= $recycle_count ?></h4></div></div>
    </div>

    <!-- BOOKINGS -->
    <h4>Bookings</h4>
    <div class="card p-3">
        <table class="table">
            <tr>
                <th>ID</th><th>User</th><th>Service</th><th>Date</th><th>Status</th><th>Update</th>
            </tr>
            <?php while($b = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td><?= $b['user_name'] ?></td>
                <td><?= $b['service_name'] ?></td>
                <td><?= $b['date'] ?></td>
                <td><span class="badge bg-primary"><?= $b['status'] ?></span></td>
                <td>
                    <form method="POST" class="d-flex gap-1">
                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                        <select name="new_status" class="form-select form-select-sm">
                            <option>Pending</option>
                            <option>Confirmed</option>
                            <option>Completed</option>
                            <option>Cancelled</option>
                        </select>
                        <button class="btn btn-brand btn-sm" name="update_booking_status">✔</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- RECYCLE -->
    <h4>Recycle Requests</h4>
    <div class="card p-3">
        <table class="table">
            <tr>
                <th>User</th><th>Item</th><th>Status</th><th>Offer</th><th>Action</th>
            </tr>
            <?php while($r = $recycles->fetch_assoc()): ?>
            <tr>
                <td><?= $r['name'] ?></td>
                <td><?= $r['item_type'] ?></td>
                <td><span class="badge bg-warning text-dark"><?= $r['status'] ?></span></td>
                <td><?= $r['admin_offer'] ?></td>
                <td>
                    <?php if($r['status'] == 'Pending'): ?>
                        <form method="POST" class="d-flex gap-1">
                            <input type="hidden" name="req_id" value="<?= $r['id'] ?>">
                            <input type="number" name="offer_points" class="form-control form-control-sm" placeholder="Pts" required>
                            <button class="btn btn-success btn-sm" name="make_offer">Offer</button>
                        </form>
                        <a href="?reject_recycle=<?= $r['id'] ?>" class="btn btn-danger btn-sm mt-1">Reject</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- USERS -->
    <h4>Users</h4>
    <div class="card p-3 mb-5">
        <table class="table">
            <tr>
                <th>Name</th><th>Email</th><th>Role</th><th>Delete</th>
            </tr>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['name'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><span class="badge bg-secondary"><?= $u['role'] ?></span></td>
                <td>
                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete_user=<?= $u['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

</body>
</html>