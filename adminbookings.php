<!-- adminbookings.php -->
<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'ems');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $booking_id = intval($_POST['booking_id']);
        $event_type = $_POST['event_type'];
        $new_status = $_POST['status'];
        switch($event_type) {
            case 'wedding':
                $table = 'userselect';
                $id_column = 'b_id';
                break;
            case 'art':
                $table = 'artselect';
                $id_column = 'selection_id';
                break;
            case 'conference':
                $table = 'confselect';
                $id_column = 'conf_id';
                break;
            default:
                $_SESSION['error_message'] = "Invalid event type";
                header("Location: adminbookings.php");
                exit();
        }
        
        $update_sql = "UPDATE $table SET status = ? WHERE $id_column = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_status, $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Status updated successfully for booking #$booking_id";
        } else {
            $_SESSION['error_message'] = "Error updating status: " . $conn->error;
        }
    } 
    elseif (isset($_POST['delete_booking'])) {
        $booking_id = intval($_POST['booking_id']);
        $event_type = $_POST['event_type'];
        switch($event_type) {
            case 'wedding':
                $table = 'userselect';
                $id_column = 'b_id';
                break;
            case 'art':
                $table = 'artselect';
                $id_column = 'selection_id';
                break;
            case 'conference':
                $table = 'confselect';
                $id_column = 'conf_id';
                break;
            default:
                $_SESSION['error_message'] = "Invalid event type";
                header("Location: adminbookings.php");
                exit();
        }
        
        $delete_sql = "DELETE FROM $table WHERE $id_column = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Booking #$booking_id deleted successfully";
        } else {
            $_SESSION['error_message'] = "Error deleting booking: " . $conn->error;
        }
    }
    
    header("Location: adminbookings.php");
    exit();
}

$results_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;
$count_sql = "SELECT (
    SELECT COUNT(*) FROM userselect
) + (
    SELECT COUNT(*) FROM artselect
) + (
    SELECT COUNT(*) FROM confselect
) AS total";

$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);
$query = "(
    -- Wedding bookings
    SELECT 
        'wedding' AS event_type,
        us.b_id AS booking_id,
        us.booking_date,
        us.event_date,
        si.fullname,
        si.email,
        si.contact,
        f.f_des AS item1_desc,
        e.e_des AS item2_desc,
        c.c_des AS item3_desc,
        v.v_des AS item4_desc,
        f.f_price AS item1_price,
        e.e_price AS item2_price,
        c.c_price AS item3_price,
        v.v_price AS item4_price,
        us.status,
        us.total_price
    FROM userselect us
    JOIN signup_info si ON us.u_id = si.u_id
    JOIN flower f ON us.f_id = f.f_id
    JOIN entrance e ON us.e_id = e.e_id
    JOIN cardboard c ON us.c_id = c.c_id
    JOIN venue v ON us.v_id = v.v_id
)
UNION ALL
(
    -- Art exhibition bookings
    SELECT 
        'art' AS event_type,
        ab.selection_id AS booking_id,
        ab.booking_date,
        ab.event_date,
        si.fullname,
        si.email,
        si.contact,
        r.r_des AS item1_desc,
        p.p_des AS item2_desc,
        c.c_des AS item3_desc,
        v.v_des AS item4_desc,
        r.r_price AS item1_price,
        p.p_price AS item2_price,
        c.c_price AS item3_price,
        v.v_price AS item4_price,
        ab.status,
        ab.total_price
    FROM artselect ab
    JOIN signup_info si ON ab.u_id = si.u_id
    JOIN roomlight r ON ab.r_id = r.r_id
    JOIN prop p ON ab.p_id = p.p_id
    JOIN cardboard c ON ab.c_id = c.c_id
    JOIN venue v ON ab.v_id = v.v_id
)
UNION ALL
(
    -- Conference bookings
    SELECT 
        'conference' AS event_type,
        cb.conf_id AS booking_id,
        cb.booking_date,
        cb.event_date,
        si.fullname,
        si.email,
        si.contact,
        l.l_des AS item1_desc,
        t.t_des AS item2_desc,
        c.c_des AS item3_desc,
        v.v_des AS item4_desc,
        l.l_price AS item1_price,
        t.t_price AS item2_price,
        c.c_price AS item3_price,
        v.v_price AS item4_price,
        cb.status,
        cb.total_price
    FROM confselect cb
    JOIN signup_info si ON cb.u_id = si.u_id
    JOIN lighting l ON cb.l_id = l.l_id
    JOIN tabledec t ON cb.t_id = t.t_id
    JOIN cardboard c ON cb.c_id = c.c_id
    JOIN venue v ON cb.v_id = v.v_id
)
ORDER BY event_date DESC
LIMIT ?, ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | EventSeek</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <script src="admin.js" defer></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: white;
            color: #333;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

       /* Modal Content */
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;  /* Control the width here */
            max-width: 900px; /* Maximum width to avoid taking full screen */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1001;  /* Ensure it stays above other content */
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            z-index: 1000;
            overflow: auto;
            padding: 20px;
        }

        .close-btn {
            cursor: pointer;
            color: #333;
            font-size: 24px;
            font-weight: bold;
            background: transparent;
            border: none;
            padding: 5px;
        }

    </style>

</head>
<body>
    <div class="container">
        <h1> Manage Bookings</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h3>All Bookings</h3>
                <div>
                    <span>Total Bookings: <?php echo $total_rows; ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Event Type</th>
                                <th>Customer</th>
                                <th>Booking Date</th>
                                <th>Event Date</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): 
                                $event_badge = '';
                                switch($row['event_type']) {
                                    case 'wedding':
                                        $event_badge = '<span class="badge badge-wedding"> Wedding</span>';
                                        break;
                                    case 'art':
                                        $event_badge = '<span class="badge badge-art"> Exhibition</span>';
                                        break;
                                    case 'conference':
                                        $event_badge = '<span class="badge badge-conference"> Conference</span>';
                                        break;
                                }
                      
                                $booking_date = date("M j, Y", strtotime($row['booking_date']));
                                $event_date = date("M j, Y", strtotime($row['event_date']));
                                $status = $row['status'] ?? 'Pending';
                                $status_class = 'status-' . $status;
                            ?>
                            <tr>
                                <td><?php echo $row['booking_id']; ?></td>
                                <td><?php echo $event_badge; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['fullname']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['email']); ?></small><br>
                                    <small><?php echo htmlspecialchars($row['contact']); ?></small>
                                </td>
                                <td><?php echo $booking_date; ?></td>
                                <td><?php echo $event_date; ?></td>
                                <td>Rs. <?php echo number_format($row['total_price'], 2); ?></td>
                                <td>
                                    <form method="POST" action="adminbookings.php" style="display: flex; align-items: center; gap: 5px;">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                        <input type="hidden" name="event_type" value="<?php echo $row['event_type']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="ended" <?php echo $status === 'ended' ? 'selected' : ''; ?>>Ended</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm view-details" 
                                            data-event-type="<?php echo $row['event_type']; ?>"
                                            data-booking-id="<?php echo $row['booking_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['fullname']); ?>"
                                            data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                            data-contact="<?php echo htmlspecialchars($row['contact']); ?>"
                                            data-booking-date="<?php echo $booking_date; ?>"
                                            data-event-date="<?php echo $event_date; ?>"
                                            data-status="<?php echo $status; ?>"
                                            data-total="<?php echo number_format($row['total_price'], 2); ?>"
                                            data-item1="<?php echo htmlspecialchars($row['item1_desc']); ?> (Rs. <?php echo number_format($row['item1_price'], 2); ?>)"
                                            data-item2="<?php echo htmlspecialchars($row['item2_desc']); ?> (Rs. <?php echo number_format($row['item2_price'], 2); ?>)"
                                            data-item3="<?php echo htmlspecialchars($row['item3_desc']); ?> (Rs. <?php echo number_format($row['item3_price'], 2); ?>)"
                                            data-item4="<?php echo htmlspecialchars($row['item4_desc']); ?> (Rs. <?php echo number_format($row['item4_price'], 2); ?>)">
                                            View
                                    </button>
                                    <form method="POST" action="adminbookings.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                        <input type="hidden" name="event_type" value="<?php echo $row['event_type']; ?>">
                                        <button type="submit" name="delete_booking" class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="adminbookings.php?page=<?php echo $page - 1; ?>">&laquo;</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="adminbookings.php?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="adminbookings.php?page=<?php echo $page + 1; ?>">&raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="text-align: center;">No bookings found.</p>
                <?php endif; ?>
            </div>
        </div>
        <a href="adminratings.php" style="
            display: inline-block;
            float: right;
            padding: 10px 20px;
            background-color:rgb(226, 217, 35);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin:20px;
        " onmouseover="this.style.backgroundColor='rgb(190, 219, 45)'" onmouseout="this.style.backgroundColor='rgb(226, 217, 35)'">
            Feedbacks
        </a>

        <a href="admindb.php" class="back-btn"> Back to Dashboard</a>
    </div>

    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 id="modalTitle">Booking Details</h2>
            <div id="modalContent">
            </div>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>