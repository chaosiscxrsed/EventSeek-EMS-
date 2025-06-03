<!-- payment.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gaegu&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="ems.css">
    <script src="ems.js" defer></script>
    <style>
        .btn-back {
            background: #55d142 !important;
            color: white !important;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none !important;
        }
        .btn-back:hover {
            background: #4bb739;
            transform: translateY(-2px);
        }



        .info p{ 
            margin: 10px 0;
            color: Gray;;
        }
    </style>
</head>
<body>
     <?php
        session_start();
        if (!isset($_SESSION['user_name'])) {
            header("Location: loginht.php");
            exit();
        }
        $user_name = htmlspecialchars($_SESSION['user_name']);
    ?>
    <div class="header-nav-container">
        <header>
           <div class="logo-heading">
                <a href="homepageht.php">
                    <img src="logo.png" alt="EventSeek Logo" class="logo">
                </a>
                <h1><a href="homepageht.php" style="color: #fcfdfd">EventSeek</a></h1>
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

    <div class="section" id="signup">
        <h2>Pay via E-sewa</h2>
            <div class="form">
                <div class="card">
                <img src="qr.jpg" alt="QR Code">
                </div>
                <div class="info">
                <p>Scan the QR code to make your payment.</p>
                <p><strong>Note: Please mention your name, booking ID and any other info in the remarks section of the payment.</strong></p>
                <p><strong>20% of payment will not be refunded if cancelled.</strong></p>
                <p>For more inquiries, contact us at 9841234567 or email us at eventseek@gmail.com</p>
                </div>
                <a href="bookings.php" class="btn-back">
                 Go Back</a>
            </div>
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
