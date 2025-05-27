<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);
    $server = "127.0.0.1";
    $user = "root";
    $db_password = "";
    $database = "ems";
    $conn = new mysqli($server, $user, $db_password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');

if (empty($fullname) || empty($email) || empty($contact)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: editprofile.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header("Location: editprofile.php");
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE signup_info SET fullname = ?, email = ?, contact = ? WHERE u_id = ?");
    $stmt->bind_param("sssi", $fullname, $email, $contact, $user_id);

    if ($stmt->execute()) {
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email'] = $email;
        $_SESSION['contact'] = $contact;

        $_SESSION['success'] = "Profile updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update profile.";
    }

    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}
header("Location: editprofile.php");
exit();
