<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $confirm_password = $_POST['confirm_password'];
    
    // In a real scenario, you'd verify a token here from the URL
    // For this example, we assume the user is validated
    if ($_POST['password'] === $confirm_password) {
        // Update logic: UPDATE users SET password = '$new_password' WHERE email = ...
        $success = "Password updated successfully!";
    } else {
        $error = "Passwords do not match.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — QuickNest</title>
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
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color: var(--brand);">Reset Password</h2>
            <p class="text-muted small">Please create a new, secure password.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger small rounded-3"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success small rounded-3">
                <?= $success ?> <br> <a href="login.php" class="alert-link">Login now</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-brand w-100 mb-3">Update Password</button>
        </form>
    </div>
</body>
</html>