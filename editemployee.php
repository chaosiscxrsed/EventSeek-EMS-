<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'ems');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from URL parameter consistently
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) {
    echo "Invalid User ID.";
    exit();
}
$query = "SELECT * FROM employee WHERE emp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Employee not found.";
    exit();
}

$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed!");
    }

    $name = $_POST['emp_name'];
    $email = $_POST['emp_email'];
    $contact = $_POST['emp_contact'];

    $update_query = "UPDATE employee SET emp_name = ?, emp_email = ?, emp_contact = ? WHERE emp_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $name, $email, $contact, $user_id);

    if ($update_stmt->execute()) {
        $message = "Employee updated successfully!";
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $message = "Error updating Employee: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <h1>Edit Employee</h1>

    <?php if (isset($message)): ?>
        <div class="message"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="section">
        <form method="POST" action="editemployee.php?id=<?= $user_id; ?>">
            <label for="name">Name:</label>
            <input type="text" name="emp_name" id="name" value="<?= htmlspecialchars($user['emp_name']); ?>" required>
            <label for="email">Email:</label>
            <input type="email" name="emp_email" id="email" value="<?= htmlspecialchars($user['emp_email']); ?>" required>
            <label for="contact">Contact:</label>
            <input type="text" name="emp_contact" id="contact" value="<?= htmlspecialchars($user['emp_contact']); ?>" required>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <button type="submit">Update Employee</button>
        </form>
    </div>

    <div style="text-align: right; margin-top: 20px;">
        <a href="employee.php" style="
            display: inline-block;
            padding: 10px 20px;
            background-color: #2e9c1d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-right: 20px;
        " onmouseover="this.style.backgroundColor='rgb(48, 130, 52)'" onmouseout="this.style.backgroundColor='#2e9c1d'">
            Back to Employee List
        </a>
    </div>

</body>
</html>

<?php
$conn->close();
?>