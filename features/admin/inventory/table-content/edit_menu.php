<?php

include 'config_db.php';

// Get item ID
if (!isset($_GET['id'])) {
    $_SESSION['session_barang'] = "ERROR: Invalid menu item ID!";
    $_SESSION['session_type'] = "error";
    header("Location: index_menu.php");
    exit();
}

$id = mysqli_real_escape_string($connection, $_GET['id']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($connection, $_POST['name_item']);
    $category = mysqli_real_escape_string($connection, $_POST['category_item']);
    $price = mysqli_real_escape_string($connection, $_POST['price']);
    $stock = mysqli_real_escape_string($connection, $_POST['stock']);

    $updateSQL = "UPDATE menu_item 
                  SET name_item = '$name', 
                      category_item = '$category', 
                      price = '$price', 
                      stock = '$stock' 
                  WHERE id_menu_item = '$id'";
    
    $hasilUpdateQuery = mysqli_query($connection, $updateSQL);

    if ($hasilUpdateQuery) {
        $_SESSION['session_barang'] = "Menu item <b>$name</b> successfully updated!";
        $_SESSION['session_type'] = "success";
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['session_barang'] = "ERROR: Failed to update data! " . mysqli_error($connection);
        $_SESSION['session_type'] = "error";
        header("Location: ../index.php");
        exit();
    }
}

// Get existing data
$selectSQL = "SELECT * FROM menu_item WHERE id_menu_item = '$id'";
$result = mysqli_query($connection, $selectSQL);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['session_barang'] = "ERROR: Menu item not found!";
    $_SESSION['session_type'] = "error";
    header("Location: ../index.php");
    exit();
}

$dataMenu = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #1a1a1a;
            color: #ffffff;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        .card {
            background: #2a2a2a;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        h2 {
            margin-bottom: 24px;
            font-size: 24px;
            color: #ffc0cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #e0e0e0;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px 16px;
            background: #3a3a3a;
            border: 1px solid #4a4a4a;
            border-radius: 8px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #ffc0cb;
            background: #404040;
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #ffc0cb;
            color: #1a1a1a;
        }

        .btn-primary:hover {
            background: #ffb0bb;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #3a3a3a;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background: #4a4a4a;
        }

        @media (max-width: 480px) {
            .card {
                padding: 24px;
            }

            h2 {
                font-size: 20px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Edit Menu Item</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name_item">Item Name *</label>
                    <input type="text" id="name_item" name="name_item" required 
                           value="<?php echo htmlspecialchars($dataMenu['name_item']); ?>">
                </div>

                <div class="form-group">
                    <label for="category_item">Category *</label>
                    <select id="category_item" name="category_item" required>
                        <option value="">Select category</option>
                        <option value="Appetizer" <?php echo $dataMenu['category_item'] == 'Appetizer' ? 'selected' : ''; ?>>Appetizer</option>
                        <option value="Main Course" <?php echo $dataMenu['category_item'] == 'Main Course' ? 'selected' : ''; ?>>Main Course</option>
                        <option value="Soup & Salad" <?php echo $dataMenu['category_item'] == 'Soup & Salad' ? 'selected' : ''; ?>>Soup & Salad</option>
                        <option value="Dessert" <?php echo $dataMenu['category_item'] == 'Dessert' ? 'selected' : ''; ?>>Dessert</option>
                        <option value="Beverages" <?php echo $dataMenu['category_item'] == 'Beverages' ? 'selected' : ''; ?>>Beverages</option>
                        <option value="Grill & BBQ" <?php echo $dataMenu['category_item'] == 'Grill & BBQ' ? 'selected' : ''; ?>>Grill & BBQ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                           value="<?php echo htmlspecialchars($dataMenu['price']); ?>">
                </div>

                <div class="form-group">
                    <label for="stock">Stock Quantity *</label>
                    <input type="number" id="stock" name="stock" min="0" required 
                           value="<?php echo htmlspecialchars($dataMenu['stock']); ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Update Item</button>
                    <a href="../index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>