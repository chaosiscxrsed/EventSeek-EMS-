<!-- adminratings.php -->
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_rating'])) {
    $rating_id = intval($_POST['rating_id']);
    
    $stmt = $conn->prepare("DELETE FROM ratings WHERE rating_id = ?");
    $stmt->bind_param("i", $rating_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Rating deleted successfully";
    } else {
        $_SESSION['error_message'] = "Error deleting rating: " . $conn->error;
    }
    
    header("Location: adminratings.php");
    exit();
}

$results_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;

$count_sql = "SELECT COUNT(*) AS total FROM ratings";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);

$sql = "SELECT r.rating_id, r.booking_id, r.event_type, r.rating, r.description, r.created_at,
               si.fullname AS customer_name, si.email AS customer_email,
               CASE 
                   WHEN r.event_type = 'wedding' THEN w.booking_date
                   WHEN r.event_type = 'art' THEN a.booking_date
                   WHEN r.event_type = 'conference' THEN c.booking_date
               END AS booking_date,
               CASE 
                   WHEN r.event_type = 'wedding' THEN w.event_date
                   WHEN r.event_type = 'art' THEN a.event_date
                   WHEN r.event_type = 'conference' THEN c.event_date
               END AS event_date
        FROM ratings r
        JOIN signup_info si ON (
            CASE r.event_type
                WHEN 'wedding' THEN (SELECT u_id FROM userselect WHERE b_id = r.booking_id)
                WHEN 'art' THEN (SELECT u_id FROM artselect WHERE selection_id = r.booking_id)
                WHEN 'conference' THEN (SELECT u_id FROM confselect WHERE conf_id = r.booking_id)
            END
        ) = si.u_id
        LEFT JOIN userselect w ON r.event_type = 'wedding' AND w.b_id = r.booking_id
        LEFT JOIN artselect a ON r.event_type = 'art' AND a.selection_id = r.booking_id
        LEFT JOIN confselect c ON r.event_type = 'conference' AND c.conf_id = r.booking_id
        ORDER BY r.created_at DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ratings | EventSeek</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #e0568d;
            --secondary-color: #4CAF50;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .rating-stars {
            color: var(--warning-color);
            font-size: 1.2em;
            white-space: nowrap;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 500;
            color: white;
        }
        
        .badge-wedding {
            background-color: var(--primary-color);
        }
        
        .badge-art {
            background-color: var(--info-color);
        }
        
        .badge-conference {
            background-color: var(--secondary-color);
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            color: var(--primary-color);
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }
        
        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }
        
        .pagination a:hover:not(.active) {
            background-color: #f1f1f1;
        }
        
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        
        .back-btn:hover {
            background-color: #45a049;
        }
        
        .description {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .close-modal {
            float: right;
            font-size: 1.5em;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Manage Customer Ratings</h1>
        
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
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Rating ID</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Feedback</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        $event_badge = '';
                        switch($row['event_type']) {
                            case 'wedding':
                                $event_badge = '<span class="badge badge-wedding">Wedding</span>';
                                break;
                            case 'art':
                                $event_badge = '<span class="badge badge-art">Art Exhibition</span>';
                                break;
                            case 'conference':
                                $event_badge = '<span class="badge badge-conference">Conference</span>';
                                break;
                        }
                        
                        $created_date = date("M j, Y", strtotime($row['created_at']));
                        $booking_date = $row['booking_date'] ? date("M j, Y", strtotime($row['booking_date'])) : 'N/A';
                        $event_date = $row['event_date'] ? date("M j, Y", strtotime($row['event_date'])) : 'N/A';
                        $stars = str_repeat('<i class="fas fa-star"></i>', $row['rating']) . 
                                 str_repeat('<i class="far fa-star"></i>', 5 - $row['rating']);
                    ?>
                    <tr>
                        <td><?php echo $row['rating_id']; ?></td>
                        <td>
                            <?php echo $event_badge; ?><br>
                            <small>Booking #<?php echo $row['booking_id']; ?></small><br>
                            <small>Event: <?php echo $event_date; ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($row['customer_email']); ?></small>
                        </td>
                        <td class="rating-stars"><?php echo $stars; ?></td>
                        <td class="description" title="<?php echo htmlspecialchars($row['description']); ?>">
                            <?php echo $row['description'] ? htmlspecialchars($row['description']) : 'No feedback'; ?>
                        </td>
                        <td><?php echo $created_date; ?></td>
                        <td>
                            <form method="POST" action="adminratings.php" onsubmit="return confirm('Are you sure you want to delete this rating?');">
                                <input type="hidden" name="rating_id" value="<?php echo $row['rating_id']; ?>">
                                <button type="submit" name="delete_rating" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete
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
                        <a href="adminratings.php?page=<?php echo $page - 1; ?>">&laquo;</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="adminratings.php?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="adminratings.php?page=<?php echo $page + 1; ?>">&raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p style="text-align: center;">No ratings found.</p>
        <?php endif; ?>
        
        <div style="text-align: center;">
            <a href="admindb.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>

    <script>
        document.querySelectorAll('.description').forEach(desc => {
            desc.addEventListener('click', function() {
                const fullText = this.getAttribute('title');
                if (fullText) {
                    alert('Customer Feedback:\n\n' + fullText);
                }
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>