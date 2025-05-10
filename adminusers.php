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

$users = $conn->query("SELECT * FROM signup_info");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<h1>Manage Users</h1>
<div class="section">
    <h2>User List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $row['u_id']; ?></td>
            <td><?= htmlspecialchars($row['fullname']); ?></td>
            <td><?= htmlspecialchars($row['contact']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td class="actions">
                <a href="adminedituser.php?id=<?= $row['u_id']; ?>"><button>Edit</button></a>
                <a href="deleteuser.php?id=<?= $row['u_id']; ?>"><button class="delete">Delete</button></a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<div style="text-align: right; margin-top: 20px;">
    <a href="admindb.php" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s;
    " onmouseover="this.style.backgroundColor='#45a049'" onmouseout="this.style.backgroundColor='#4CAF50'">
        Back to Admin Dashboard
    </a>
</div>
</body>
</html>

<?php
$conn->close();
?>
