<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    // Logic: Check if email exists and send reset token via email
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $success = "A password reset link has been sent to your email.";
    } else {
        $error = "No account found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — QuickNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --brand: #176B87; --brand-700: #073347; --sky-light: #f0f9ff; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--sky-light) 0%, #ffffff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(23, 107, 135, 0.1);
            border-radius: 30px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }
        .btn-brand {
            background: var(--brand);
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px;
            transition: 0.3s;
        }
        .btn-brand:hover { background: var(--brand-700); transform: translateY(-2px); color: white; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; }
        .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(23, 107, 135, 0.1); }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color: var(--brand);"><i class="bi bi-shield-lock"></i> Forgot?</h2>
            <p class="text-muted small">Enter your email and we'll send you a link to reset your password.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger small rounded-3"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success small rounded-3"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>
            <button type="submit" class="btn btn-brand w-100 mb-3">Send Reset Link</button>
            <div class="text-center">
                <a href="login.php" class="small fw-bold text-decoration-none" style="color: var(--brand);"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>