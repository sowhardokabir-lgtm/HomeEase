<?php
require 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($pass !== $confirm_pass) {
        $message = "Passwords do not match!";
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_pass);
        
        if ($stmt->execute()) {
            header("Location: login.php");
        } else {
            $message = "Email already exists!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register – HomeEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sky-bright: #0ea5e9; --navy: #0f172a; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(at 0% 0%, hsla(199,89%,82%,1) 0, transparent 50%); min-height: 100vh; display: flex; align-items: center; }
        .auth-card { border-radius: 25px; background: white; padding: 2.5rem; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .btn-sky { background: var(--sky-bright); color: white; border-radius: 12px; padding: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card auth-card">
                    <h3 class="fw-800 text-center mb-4">Register</h3>
                    <?php if($message): ?>
                        <div class="alert alert-danger"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sky w-100">Register Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>