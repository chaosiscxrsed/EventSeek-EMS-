<!-- adminitems.php -->
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
if (getenv('REQUEST_METHOD') === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed!");
    }
}
$uploadMessage = '';
$uploadSuccess = false;

if (isset($_POST['upload_item'])) {
    $type = $_POST['upload_type'];
    $description = $conn->real_escape_string(trim($_POST['upload_description']));
    $price = (float)$_POST['upload_price'];

    if (!empty($_FILES['upload_image']['name']) && in_array($type, ['flower', 'entrance','roomlight', 'prop', 'lighting', 'tabledec', 'cardboard', 'venue'])) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($_FILES["upload_image"]["name"], PATHINFO_EXTENSION));

        if (in_array($extension, $allowedExts)) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $imageName = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES["upload_image"]["name"]));
            $targetPath = "uploads/" . $imageName;

            if (move_uploaded_file($_FILES["upload_image"]["tmp_name"], $targetPath)) {
                $imagePath = $conn->real_escape_string($targetPath);
                $prefix = '';
                switch($type) {
                    case 'flower': $prefix = 'f'; break;
                    case 'entrance': $prefix = 'e'; break;
                    case 'roomlight': $prefix = 'r'; break;
                    case 'prop': $prefix = 'p'; break;
                    case 'lighting': $prefix = 'l'; break;
                    case 'tabledec': $prefix = 't'; break;
                    case 'cardboard': $prefix = 'c'; break;
                    case 'venue': $prefix = 'v'; break;
                }

                $stmt = $conn->prepare("INSERT INTO `$type` (`{$prefix}_des`, `{$prefix}_price`, `{$prefix}_image`) VALUES (?, ?, ?)");
                $stmt->bind_param("sds", $description, $price, $imagePath);
                $stmt->execute();
                $stmt->close();

                $uploadMessage = 'Item uploaded successfully!';
                $uploadSuccess = true;
            } else {
                $uploadMessage = 'Failed to upload image. Please try again.';
            }
        } else {
            $uploadMessage = 'Invalid image type. Only JPG, JPEG, PNG, GIF, WEBP allowed.';
        }
    } else {
        $uploadMessage = 'Please select a valid image and type.';
    }
}

if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = (int)$_GET['delete'];
    $type = $_GET['type'];

    if (in_array($type, ['flower', 'entrance','roomlight', 'prop', 'lighting', 'tabledec', 'cardboard', 'venue'])) {
        $idField = '';
        $imageField = '';

        switch ($type) {
            case 'flower': $idField = 'f_id'; $imageField = 'f_image'; break;
            case 'entrance': $idField = 'e_id'; $imageField = 'e_image'; break;
            case 'roomlight': $idField = 'r_id'; $imageField = 'r_image'; break;
            case 'prop': $idField = 'p_id'; $imageField = 'p_image'; break;
            case 'lighting': $idField = 'l_id'; $imageField = 'l_image'; break;
            case 'tabledec': $idField = 't_id'; $imageField = 't_image'; break;
            case 'cardboard': $idField = 'c_id'; $imageField = 'c_image'; break;
            case 'venue': $idField = 'v_id'; $imageField = 'v_image'; break;
        }

        $stmt = $conn->prepare("SELECT `$imageField` FROM `$type` WHERE `$idField` = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($currentImage);
        $stmt->fetch();
        $stmt->close();

        if ($currentImage && file_exists($currentImage)) {
            unlink($currentImage);
        }

        $stmt = $conn->prepare("DELETE FROM `$type` WHERE `$idField` = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: adminitems.php");
    exit();
}

if (isset($_POST['update_item'])) {
    $id = (int)$_POST['id'];
    $type = $_POST['type'];
    $description = $conn->real_escape_string(trim($_POST['description']));
    $price = (float)$_POST['price'];

     if (in_array($type, ['flower', 'entrance','roomlight', 'prop', 'lighting', 'tabledec', 'cardboard', 'venue'])) {
        $prefix = '';
        switch($type) {
            case 'flower': $prefix = 'f'; break;
            case 'entrance': $prefix = 'e'; break;
            case 'roomlight': $prefix = 'r'; break;
            case 'prop': $prefix = 'p'; break;
            case 'lighting': $prefix = 'l'; break;
            case 'tabledec': $prefix = 't'; break;
            case 'cardboard': $prefix = 'c'; break;
            case 'venue': $prefix = 'v'; break;
        }

        $idField = $prefix . '_id';
        $imageField = $prefix . '_image';

        if (!empty($_FILES['new_image']['name'])) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower(pathinfo($_FILES["new_image"]["name"], PATHINFO_EXTENSION));

            if (in_array($extension, $allowedExts)) {
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                $imageName = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES["new_image"]["name"]));
                $targetPath = "uploads/" . $imageName;
                move_uploaded_file($_FILES["new_image"]["tmp_name"], $targetPath);

                $imagePath = $conn->real_escape_string($targetPath);
                $stmt = $conn->prepare("SELECT `$imageField` FROM `$type` WHERE `$idField` = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($currentImage);
                $stmt->fetch();
                $stmt->close();

                if ($currentImage && file_exists($currentImage)) {
                    unlink($currentImage);
                }

                $stmt = $conn->prepare("UPDATE `$type` SET `{$prefix}_des` = ?, `{$prefix}_price` = ?, `{$prefix}_image` = ? WHERE `$idField` = ?");
                $stmt->bind_param("sdsi", $description, $price, $imagePath, $id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare("UPDATE `$type` SET `{$prefix}_des` = ?, `{$prefix}_price` = ? WHERE `$idField` = ?");
            $stmt->bind_param("sdi", $description, $price, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: adminitems.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Items</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <script src="admin.js" defer></script>
    </style>
</head>
<body>
    <h1>Manage Items</h1>
    <?php if ($uploadMessage): ?>
        <div class="message <?= $uploadSuccess ? '' : 'error' ?>">
            <?= htmlspecialchars($uploadMessage) ?>
        </div>
    <?php endif; ?>
    <div class="section">
        <a href="admindb.php" class="back-btn"> Back to Dashboard</a>
        <a href="adminlogout.php" style="
            display: inline-block;
            float: right;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin:20px;
            " onmouseover="this.style.backgroundColor='#45a049'" onmouseout="this.style.backgroundColor='#4CAF50'">
            Logout
        </a>
    <h2>Upload New Item</h2>
    <form method="POST" enctype="multipart/form-data">
        <select name="upload_type" required>
            <option value="">Select Type</option>
            <option value="flower">Flower</option>
            <option value="entrance">Entrance</option>
            <option value="roomlight">Room Light</option>
            <option value="prop">Direction Prop</option>
            <option value="lighting">Lighting</option>
            <option value="tabledec">Table Decoration</option>
            <option value="cardboard">Cardboard</option>
            <option value="venue">Venue</option>
        </select>
        <input type="text" name="upload_description" placeholder="Enter description" required>
        <input type="number" step="0.01" name="upload_price" placeholder="Enter price (Rs)" required>
        <input type="file" name="upload_image" accept="image/*" required>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <button type="submit" name="upload_item">Upload</button>
    </form>

    <div class="section">
        <h2>Flowers</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $flowers = $conn->query("SELECT * FROM flower");
            while ($row = $flowers->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['f_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['f_image']); ?>" alt="Flower"></td>
                <td><?= htmlspecialchars($row['f_des']); ?></td>
                <td><?= number_format($row['f_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['f_id']; ?>">
                        <input type="hidden" name="type" value="flower">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['f_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['f_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item">Update</button>
                    </form>
                    <a href="?delete=<?= $row['f_id']; ?>&type=flower"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>Entrances</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $entrances = $conn->query("SELECT * FROM entrance");
            while ($row = $entrances->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['e_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['e_image']); ?>" alt="Entrance"></td>
                <td><?= htmlspecialchars($row['e_des']); ?></td>
                <td><?= number_format($row['e_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['e_id']; ?>">
                        <input type="hidden" name="type" value="entrance">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['e_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['e_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item">Update</button>
                    </form>
                    <a href="?delete=<?= $row['e_id']; ?>&type=entrance"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>Room Lights</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $roomlights = $conn->query("SELECT * FROM roomlight");
            while ($row = $roomlights->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['r_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['r_image']); ?>" alt="roomlight"></td>
                <td><?= htmlspecialchars($row['r_des']); ?></td>
                <td><?= number_format($row['r_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['r_id']; ?>">
                        <input type="hidden" name="type" value="roomlight">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['r_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['r_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item" >Update</button>
                    </form>
                    <a href="?delete=<?= $row['r_id']; ?>&type=roomlight"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>Direction Props</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $props = $conn->query("SELECT * FROM prop");
            while ($row = $props->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['p_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['p_image']); ?>" alt="prop"></td>
                <td><?= htmlspecialchars($row['p_des']); ?></td>
                <td><?= number_format($row['p_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['p_id']; ?>">
                        <input type="hidden" name="type" value="prop">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['p_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['p_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item" >Update</button>
                    </form>
                    <a href="?delete=<?= $row['p_id']; ?>&type=prop"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>Lightings</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $lightings = $conn->query("SELECT * FROM lighting");
            while ($row = $lightings->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['l_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['l_image']); ?>" alt="lighting"></td>
                <td><?= htmlspecialchars($row['l_des']); ?></td>
                <td><?= number_format($row['l_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['l_id']; ?>">
                        <input type="hidden" name="type" value="lighting">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['l_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['l_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item" >Update</button>
                    </form>
                    <a href="?delete=<?= $row['l_id']; ?>&type=lighting"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>Table Decorations</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $tabledecs = $conn->query("SELECT * FROM tabledec");
            while ($row = $tabledecs->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['t_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['t_image']); ?>" alt="tabledec"></td>
                <td><?= htmlspecialchars($row['t_des']); ?></td>
                <td><?= number_format($row['t_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['t_id']; ?>">
                        <input type="hidden" name="type" value="tabledec">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['t_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['t_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item" >Update</button>
                    </form>
                    <a href="?delete=<?= $row['t_id']; ?>&type=tabledec"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>Cardboards</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $cardboards = $conn->query("SELECT * FROM cardboard");
            while ($row = $cardboards->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['c_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['c_image']); ?>" alt="Cardboard"></td>
                <td><?= htmlspecialchars($row['c_des']); ?></td>
                <td><?= number_format($row['c_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['c_id']; ?>">
                        <input type="hidden" name="type" value="cardboard">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['c_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['c_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item" >Update</button>
                    </form>
                    <a href="?delete=<?= $row['c_id']; ?>&type=cardboard"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h2>Venues</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price (Rs)</th>
                <th>Actions</th>
            </tr>
            <?php
            $venues = $conn->query("SELECT * FROM venue");
            while ($row = $venues->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['v_id']; ?></td>
                <td><img src="<?= htmlspecialchars($row['v_image']); ?>" alt="venue"></td>
                <td><?= htmlspecialchars($row['v_des']); ?></td>
                <td><?= number_format($row['v_price'], 2); ?></td>
                <td class="actions">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['v_id']; ?>">
                        <input type="hidden" name="type" value="venue">
                        <input type="text" name="description" value="<?= htmlspecialchars($row['v_des']); ?>">
                        <input type="number" step="0.01" name="price" value="<?= $row['v_price']; ?>">
                        <input type="file" name="new_image" accept="image/*">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="update_item" >Update</button>
                    </form>
                    <a href="?delete=<?= $row['v_id']; ?>&type=venue"><button class="delete">Delete</button></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php $conn->close(); ?>

</body>
</html>
