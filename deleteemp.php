<!-- deleteemp.php -->
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
    $user_id = $_GET['id'];
    $query = "SELECT * FROM employee WHERE emp_id = ?";
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $delete_query = "DELETE FROM employee WHERE emp_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $user_id);

        if ($delete_stmt->execute()) {
            header("Location: employee.php"); 
            exit();
        } else {
            $message = "Error deleting user.";
        }
    }
} else {
    echo "Invalid User ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Employee</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<h1>Delete Employee</h1>
<?php if (isset($message)): ?>
    <div class="message error"><?= $message; ?></div>
<?php endif; ?>

<div class="section">
    <p>Are you sure you want to delete this employee <strong><?= htmlspecialchars($user['emp_name']); ?></strong> (Email: <?= htmlspecialchars($user['emp_email']); ?>)?</p>

    <form method="POST" action="deleteemp.php?id=<?= $user['emp_id']; ?>">
        <button type="submit" style="background-color: #f44336;">Delete Employee</button>
        <a href="employee.php" style="
            margin: 10px;
            padding: 6px 14px;
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: normal;
            transition: background-color 0.3s;
            text-align:center;
            "onmouseover="this.style.backgroundColor='#45a049'" onmouseout="this.style.backgroundColor='#4CAF50'">
            Cancel
        </a>

    </form>
</div>

</body>
</html>

<?php
$conn->close();
?>
