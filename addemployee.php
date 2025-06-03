<!-- addemployee.php -->
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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $address = $conn->real_escape_string($_POST['address']);
    $role = $conn->real_escape_string($_POST['role']);

    // Check if email already exists
    $check = $conn->query("SELECT emp_id FROM employee WHERE emp_email = '$email'");
    if ($check->num_rows > 0) {
        $error = "Email address already exists!";
    } else {
        $sql = "INSERT INTO employee (emp_name, emp_email, emp_contact, emp_address, role) 
                VALUES ('$name', '$email', '$contact', '$address', '$role')";
        
        if ($conn->query($sql)) {
            $message = "Employee added successfully!";
            // Clear form values
            $_POST = array();
        } else {
            $error = "Error adding employee: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Employee</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Additional styles for form alignment */
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        .form-group input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #e0568d;
            box-shadow: 0 0 0 2px rgba(224, 86, 141, 0.2);
        }
        .btn-submit {
            background-color: #e0568d;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #c04a77;
        }
    </style>
</head>
<body>
    <h1>Add New Employee</h1>
    <div class="section">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="addemployee.php">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact"
                           value="<?= isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address"
                           value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="role">Role/Department *</label>
                    <input type="text" id="role" name="role" required
                           value="<?= isset($_POST['role']) ? htmlspecialchars($_POST['role']) : '' ?>">
                </div>
                
                <button type="submit" class="btn-submit">Add Employee</button>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="employee.php" class="back-btn">Back to Employee List</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>