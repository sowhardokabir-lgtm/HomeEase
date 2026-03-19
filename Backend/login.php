<?php
session_start();
require 'db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login – HomeEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sky-bright: #0ea5e9; }
        body { background: #f8fafc; min-height: 100vh; display: flex; align-items: center; }
        .auth-card { border-radius: 25px; background: white; padding: 2.5rem; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .btn-sky { background: var(--sky-bright); color: white; border-radius: 12px; padding: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card auth-card">
                    <h3 class="text-center mb-4">Welcome Back</h3>
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                            <div class="text-end">
                                <a href="forgot.php" class="text-decoration-none small">Forgot Password?</a>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sky w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>