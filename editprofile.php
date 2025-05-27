<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$fullname = $_SESSION['fullname'] ?? '';
$email = $_SESSION['email'] ?? '';
$contact = $_SESSION['contact'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - EventSeek</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gaegu&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="ems.css">
    <script src="ems.js" defer></script>
    <style>
        .section a{
            transition: color 0.3s;
        }
        .section a:hover {
            color: #333;
            background-color: transparent;
        }
    </style>
</head>

<body>
    <div class="header-nav-container">
        <header>
            <div class="logo-heading">
                <a href="EMShome.html"><img src="logo.png" alt="EventSeek Logo" class="logo"></a>
                <h1><a href="EMShome.html" style=" color: #fcfdfd;">EventSeek</a></h1>
            </div>
        </header>
        
        <nav>
            <a href="#contact" id="contact-link">Contact</a>
            <a href="#about" id="about-link">About Us</a>
            <button id="open-dashboard" class="dashboard-btn">☰</button>
        </nav>
    </div>
    
    <div id="dashboard" class="dashboard">
        <button id="close-dashboard" class="close-btn">&times;</button>
        <ul>
            <li><a href="#profile">Profile</a></li>
            <li><a href="bookings.php">Event History</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </div>
    
    <div class="popup-overlay" id="popup-overlay-contact">
        <div class="popup-pane">
            <h3>Contact Us</h3>
            <p>Email: eventseek@gmail.com</p>
            <p>Phone: 9812345678</p>
            <button class="close-popup">Close</button>
        </div>
    </div>
    <div class="popup-overlay" id="popup-overlay-about">
        <div class="popup-pane">
            <h3>About Us</h3>
            <p>Welcome to EventSeek! We specialize in creating unforgettable events, tailored to your needs.</p>
            <p>Our mission is to turn your dreams into reality with seamless planning and execution.</p>
            <button class="close-popup">Close</button>
        </div>
    </div>

    <div class="section">
        <h2>Edit Your Profile</h2>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success-message"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-message"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>

        <form class="form" id="edit-profile-form" action="updateprofile.php" method="post">
            <label for="fullname">Name:</label>
            <input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($fullname) ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="contact">Contact Number:</label>
            <input type="text" name="contact" id="contact" value="<?= htmlspecialchars($contact) ?>" required>

            <button type="submit">Save Changes</button>
            <div class="sub" style="margin-top: 30px;">
            <a href="forgotps.php">Reset Password?</a>
            </div>
        </form>
        <div style="text-align: right; margin-top: 20px;">
            <p><a href="homepageht.php" style="
                display: inline-block;
                padding: 10px 20px;
                color: white;
                transition: color 0.3s;">
                Back to Homepage
            </a></p>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-column">
            <h3>About Us</h3>
            <p>Welcome to EventSeek! We specialize in creating unforgettable events, tailored to your needs.</p>
            <a href="https://www.instagram.com/eventseek/" target="_blank">More About Us</a> 
        </div>

        <div class="footer-column">
            <h3>Get In Touch</h3>
            <ul>
                <li>eventseek@gmail.com</li>
                <li>9812345678</li>
            </ul>
            <a href="https://www.instagram.com/eventseek/" target="_blank">More Ways to Get In Touch</a>
        </div>

        <div class="footer-column">
            <h3>Drop By</h3>
            <ul>
                <li>Old Baneshwor, Kathmandu</li>
                <li>-446600</li>
            </ul>
            <a href="https://maps.app.goo.gl/zQZrZFiRTHoZRPTy7" target="_blank">Direction and Maps</a>
        </div>
    </footer>
</body>
</html>
</body>
</html>
