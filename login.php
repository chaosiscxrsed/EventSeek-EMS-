<!-- login.php -->
<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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

   $sql = "SELECT u_id, fullname, email, contact, password FROM signup_info WHERE email=?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $fullname, $db_email, $contact, $hashed_password);
        $stmt->fetch();

        if (password_verify($pass, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_email'] = $db_email;
            $_SESSION['user_name'] = $fullname;
            $_SESSION['user_contact'] = $contact;
            header("Location: homepageht.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect password!";
            header("Location: loginht.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No user found with this email.";
        header("Location: loginht.php");
        exit();
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Database error. Please try again later.";
    header("Location: loginht.php");
    exit();
}

    $conn->close();
}
?>
