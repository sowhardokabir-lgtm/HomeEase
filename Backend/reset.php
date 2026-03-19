<?php
require 'db.php';
$message = "";
$status = "";

// Capture email from URL if redirected from forgot.php
$email = $_GET['email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $status = "danger";
        $message = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $status = "danger";
        $message = "Passwords do not match!";
    } else {
        // Hash the new password for security
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            $status = "success";
            $message = "Password updated successfully! <a href='login.php'>Login here</a>";
        } else {
            $status = "danger";
            $message = "Error updating password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – HomeEase</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --sky-light: #e0f2fe; --sky-medium: #bae6fd; --sky-bright: #0ea5e9; --sky-dark: #0369a1; --white: #ffffff; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(at 100% 100%, hsla(199,89%,92%,1) 0, transparent 50%), linear-gradient(180deg, var(--sky-light) 0%, var(--white) 100%); min-height: 100vh; display: flex; align-items: center; position: relative; }
        .auth-card { border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 25px; background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(20px); box-shadow: 0 20px 40px rgba(0,0,0,0.06); padding: 2.5rem; }
        .btn-sky { background: var(--sky-bright); color: white; font-weight: 700; border-radius: 12px; padding: 12px; border: none; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #cbd5e1; background: rgba(255, 255, 255, 0.9); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card auth-card">
                    <h3 class="fw-800 text-center mb-2">Set New Password</h3>
                    <p class="text-muted text-center mb-4 small">Update your account security for <strong><?php echo htmlspecialchars($email); ?></strong></p>
                    
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $status; ?> small"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form action="reset.php" method="POST">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-600">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-600">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                        </div>
                        <button type="submit" class="btn btn-sky w-100">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>