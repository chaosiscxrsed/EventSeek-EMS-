<!-- forgotps.php -->
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - EventSeek</title>
    <link rel="stylesheet" href="ems.css">
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
            <li><a href="EMShome.html">Home</a></li>
            <li><a href="loginht.php" target="_blank">Log In</a></li>
            <li><a href="signup.html" target="_blank">Sign Up</a></li>
        </ul>
    </div>
        
    <div class="popup-overlay" id="popup-overlay-contact">
        <div class="popup-pane">
        <h3>Contact Us</h3>
            <p>Email: livelyevents@gmail.com</p>
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
        <h2>Reset Your Password</h2>
        <div class="form">
            <?php
            if (isset($_SESSION['reset_error'])) {
                echo "<p class='error-message'>" . $_SESSION['reset_error'] . "</p>";
                unset($_SESSION['reset_error']);
            }
            if (isset($_SESSION['reset_success'])) {
                echo "<p class='success-message'>" . $_SESSION['reset_success'] . "</p>";
                unset($_SESSION['reset_success']);
            }
            ?>
            <form action="fps.php" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Reset Password</button>
            </form>
            <p><a href="loginht.php">Back to Login</a></p>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-column">
            <h3>About Us</h3>
            <p>Welcome to EventSeek! We specialize in creating unforgettable events, tailored to your needs.</p>
            <a href="https://www.instagram.com/livelyevents/" target="_blank">More About Us</a>
        </div>

        <div class="footer-column">
            <h3>Get In Touch</h3>
            <ul>
                <li>livelyevents@gmail.com</li>
                <li>9812345678</li>
            </ul>
            <a href="https://www.instagram.com/livelyevents/" target="_blank">More Ways to Get In Touch</a>
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

