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

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $query = "SELECT * FROM signup_info WHERE u_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
} else {
    echo "Invalid User ID.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];

    $update_query = "UPDATE signup_info SET fullname = ?, email = ?, contact = ? WHERE u_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssi", $name, $email, $contact, $user_id);

    if ($update_stmt->execute()) {
        $message = "User updated successfully!";
    } else {
        $message = "Error updating user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<h1>Edit User</h1>

<?php if (isset($message)): ?>
    <div class="message"><?= $message; ?></div>
<?php endif; ?>

<div class="section">
    <form method="POST" action="edit_user.php?u_id=<?= $user['u_id']; ?>">
        <label for="name">Name:</label>
        <input type="text" name="fullname" id="name" value="<?= htmlspecialchars($user['fullname']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" required>

        <label for="contact">Contact:</label>
        <input type="text" name="contact" id="contact" value="<?= htmlspecialchars($user['contact']); ?>" required>

        <button type="submit">Update User</button>
    </form>
</div>

<div style="text-align: right; margin-top: 20px;">
    <a href="adminusers.php" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s;
    " onmouseover="this.style.backgroundColor='#45a049'" onmouseout="this.style.backgroundColor='#4CAF50'">
        Back to User List
    </a>
</div>

</body>
</html>

<?php
$conn->close();
?>
