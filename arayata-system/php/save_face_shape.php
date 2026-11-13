<?php
include 'db.php';
session_start();

// Allow Admin, Staff, or Customer to save analysis results safely
if (isset($_SESSION['user_id']) && isset($_POST['shape'])) {
    $user_id = $_SESSION['user_id'];
    $patient_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin/Staff Scan';
    $shape = $conn->real_escape_string($_POST['shape']);
    
    $conn->query("INSERT INTO face_analyses (user_id, patient_name, face_shape) VALUES ('$user_id', '$patient_name', '$shape')");
    echo "success";
}
?>