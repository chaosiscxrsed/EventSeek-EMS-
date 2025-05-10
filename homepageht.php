<!-- homepageht.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventSeek Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gaegu&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="ems.css">
    <script src="ems.js" defer></script>
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
                <a href="EMShome.html" style="color: inherit;"><img src="logo.png" alt="EventSeek Logo" class="logo"></a>
                <h1><a href="EMShome.html" style= "color: #fcfdfd;">EventSeek</a></h1>
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

    <section class="hero">
        <h1>Welcome, <?php echo $user_name; ?>!</h1>
        <p>Let's Get Started</p>
    </section>

    <div class="section">
        <h2>Choose an Event</h2>
        <div class="container">
            <div class="card">
            <a href="decoration.php"><img src="weddd.jpg" alt="Wedding event" style="width:100%; height:200px; object-fit:cover;"></a>
                <div class="card-content">
                    <a href="decoration.php"><h3>Wedding</h3></a>
                    <div class="line"></div>
                </div>
            </div>
            <div class="card">
            <a href="artex.php"><img src="artt.jpeg" alt="Art exhibition" style="width:100%; height:200px; object-fit:cover;"></a>
                <div class="card-content">
                    <a href="decoration.php"><h3>Art Exhibition</h3></a>
                    <div class="line"></div>
                </div>
            </div>
            <div class="card">
            <a href="conference.php"><img src="conf.jpg" alt="Conference meeting" style="width:100%; height:200px; object-fit:cover;"></a>
                <div class="card-content">
                    <a href="conference.php"><h3>Conference</h3></a>
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
   
    <section id="profile" class="section">
        <h2>My Profile</h2>
        <p>Name: <?php echo $user_name; ?></p>
        <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        <button onclick="window.location.href='editprofile.php'">Edit Profile</button>
    </section>

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