<!-- admindb.php -->
<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gaegu&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="admin.js" defer></script>
    <style>
        .management-options {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            width: 300px;
            overflow: hidden;
            border-radius: 8px;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            background-color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        button {
            display: inline-block;
            padding: 10px;
            margin-top: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: #2e9c1d;
            color: white;
            font-weight: bold;
        }

        button:hover {
            background-color:rgb(48, 130, 52);
        }
    </style>
</head>
<body>
    <h1>Welcome to the Admin Dashboard</h1>
    <div class="section">
        <h2>Select Management Option</h2>
        <div class="management-options">

        <div class="card">
            <a href="adminitems.php">
                <img src="items.jpg" alt="Manage Items">
            </a>
            <a href="adminitems.php">
                <button>Manage Items</button>
            </a>
        </div>

        <div class="card">
            <a href="adminusers.php">
                <img src="users.jpg" alt="Manage Users">
            </a>
            <a href="adminusers.php">
                <button>Manage Users</button>
            </a>
        </div>

        <div class="card">
            <a href="adminbookings.php">
                <img src="tick.jpg" alt="Manage Bookings" style="width:100%; height:200px; object-fit:cover;">
            </a>
            <a href="adminbookings.php">
                <button>Manage Bookings</button>
            </a>
        </div>

        <div class="card">
            <a href="employee.php">
                <img src="users.jpg" alt="Manage Users">
            </a>
            <a href="employee.php">
                <button>Manage Employees</button>
            </a>
        </div>

    </div>

    <div style="text-align: right; margin-top: 20px;">
        <a href="adminlogout.php" style="
            margin: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        " onmouseover="this.style.backgroundColor='#d32f2f'" onmouseout="this.style.backgroundColor='#f44336'">
            Logout
        </a>
    </div>

</body>
</html>
