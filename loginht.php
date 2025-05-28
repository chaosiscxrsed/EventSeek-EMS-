<!-- loginht.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventSeek Login</title>
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
        <h2>Login to EventSeek</h2>
        <div class="form">
            <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo "<p style='color: red; font-weight: bold;'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }
            ?>
            
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>

                <div class="rem">
                    <input type="checkbox" name="remember" id="rem">
                    <label for="rem">Remember Me</label>
                </div>
                <div class="sub">
                    <button type="submit">Login</button>
                </div>
                <p><a href="forgotps.php">Forgot Password?</a></p>
                <p>Don't have an account? <a href="signup.html">Sign Up</a></p> 
            </form>
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
