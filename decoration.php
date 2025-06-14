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
    'flower' => null,
    'entrance' => null,
    'cardboard' => null,
    'venue' => null
];

$selected_date = isset($_POST['event_date']) ? $_POST['event_date'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_selection'])) {
    $flower_id = isset($_POST['flower']) ? intval($_POST['flower']) : null;
    $entrance_id = isset($_POST['entrance']) ? intval($_POST['entrance']) : null;
    $cardboard_id = isset($_POST['cardboard']) ? intval($_POST['cardboard']) : null;
    $venue_id = isset($_POST['venue']) ? intval($_POST['venue']) : null;
    $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : null;

    $selected_items = [
        'flower' => $flower_id,
        'entrance' => $entrance_id,
        'cardboard' => $cardboard_id,
        'venue' => $venue_id
    ];

    if (!$flower_id || !$entrance_id || !$cardboard_id || !$venue_id || !$event_date) {
        $missing = [];
        if (!$flower_id) $missing[] = "flower";
        if (!$entrance_id) $missing[] = "entrance";
        if (!$cardboard_id) $missing[] = "cardboard";
        if (!$venue_id) $missing[] = "venue";
        if (!$event_date) $missing[] = "event date";
        
        $error_message = "Please select: " . implode(", ", $missing);
    } else {
        // Check venue availability
        $venue_check_sql = "SELECT v_id FROM (
            SELECT v_id, event_date FROM userselect WHERE event_date = ? AND v_id = ?
            UNION ALL
            SELECT v_id, event_date FROM artselect WHERE event_date = ? AND v_id = ?
            UNION ALL
            SELECT v_id, event_date FROM confselect WHERE event_date = ? AND v_id = ?
        ) AS all_bookings";

        $stmt_check = $conn->prepare($venue_check_sql);
        $stmt_check->bind_param("sisisi", $event_date, $venue_id, $event_date, $venue_id, $event_date, $venue_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $error_message = "This venue is already booked for your selected date. Please choose another date or venue.";
        } else {
            $flower_price = $conn->query("SELECT f_price FROM flower WHERE f_id = $flower_id")->fetch_assoc()['f_price'];
            $entrance_price = $conn->query("SELECT e_price FROM entrance WHERE e_id = $entrance_id")->fetch_assoc()['e_price'];
            $cardboard_price = $conn->query("SELECT c_price FROM cardboard WHERE c_id = $cardboard_id")->fetch_assoc()['c_price'];
            $venue_price = $conn->query("SELECT v_price FROM venue WHERE v_id = $venue_id")->fetch_assoc()['v_price'];

            $total_price = $flower_price + $entrance_price + $cardboard_price + $venue_price;

            $stmt = $conn->prepare("INSERT INTO userselect (u_id, f_id, e_id, c_id, v_id, booking_date, event_date, total_price) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
            $stmt->bind_param("iiiiisd", $_SESSION['user_id'], $flower_id, $entrance_id, $cardboard_id, $venue_id, $event_date, $total_price);

            if ($stmt->execute()) {
                $_SESSION['booking_success'] = true;
                header("Location: bookings.php");
                exit();
            } else {
                $error_message = "Error saving your booking: " . $conn->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Decorations | EventSeek</title>
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
            transition: all 0.3s ease;
        }

        .card.unavailable {
            opacity: 0.6;
            border: 2px solid #ffcccc;
            position: relative;
        }

        .card.unavailable::after {
            content: "Booked";
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4757;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px;
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

        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffebee;
            border-radius: 4px;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 4px;
        }

        .scroll-container {
            display: flex;
            overflow-x: auto;
            padding: 15px 0;
            gap: 15px;
        }

        .btn-confirm {
            background: #e0568d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s;
        }

        .btn-confirm:hover {
            background: #c04a77;
            transform: translateY(-2px);
        }

        .message-container {
            display: none;
            margin: 20px 0;
        }
        
        .visible {
            display: block;
        }
        
        #confirmation {
            scroll-margin-top: 100px;
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
    <form method="post" action="decoration.php#confirmation" id="booking-form">
        <section class="section" id="confirmation">
        <div class="date-picker">
            <label for="event_date">Select Event Date:</label>
            <input type="date" id="event_date" name="event_date" required 
                   min="<?php echo date('Y-m-d'); ?>" 
                   value="<?php echo htmlspecialchars($selected_date); ?>">
        </div> 
        </section>
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
        
        <div id="message-container" class="message-container <?php echo (!empty($error_message) || (!empty($success_message))) ? 'visible' : ''; ?>">
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
        </div>

        <section class="section" id="flowers">
            <h2>Flower Arrangements</h2>
            <div class="scroll-container">
                <?php
                $result = $conn->query("SELECT * FROM flower");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['f_image']); ?>" alt="Flower Arrangement">
                    <div class="card-content">
                        <p><strong><?php echo htmlspecialchars($row['f_des']); ?></strong></p>
                        <p style="color:#5a5a5c;">Rs. <?php echo number_format($row['f_price'], 2); ?></p>
                        <label>
                            <input type="radio" name="flower" value="<?php echo $row['f_id']; ?>"
                                data-description="<?php echo htmlspecialchars($row['f_des']); ?>"
                                data-price="<?php echo $row['f_price']; ?>"
                                <?php echo ($selected_items['flower'] == $row['f_id']) ? 'checked' : ''; ?>>
                            Select
                        </label>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No flower arrangements available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="entrances">
            <h2>Entrance Decorations</h2>
            <div class="scroll-container">
                <?php
                $result = $conn->query("SELECT * FROM entrance");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['e_image']); ?>" alt="Entrance Decoration">
                    <div class="card-content">
                        <p><strong><?php echo htmlspecialchars($row['e_des']); ?></strong></p>
                        <p style="color:#5a5a5c;">Rs. <?php echo number_format($row['e_price'], 2); ?></p>
                        <label>
                            <input type="radio" name="entrance" value="<?php echo $row['e_id']; ?>"
                                data-description="<?php echo htmlspecialchars($row['e_des']); ?>"
                                data-price="<?php echo $row['e_price']; ?>"
                                <?php echo ($selected_items['entrance'] == $row['e_id']) ? 'checked' : ''; ?>>
                            Select
                        </label>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No entrance decorations available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="cardboards">
            <h2>Display Cardboards</h2>
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
                                data-price="<?php echo $row['c_price']; ?>"
                                <?php echo ($selected_items['cardboard'] == $row['c_id']) ? 'checked' : ''; ?>>
                            Select
                        </label>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No cardboard displays available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="venues">
            <h2>Venue Selection</h2>
            <div class="scroll-container">
                <?php
                $result = $conn->query("SELECT * FROM venue");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        // Check availability for each venue
                        $is_available = true;
                        if (!empty($selected_date)) {
                            $check_sql = "SELECT v_id FROM (
                                SELECT v_id, event_date FROM userselect WHERE event_date = ? AND v_id = ?
                                UNION ALL
                                SELECT v_id, event_date FROM artselect WHERE event_date = ? AND v_id = ?
                                UNION ALL
                                SELECT v_id, event_date FROM confselect WHERE event_date = ? AND v_id = ?
                            ) AS all_bookings";
                            
                            $stmt_check = $conn->prepare($check_sql);
                            $stmt_check->bind_param("sisisi", $selected_date, $row['v_id'], $selected_date, $row['v_id'], $selected_date, $row['v_id']);
                            $stmt_check->execute();
                            $result_check = $stmt_check->get_result();
                            $is_available = $result_check->num_rows === 0;
                            $stmt_check->close();
                        }
                ?>
                <div class="card <?php echo !$is_available ? 'unavailable' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($row['v_image']); ?>" alt="Venue">
                    <div class="card-content">
                        <p><strong><?php echo htmlspecialchars($row['v_des']); ?></strong></p>
                        <p style="color:#5a5a5c;">Rs. <?php echo number_format($row['v_price'], 2); ?></p>
                        <?php if ($is_available): ?>
                            <label>
                                <input type="radio" name="venue" value="<?php echo $row['v_id']; ?>"
                                    data-description="<?php echo htmlspecialchars($row['v_des']); ?>"
                                    data-price="<?php echo $row['v_price']; ?>"
                                    <?php echo ($selected_items['venue'] == $row['v_id']) ? 'checked' : ''; ?>>
                                Select
                            </label>
                        <?php else: ?>
                            <p style="color:red; font-size:0.9em;">Unavailable on selected date</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No venues available</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="confirmation">
            <h2>Your Selections</h2>
            <div id="selected-items">
                <p>No items selected yet</p>
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
        // Show messages if they exist
        const messageContainer = document.getElementById('message-container');
        if (messageContainer.querySelector('.error-message') || messageContainer.querySelector('.success-message')) {
            messageContainer.classList.add('visible');
            
            // Scroll to messages if they exist
            setTimeout(() => {
                messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }

        // Update selected items display
        function updateSelectedItems() {
            const selected = {
                flower: document.querySelector('input[name="flower"]:checked'),
                entrance: document.querySelector('input[name="entrance"]:checked'),
                cardboard: document.querySelector('input[name="cardboard"]:checked'),
                venue: document.querySelector('input[name="venue"]:checked'),
                date: document.getElementById('event_date').value
            };

            let html = '<ul>';
            let allSelected = true;
            
            if (selected.flower) {
                html += `<li>Flower Arrangement: ${selected.flower.dataset.description} (Rs. ${selected.flower.dataset.price})</li>`;
            } else {
                allSelected = false;
            }
            
            if (selected.entrance) {
                html += `<li>Entrance Decoration: ${selected.entrance.dataset.description} (Rs. ${selected.entrance.dataset.price})</li>`;
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

        // Update when any radio button changes
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', updateSelectedItems);
        });
        
        // Update when date changes
        document.getElementById('event_date').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Highlight missing selections if there was an error
        <?php if (!empty($error_message)): ?>
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
        
        // Initialize selected items display
        updateSelectedItems();
    });
    </script>
</body>
</html>
<?php
$conn->close();
?>