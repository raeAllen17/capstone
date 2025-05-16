<?php
session_start();
require 'dbCon.php'; // Assumes $pdo is defined here and connected

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES['avatar']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        $_SESSION['error_message'] = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
        header("Location: ../joiner_account.php");
        exit();
    }

    $imageData = file_get_contents($_FILES['avatar']['tmp_name']);


    if (!isset($_SESSION['id'])) {
        $_SESSION['error_message'] = "User not logged in.";
        header("Location: login.php");
        exit();
    }

    $userId = $_SESSION['id'];

    try {
        $stmt = $pdo->prepare("UPDATE account_joiner SET avatar = :avatar WHERE id = :id");
        $stmt->bindParam(':avatar', $imageData, PDO::PARAM_LOB);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success_message'] = "Avatar uploaded successfully!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No image uploaded or an error occurred.";
}

header("Location: ../joiner_account.php");
exit();
