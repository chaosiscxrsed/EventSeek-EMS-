<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ems');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed"]));
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => "Not logged in"]));
}

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
$event_type = isset($_POST['event_type']) ? $_POST['event_type'] : null;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
$description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : null;

if (!$booking_id || !$event_type || !$rating) {
    die(json_encode(['success' => false, 'message' => "Missing required fields"]));
}

if ($rating < 1 || $rating > 5) {
    die(json_encode(['success' => false, 'message' => "Invalid rating value"]));
}

$check = $conn->prepare("SELECT rating_id FROM ratings WHERE booking_id = ? AND event_type = ?");
$check->bind_param("is", $booking_id, $event_type);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

if ($exists) {
    die(json_encode(['success' => false, 'message' => "You've already rated this booking"]));
}

// Insert new rating
$stmt = $conn->prepare("INSERT INTO ratings (booking_id, event_type, rating, description) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isis", $booking_id, $event_type, $rating, $description);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => "Rating submitted successfully"]);
} else {
    echo json_encode(['success' => false, 'message' => "Error submitting rating"]);
}

$stmt->close();
$conn->close();
?>