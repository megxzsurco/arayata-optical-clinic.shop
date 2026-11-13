<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['product_category']);
    $price = floatval($_POST['product_price']);

    $query = "INSERT INTO orders (user_id, product_name, category, price, status) VALUES ('$user_id', '$name', '$category', '$price', 'Pending')";
    
    // Fixed: changed Query to the correct $query variable
    if ($conn->query($query)) {
        header("Location: my_portal.php?order=success");
        exit();
    } else {
        echo "Error placing order: " . $conn->error;
    }
}
?>