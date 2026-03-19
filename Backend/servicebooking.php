<?php
session_start();
require 'db.php';

// Create bookings table
$conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    service_type VARCHAR(100),
    contact VARCHAR(20),
    booking_date DATE,
    booking_time TIME,
    address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $service = $_POST['service'];
    $contact = $_POST['contact'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_type, contact, booking_date, booking_time, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $service, $contact, $date, $time, $address);
    $stmt->execute();
    echo "<script>alert('Booking Confirmed!');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Service – HomeEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: radial-gradient(at 0% 0%, hsla(199,89%,82%,1) 0, transparent 50%); padding: 40px 0; }
        .booking-card { border-radius: 25px; background: white; padding: 3rem; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card booking-card">
                    <h2 class="text-center mb-4">Book a Professional Service</h2>
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Service</label>
                                <select name="service" class="form-select">
                                    <option>Home Cleaning</option>
                                    <option>AC Repair</option>
                                    <option>Plumbing</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" name="contact" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time</label>
                                <input type="time" name="time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>