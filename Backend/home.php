<?php
/* HOME-EASE BACKEND LOGIC 
   This section handles navigation and session checks
*/
session_start();
require_once 'db.php'; // Ensure your database connection file exists

// Logic for button clicks if they are changed to form submits or specific GET requests
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'logout') {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// Check if user is logged in to change "Get Started" behavior
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : "Guest";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeEase — Smart Home Management</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --sky-light: #f0f9ff;
            --sky-medium: #e0f2fe;
            --sky-bright: #0ea5e9;
            --sky-dark: #0369a1;
            --navy: #0f172a;
            --slate: #475569;
            --white: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--white);
            color: var(--navy);
            margin: 0;
            overflow-x: hidden;
        }

        /* ===== Navbar Glassmorphism ===== */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(14, 165, 233, 0.1);
            padding: 18px 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            color: var(--sky-bright) !important;
        }

        .nav-link {
            color: var(--navy) !important;
            font-weight: 600;
            margin: 0 12px;
            transition: color 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--sky-bright) !important;
        }

        .btn-get-started {
            background: var(--sky-bright);
            color: white !important;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
            transition: all 0.3s ease;
        }

        .btn-get-started:hover {
            background: var(--sky-dark);
            transform: translateY(-2px);
        }

        /* ===== Hero Section (Split Layout) ===== */
        .hero {
            padding: 11rem 0 7rem;
            background: linear-gradient(180deg, var(--sky-light) 0%, var(--white) 100%);
            position: relative;
        }

        .hero h1 {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero h1 span {
            color: var(--sky-bright);
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--slate);
            margin-bottom: 2.5rem;
            max-width: 550px;
        }

        .hero-img-box img {
            width: 100%;
            border-radius: 40px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.1);
        }

        /* ===== Service Cards ===== */
        .service-card {
            border: none;
            border-radius: 25px;
            background: #ffffff;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px rgba(14, 165, 233, 0.15);
        }

        .card-img-top {
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .service-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .btn-outline-custom {
            border: 2px solid var(--sky-bright);
            color: var(--sky-bright);
            font-weight: 700;
            border-radius: 12px;
            transition: 0.3s;
        }

        .btn-outline-custom:hover {
            background: var(--sky-bright);
            color: white;
        }

        /* ===== Feature & CTA Sections ===== */
        .feature-icon {
            font-size: 2.5rem;
            color: var(--sky-bright);
            margin-bottom: 1rem;
        }

        .cta-container {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            border-radius: 40px;
            padding: 5rem 2rem;
            color: white;
            text-align: center;
            margin-bottom: 4rem;
        }

        /* ===== Professional Footer ===== */
        footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 4rem 0 2rem;
        }

        .footer-link {
            color: var(--slate);
            text-decoration: none;
            transition: 0.3s;
        }

        .footer-link:hover {
            color: var(--sky-bright);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-house-heart-fill"></i> HomeEase
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="services.php">Book Service</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item ms-lg-3">
                    <?php if($isLoggedIn): ?>
                        <a class="btn btn-get-started" href="dashboard.php">Hi, <?php echo htmlspecialchars($userName); ?></a>
                    <?php else: ?>
                        <a class="btn btn-get-started" href="registration.php">Get Started</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 shadow-sm">✨ Premium Home Solutions</span>
                <h1>Smart Service <br><span>Management System.</span></h1>
                <p>Book trusted professionals for home services — fast, easy, and reliable. Experience a cleaner, safer, and better home today.</p>
                <div class="d-flex gap-3">
                    <a href="services.php" class="btn btn-get-started btn-lg px-4">Explore Services</a>
                    <a href="admin.php" class="btn btn-outline-dark btn-lg px-4" style="border-radius:12px;">Admin Portal</a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0">
                <div class="hero-img-box">
                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80" alt="Modern Interior">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold display-5">Our Popular Services</h2>
            <p class="text-muted">High-quality solutions tailored for your household needs.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1200&q=80" class="card-img-top" alt="Cleaning">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold">Cleaning Service</h5>
                        <p class="text-muted small">Professional cleaning for homes and offices with care.</p>
                        <a href="booking.php?service=cleaning" class="btn btn-outline-custom w-100 mt-2">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT41E1u9zE01ocZ8QelCieFl11lgCb39ihs3A&s" class="card-img-top" alt="Pest Control">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold">Pest Control</h5>
                        <p class="text-muted small">Safe and effective pest elimination for your family.</p>
                        <a href="booking.php?service=pest" class="btn btn-outline-custom w-100 mt-2">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card service-card h-100">
                    <img src="https://www.homeshifting.com.bd/cloud/themes/moversV3/extra-images/services-grid-img8.jpg" class="card-img-top" alt="Shifting">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold">Home Shifting</h5>
                        <p class="text-muted small">Quick and stress-free relocation by our expert team.</p>
                        <a href="booking.php?service=shifting" class="btn btn-outline-custom w-100 mt-2">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: #f8fafc;">
    <div class="container text-center">
        <div class="row g-4">
            <div class="col-md-4">
                <i class="bi bi-shield-check feature-icon"></i>
                <h5 class="fw-bold">Verified Pros</h5>
                <p class="text-muted small">All service providers are background checked.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-lightning-charge feature-icon"></i>
                <h5 class="fw-bold">Instant Booking</h5>
                <p class="text-muted small">Get your service scheduled in under a minute.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-star feature-icon"></i>
                <h5 class="fw-bold">Quality Guaranteed</h5>
                <p class="text-muted small">We ensure 100% satisfaction on every task.</p>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="cta-container shadow-lg">
        <h2 class="fw-bold display-5 mb-3">Ready to simplify your life?</h2>
        <p class="opacity-75 mb-4 fs-5">Create an account and book your first service in minutes.</p>
        <a href="registration.php" class="btn btn-light btn-lg px-5 py-3 fw-bold text-primary rounded-pill shadow">Register Now</a>
    </div>
</div>

<footer>
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4">
                <h4 class="fw-bold text-primary mb-3">HomeEase</h4>
                <p class="text-muted small">Bringing smart solutions to every household. Manage your home services effortlessly with our modern platform.</p>
            </div>
            <div class="col-lg-2 ms-auto">
                <h6 class="fw-bold mb-3">Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="#" class="footer-link">About Us</a></li>
                    <li><a href="services.php" class="footer-link">Services</a></li>
                    <li><a href="#" class="footer-link">Reviews</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h6 class="fw-bold mb-3">Support</h6>
                <ul class="list-unstyled small">
                    <li><a href="#" class="footer-link">Contact</a></li>
                    <li><a href="#" class="footer-link">Privacy Policy</a></li>
                    <li><a href="#" class="footer-link">Terms</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6 class="fw-bold mb-3">Follow Us</h6>
                <div class="d-flex gap-3 fs-4">
                    <a href="#" class="text-primary"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-primary"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-primary"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>
        <hr class="my-4 opacity-10">
        <div class="text-center">
            <p class="text-muted small mb-0">© 2026 HomeEase Management • All Rights Reserved</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>