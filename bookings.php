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
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name']);
$user_email = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : "";

$results_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;

$count_sql = "SELECT (
    SELECT COUNT(*) FROM userselect WHERE u_id = ?
) + (
    SELECT COUNT(*) FROM artselect WHERE u_id = ?
) + (
    SELECT COUNT(*) FROM confselect WHERE u_id = ?
) AS total";

$stmt = $conn->prepare($count_sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$count_result = $stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);
$sql = "(
    -- Wedding bookings
    SELECT 
        'wedding' AS event_type,
        ub.b_id AS booking_id,
        f.f_des AS item1, 
        e.e_des AS item2, 
        c.c_des AS item3, 
        v.v_des AS item4,
        f.f_price AS price1,
        e.e_price AS price2,
        c.c_price AS price3,
        v.v_price AS price4,
        ub.booking_date, 
        ub.event_date, 
        ub.status, 
        ub.total_price
    FROM userselect ub
    JOIN flower f ON ub.f_id = f.f_id
    JOIN entrance e ON ub.e_id = e.e_id
    JOIN cardboard c ON ub.c_id = c.c_id
    JOIN venue v ON ub.v_id = v.v_id
    WHERE ub.u_id = ?
)
UNION ALL
(
    -- Art exhibition bookings
    SELECT 
        'art' AS event_type,
        ab.selection_id AS booking_id,
        r.r_des AS item1, 
        p.p_des AS item2, 
        c.c_des AS item3, 
        v.v_des AS item4,
        r.r_price AS price1,
        p.p_price AS price2,
        c.c_price AS price3,
        v.v_price AS price4,
        ab.booking_date, 
        ab.event_date, 
        ab.status, 
        ab.total_price
    FROM artselect ab
    JOIN roomlight r ON ab.r_id = r.r_id
    JOIN prop p ON ab.p_id = p.p_id
    JOIN cardboard c ON ab.c_id = c.c_id
    JOIN venue v ON ab.v_id = v.v_id
    WHERE ab.u_id = ?
)
UNION ALL
(
    -- Conference bookings
    SELECT 
        'conference' AS event_type,
        cb.conf_id AS booking_id,
        l.l_des AS item1, 
        t.t_des AS item2, 
        c.c_des AS item3, 
        v.v_des AS item4,
        l.l_price AS price1,
        t.t_price AS price2,
        c.c_price AS price3,
        v.v_price AS price4,
        cb.booking_date, 
        cb.event_date, 
        cb.status, 
        cb.total_price
    FROM confselect cb
    JOIN lighting l ON cb.l_id = l.l_id
    JOIN tabledec t ON cb.t_id = t.t_id
    JOIN cardboard c ON cb.c_id = c.c_id
    JOIN venue v ON cb.v_id = v.v_id
    WHERE cb.u_id = ?
)
ORDER BY event_date DESC
LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $offset, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | EventSeek</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gaegu&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="ems.css">
    <script src="ems.js" defer></script>
    <style>
        .booking-container {
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }

        .booking-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            padding: 25px;
            transition: all 0.3s ease;
            border-left: 4px solid #e0568d;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .event-type {
            font-size: 0.8em;
            background: #e0568d;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .booking-status {
            text-transform: capitalize; 
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }

        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }

        .status-confirmed {
            background-color: #D4EDDA;
            color: #155724;
        }

        .status-cancelled {
            background-color: #F8D7DA;
            color: #721C24;
        }

        .status-ended {
            background-color: #E2E3E5;
            color: #383D41;
        }

        .days-remaining {
            font-size: 0.8em;
            font-weight: normal;
            color: #6c757d;
            margin-left: 8px;
        }

        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-group {
            display: flex;
            margin-bottom: 8px;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
            min-width: 120px;
        }

        .detail-value {
            color: #333;
        }

        .booking-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-cancel {
            font-family: 'Poppins', sans-serif;
            background: #ff4757;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background:rgb(205, 78, 97);
            transform: translateY(-2px);
        }

        .booking-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .booking-actions > * {
            flex: 1;
            min-width: fit-content;
            text-align: center;
        }

        .btn-receipt {
            background: #e0568d;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-receipt:hover {
            background: #c04a77;
            transform: translateY(-2px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .pagination a {
            color: #e0568d;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #e0568d;
            color: white;
            border: 1px solid #e0568d;
        }

        .pagination a:hover:not(.active) {
            background-color: #f1f1f1;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .rating {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .star {
            color: #ccc;
            cursor: pointer;
            font-size: 1.2em;
            transition: color 0.2s;
        }

        .star:hover, .star.active {
            color: #ffc107;
        }

        .btn-submit-rating {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-submit-rating:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .btn-submit-rating:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <div class="header-nav-container">
        <header>
            <div class="logo-heading">
                <a href="EMShome.html">
                    <img src="logo.png" alt="EventSeek Logo" class="logo">
                </a>
                <h1><a href="EMShome.html" style="color: #fcfdfd">EventSeek</a></h1>
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
        <h1>Your Bookings</h1>
    </section>

    <div class="booking-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                $event_date = new DateTime($row['event_date']);
                $today = new DateTime();
                $status = $row['status'];
                if ($status !== 'Cancelled' && $status !== 'cancelled') {
                    if ($event_date < $today) {
                        $status = 'Ended';
                    } elseif ($status === null) {
                        $status = 'Pending';
                    }
                }
                
                $status_class = 'status-' . strtolower($status);
                $days_remaining = ($status === 'Pending') ? $today->diff($event_date)->days : 0;
                switch($row['event_type']) {
                    case 'wedding':
                        $event_name = "Wedding";
                        $item_labels = ['Flower', 'Entrance', 'Cardboard', 'Venue'];
                        break;
                    case 'art':
                        $event_name = "Art Exhibition";
                        $item_labels = ['Room Lighting', 'Prop', 'Cardboard', 'Venue'];
                        break;
                    case 'conference':
                        $event_name = "Conference";
                        $item_labels = ['Lighting', 'Table Decoration', 'Cardboard', 'Venue'];
                        break;
                }
            ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div>
                            <h3><?php echo $event_name; ?> Booking #<?php echo htmlspecialchars($row['booking_id']); ?></h3>
                            <span class="event-type"><?php echo $event_name; ?></span>
                        </div>
                        <div class="booking-status <?php echo $status_class; ?>">
                            <?php echo $status; ?>
                            <?php if ($days_remaining > 0): ?>
                                <span class="days-remaining">(<?php echo $days_remaining; ?> days remaining)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-group">
                            <span class="detail-label">Booking Date:</span>
                            <span class="detail-value"><?php echo date("M j, Y", strtotime($row['booking_date'])); ?></span>
                        </div>
                        <div class="detail-group">
                            <span class="detail-label">Event Date:</span>
                            <span class="detail-value"><?php echo date("M j, Y", strtotime($row['event_date'])); ?></span>
                        </div>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="detail-group">
                            <span class="detail-label"><?php echo $item_labels[$i-1]; ?>:</span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars($row['item'.$i]); ?> 
                                (Rs. <?php echo number_format($row['price'.$i], 2); ?>)
                            </span>
                        </div>
                        <?php endfor; ?>
                        <div class="detail-group">
                            <span class="detail-label">Total Price:</span>
                            <span class="detail-value"><strong>Rs. <?php echo number_format($row['total_price'], 2); ?></strong></span>
                        </div>
                    </div>

                    
                    
                    <div class="booking-actions">
                        <?php if ($status === 'Pending'|| $status==='Confirmed'|| $status === 'pending'|| $status==='confirmed'): ?>
                            <a class="btn-receipt"> 20% of payment will not be refunded if cancelled</a>
                            <?php if ($status === 'Pending' || $status === 'pending'): ?>
                            <a href="https://esewa.com.np/#/home" class="btn-pay">
                             Pay with eSewa
                            </a>
                            <?php endif; ?>
                            <form method="POST" action="cancelbooking.php" onsubmit="return confirm('20% of payment will not be refunded. Are you sure you want to cancel this booking?');">
                                <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                <input type="hidden" name="event_type" value="<?php echo $row['event_type']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" name="cancel_booking" class="btn-cancel">
                                 <a>Cancel Booking</a>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($status === 'Ended'|| $status==="ended"): ?>
                        <div class="rating-system">
                            <p>Rate your experience:</p>
                            <div class="rating" data-booking-id="<?php echo $row['booking_id']; ?>" data-event-type="<?php echo $row['event_type']; ?>">
                                <span class="star" data-value="1">★</span>
                                <span class="star" data-value="2">★</span>
                                <span class="star" data-value="3">★</span>
                                <span class="star" data-value="4">★</span>
                                <span class="star" data-value="5">★</span>
                            </div>
                            <textarea id="rating-description-<?php echo $row['booking_id']; ?>" placeholder="Describe your experience..." rows="4" style="width: 100%; margin-top: 10px;"></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="bookings.php?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p style="text-align:center; font-size:1.1em;">No bookings found. <a href="homepageht.php">Create a new booking</a></p>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-column">
            <h3>About Us</h3>
            <p>Welcome to EventSeek! We specialize in creating unforgettable events, tailored to your needs.</p>
            <a href="https://www.instagram.com/livelyevents/" target="_blank" style="text-decoration: none;">More About Us</a>
        </div>

        <div class="footer-column">
            <h3>Get In Touch</h3>
            <ul>
                <li>livelyevents@gmail.com</li>
                 <li>9812345678</li>
            </ul>
            <a href="https://www.instagram.com/livelyevents/" target="_blank" style="text-decoration: none;">More Ways to Get In Touch</a>
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
    document.querySelectorAll('.rating').forEach(rating => {
    const stars = rating.querySelectorAll('.star');
    const bookingId = rating.dataset.bookingId;
    const eventType = rating.dataset.eventType;
    const descriptionBox = document.getElementById(`rating-description-${bookingId}`);
    const submitBtn = document.createElement('button');
    
    submitBtn.className = 'btn-submit-rating';
    submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit Rating';
    submitBtn.style.marginTop = '10px';
    submitBtn.style.padding = '8px 16px';
    submitBtn.style.background = '#4CAF50';
    submitBtn.style.color = 'white';
    submitBtn.style.border = 'none';
    submitBtn.style.borderRadius = '4px';
    submitBtn.style.cursor = 'pointer';
    
    rating.parentNode.insertBefore(submitBtn, descriptionBox.nextSibling);
    
    let selectedRating = 0;
    fetch(`check_rating.php?booking_id=${bookingId}&event_type=${eventType}`)
        .then(response => response.json())
        .then(data => {
            if (data.rated) {
                highlightStars(stars, data.rating);
                if (descriptionBox && data.description) {
                    descriptionBox.value = data.description;
                }
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Rating Submitted';
                submitBtn.style.background = '#6c757d';
            }
        });

    stars.forEach(star => {
        star.addEventListener('click', () => {
            selectedRating = star.dataset.value;
            highlightStars(stars, selectedRating);
        });
    });
    
    submitBtn.addEventListener('click', () => {
        if (selectedRating === 0) {
            alert('Please select a rating first');
            return;
        }
        
        const description = descriptionBox ? descriptionBox.value : '';
        
        fetch('submit_rating.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `booking_id=${bookingId}&event_type=${eventType}&rating=${selectedRating}&description=${encodeURIComponent(description)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you for your rating!');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Rating Submitted';
                submitBtn.style.background = '#6c757d';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error submitting rating');
            console.error(error);
        });
    });
});

function highlightStars(stars, value) {
    stars.forEach(star => {
        star.classList.toggle('active', star.dataset.value <= value);
    });
}
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>