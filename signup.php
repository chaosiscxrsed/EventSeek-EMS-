<!-- signup.php -->
<?php
session_start();
$name = $email = $contact = $pass = $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $contact = $_POST["contact"];
    $pass = $_POST["password"];
    $server = "localhost";
    $user = "root";
    $password = "";
    $database = "ems";
    $conn = new mysqli($server, $user, $password, $database);

    if ($conn->connect_error) {
        die("Error in connection: " . $conn->connect_error);
    }

    $checkEmail = "SELECT u_id FROM signup_info WHERE email = ?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: signup.php");
        exit();
    } else {
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO signup_info (fullname, email, contact, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $contact, $hashedPass);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: loginht.php"); 
            exit();
        } else {
            $_SESSION['error'] = "Data insert failed: " . $conn->error;
            header("Location: signup.php");
            exit();
        }
    }
    $stmt->close();
    $conn->close();
}
?>
