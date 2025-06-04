<!-- conference.php -->
<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ems');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: loginht.php");
    exit();
}

$error_message = '';
$success_message = '';
$selected_items = [
    'lighting' => null,
    'tabledec' => null,
    'cardboard' => null,
    'venue' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_selection'])) {
    $lighting_id = isset($_POST['lighting']) ? intval($_POST['lighting']) : null;
    $tabledec_id = isset($_POST['tabledec']) ? intval($_POST['tabledec']) : null;
    $cardboard_id = isset($_POST['cardboard']) ? intval($_POST['cardboard']) : null;
    $venue_id = isset($_POST['venue']) ? intval($_POST['venue']) : null;
    $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : null;

    $selected_items = [
        'lighting' => $lighting_id,
        'tabledec' => $tabledec_id,
        'cardboard' => $cardboard_id,
        'venue' => $venue_id
    ];

    if (!$lighting_id || !$tabledec_id || !$cardboard_id || !$venue_id || !$event_date) {
        $missing = [];
        if (!$lighting_id) $missing[] = "lighting";
        if (!$tabledec_id) $missing[] = "tabledec";
        if (!$cardboard_id) $missing[] = "cardboard";
        if (!$venue_id) $missing[] = "venue";
        if (!$event_date) $missing[] = "event date";
        
        $error_message = "Please select: " . implode(", ", $missing);
    } else {
        // Fetch prices
        $lighting_price = $conn->query("SELECT l_price FROM lighting WHERE l_id = $lighting_id")->fetch_assoc()['l_price'];
        $tabledec_price = $conn->query("SELECT t_price FROM tabledec WHERE t_id = $tabledec_id")->fetch_assoc()['t_price'];
        $cardboard_price = $conn->query("SELECT c_price FROM cardboard WHERE c_id = $cardboard_id")->fetch_assoc()['c_price'];
        $venue_price = $conn->query("SELECT v_price FROM venue WHERE v_id = $venue_id")->fetch_assoc()['v_price'];

        $total_price = $lighting_price + $tabledec_price + $cardboard_price + $venue_price;

        $stmt = $conn->prepare("INSERT INTO confselect (u_id, l_id, t_id, c_id, v_id, booking_date, event_date, total_price) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param("iiiiisd", $_SESSION['user_id'], $lighting_id, $tabledec_id, $cardboard_id, $venue_id, $event_date, $total_price);

        if ($stmt->execute()) {
            header("Location: bookings.php");
            exit();
        } else {
            $error_message = "Error saving your booking: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Decorations | EventSeek</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gaegu&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="ems.css">
    <script src="ems.js" defer></script>
    <style>
        .card {
            min-width: 280px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 0 15px;
        }

        .card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px;
        }

        .card h3 {
            color: #e0568d;
            margin: 10px 0 5px;
        }

        #selected-items {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .date-picker {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .date-picker label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #e0568d;
        }

        .date-picker input[type="date"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            width: 100%;
            max-width: 300px;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }     
    </style>
   
</head>
<body>
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

    <section class="hero">
        <h1>Let's Choose Decorations</h1>
    </section>

    <form method="post" action="conference.php">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="hidden" id="selected-lighting" name="lighting_id">
        <input type="hidden" id="selected-tabledec" name="tabledec_id">
        <input type="hidden" id="selected-cardboard" name="cardboard_id">
        <input type="hidden" id="selected-venue" name="venue_id">
        <input type="hidden" id="selected-event-date" name="event_date">
        
        <section class="section" id="lightings">
            <h2> Stage Lights</h2>
            <div class="scroll-container">
                <?php
                $result = $conn->query("SELECT * FROM lighting");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['l_image']); ?>" alt="lighting">
                    <div class="card-content">
                        <p><strong><?php echo htmlspecialchars($row['l_des']); ?></strong></p>
                        <p style="color:#5a5a5c;">Rs. <?php echo number_format($row['l_price'], 2); ?></p>
                        <label>
                            <input type="radio" name="lighting" value="<?php echo $row['l_id']; ?>"
                                data-description="<?php echo htmlspecialchars($row['l_des']); ?>"
                                data-price="<?php echo $row['l_price']; ?>">
                            Select
                        </label>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No stage lights available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="tabledecs">
            <h2> Table</h2>
            <div class="scroll-container">
                <?php
                $result = $conn->query("SELECT * FROM tabledec");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['t_image']); ?>" alt="tabledec">
                    <div class="card-content">
                        <p><strong><?php echo htmlspecialchars($row['t_des']); ?></strong></p>
                        <p style="color:#5a5a5c;">Rs. <?php echo number_format($row['t_price'], 2); ?></p>
                        <label>
                            <input type="radio" name="tabledec" value="<?php echo $row['t_id']; ?>"
                                data-description="<?php echo htmlspecialchars($row['t_des']); ?>"
                                data-price="<?php echo $row['t_price']; ?>">
                            Select
                        </label>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No tabledecs available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="cardboards">
            <h2> Cardboard</h2>
            <div class="scroll-container">
                <?php
                $result = $conn->query("SELECT * FROM cardboard");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['c_image']); ?>" alt="Cardboard">
                    <div class="card-content">
                        <p><strong><?php echo htmlspecialchars($row['c_des']); ?></strong></p>
                        <p style="color:#5a5a5c;">Rs. <?php echo number_format($row['c_price'], 2); ?></p>
                        <label>
                            <input type="radio" name="cardboard" value="<?php echo $row['c_id']; ?>"
                                data-description="<?php echo htmlspecialchars($row['c_des']); ?>"
                                data-price="<?php echo $row['c_price']; ?>">
                            Select
                        </label>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No cardboards available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="venues">
            <h2> Venue</h2>
            <div class="scroll-container">
                <?php
                $result = $conn->query("SELECT * FROM venue");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['v_image']); ?>" alt="Venue">
                    <div class="card-content">
                        <p><strong><?php echo htmlspecialchars($row['v_des']); ?></strong></p>
                        <p style="color:#5a5a5c;">Rs. <?php echo number_format($row['v_price'], 2); ?></p>
                        <label>
                            <input type="radio" name="venue" value="<?php echo $row['v_id']; ?>"
                                data-description="<?php echo htmlspecialchars($row['v_des']); ?>"
                                data-price="<?php echo $row['v_price']; ?>">
                            Select
                        </label>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No venues available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="confirmation">
            <h2>Your Selections:</h2>
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div id="selected-items">
                <p>No items selected yet</p>
            </div>

            <div class="date-picker">
                <label for="event_date">Select Event Date:</label>
                <input type="date" id="event_date" name= "event_date"vrequired min="<?php echo date('Y-m-d'); ?>">
            </div>

            <button type="submit" name="confirm_selection" class="btn-confirm">Confirm Booking</button>
        </section>
        
    </form>
    <footer class="footer">
        <div class="footer-column">
            <h3>About Us</h3>
            <p>Welcome to EventSeek! We specialize in creating unforgettable events, tailored to your needs.</p>
            <a href="https://www.instagram.com/eventseek/" target="_blank" style="text-decoration: none;">More About Us</a>
        </div>

        <div class="footer-column">
            <h3>Get In Touch</h3>
            <ul>
                <li>eventseek@gmail.com</li>
                 <li>9812345678</li>
            </ul>
            <a href="https://www.instagram.com/eventseek/" target="_blank" style="text-decoration: none;">More Ways to Get In Touch</a>
        </div>

        <div class="footer-column">
            <h3>Drop By</h3>
            <ul>
                <li>Old Baneshwor, Kathmandu</li>
                <li>-446600</li>
            </ul>
            <a href="https://maps.app.goo.gl/zQZrZFiRTHoZRPTy7" target="_blank" style="text-decoration: none;">Direction and Maps</a>
        </div>
    </footer>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateSelectedItems() {
            const selected = {
                lighting: document.querySelector('input[name="lighting"]:checked'),
                tabledec: document.querySelector('input[name="tabledec"]:checked'),
                cardboard: document.querySelector('input[name="cardboard"]:checked'),
                venue: document.querySelector('input[name="venue"]:checked'),
                date: document.getElementById('event_date').value
            };

            let html = '<ul>';
            let allSelected = true;
            
            if (selected.lighting) {
                html += `<li>lighting: ${selected.lighting.dataset.description} (Rs. ${selected.lighting.dataset.price})</li>`;
            } else {
                allSelected = false;
            }
            
            if (selected.tabledec) {
                html += `<li>tabledec: ${selected.tabledec.dataset.description} (Rs. ${selected.tabledec.dataset.price})</li>`;
            } else {
                allSelected = false;
            }
            
            if (selected.cardboard) {
                html += `<li>Cardboard: ${selected.cardboard.dataset.description} (Rs. ${selected.cardboard.dataset.price})</li>`;
            } else {
                allSelected = false;
            }
            
            if (selected.venue) {
                html += `<li>Venue: ${selected.venue.dataset.description} (Rs. ${selected.venue.dataset.price})</li>`;
            } else {
                allSelected = false;
            }
            
            if (selected.date) {
                html += `<li>Event Date: ${selected.date}</li>`;
            } else {
                allSelected = false;
            }
            
            html += '</ul>';
            
            if (!allSelected) {
                html += '<p style="color:red;">Please select all items and a date</p>';
            }
            
            document.getElementById('selected-items').innerHTML = html;
        }

        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', updateSelectedItems);
        });
        
        document.getElementById('event_date').addEventListener('change', updateSelectedItems);
        updateSelectedItems();

        <?php if ($error_message): ?>
            const missing = "<?php echo addslashes($error_message); ?>".replace('Please select: ', '').split(', ');
            missing.forEach(field => {
                if (field === 'event date') {
                    document.getElementById('event_date').style.border = '2px solid red';
                } else {
                    const radios = document.querySelectorAll(`input[name="${field}"]`);
                    radios.forEach(radio => {
                        radio.closest('.card').style.border = '2px solid red';
                    });
                }
            });
        <?php endif; ?>
    });
</script>
</body>
</html>
</body>
</html>
