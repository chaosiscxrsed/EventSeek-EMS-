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

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

$user_id = $_SESSION['user_id'];
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
        header("Location: bookings.php");
        exit();
}
$update_sql = "UPDATE $table SET status = 'cancelled' WHERE $id_column = ? AND u_id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("ii", $booking_id, $user_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Booking #$booking_id has been cancelled successfully.";
} else {
    $_SESSION['error_message'] = "Error cancelling booking: " . $conn->error;
}

header("Location: bookings.php");
exit();
?>