<?php

include 'config_db.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['session_barang'] = "ERROR: Invalid menu item ID!";
    $_SESSION['session_type'] = "error";
    header("Location: ../index.php");
    exit();
}

$id = mysqli_real_escape_string($connection, $_GET['id']);

// Get item name before deleting
$selectSQL = "SELECT name_item FROM menu_item WHERE id_menu_item = '$id'";
$result = mysqli_query($connection, $selectSQL);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['session_barang'] = "ERROR: Menu item not found!";
    $_SESSION['session_type'] = "error";
    header("Location: ../index.php");
    exit();
}

$dataMenu = mysqli_fetch_assoc($result);
$itemName = $dataMenu['name_item'];

// Delete the item
$deleteSQL = "DELETE FROM menu_item WHERE id_menu_item = '$id'";
$hasilDeleteQuery = mysqli_query($connection, $deleteSQL);

if ($hasilDeleteQuery) {
    $_SESSION['session_barang'] = "Menu item <b>$itemName</b> successfully deleted!";
    $_SESSION['session_type'] = "success";
} else {
    $_SESSION['session_barang'] = "ERROR: Failed to delete item! " . mysqli_error($connection);
    $_SESSION['session_type'] = "error";
}

header("Location: ../index.php");
exit();
?>