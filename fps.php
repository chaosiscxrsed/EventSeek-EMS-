<!-- fps.php -->
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['reset_error'] = "Passwords do not match.";
        header("Location: forgotps.php");
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("SELECT u_id FROM signup_info WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['reset_error'] = "Email not found.";
        header("Location: forgotps.php");
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE signup_info SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    if ($stmt->execute()) {
        $_SESSION['reset_success'] = "Password updated successfully. Please log in.";
    } else {
        $_SESSION['reset_error'] = "Something went wrong. Please try again.";
    }

    header("Location: forgotps.php");
    exit();
}
?>
