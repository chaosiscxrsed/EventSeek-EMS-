<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ems');
if ($conn->connect_error) {
    die(json_encode(['rated' => false]));
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : null;
$event_type = isset($_GET['event_type']) ? $_GET['event_type'] : null;

if (!$booking_id || !$event_type) {
    die(json_encode(['rated' => false]));
}

$stmt = $conn->prepare("SELECT rating, description FROM ratings WHERE booking_id = ? AND event_type = ?");
$stmt->bind_param("is", $booking_id, $event_type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        'rated' => true,
        'rating' => $data['rating'],
        'description' => $data['description']
    ]);
} else {
    echo json_encode(['rated' => false]);
}

$stmt->close();
$conn->close();
?>