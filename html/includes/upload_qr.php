<?php
session_start(); // Start the session to access org_id
require 'dbCon.php'; // Include your database connection file

// Function to handle QR code upload
function uploadQRCode($file, $bankName, $org_id, $pdo) {
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
    }

    // Validate file size
    $maxFileSize = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $maxFileSize) {
        return "File size exceeds the maximum limit of 2 MB.";
    }

    // Check for upload errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($file['tmp_name']);
        
        // Prepare SQL statement to insert the QR code image and bank name
        $stmt = $pdo->prepare("INSERT INTO qr_codes (org_id, qr_code_image, bank_name) VALUES (?, ?, ?)");
        if ($stmt->execute([$org_id, $imageData, $bankName])) {
            return "QR code uploaded successfully!";
        } else {
            return "Failed to upload QR code. Please try again.";
        }
    } else {
        return "Error uploading file.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['qr_code_image'])) {
    $org_id = $_SESSION['id']; // Assuming the organization ID is stored in the session
    $bankName = $_POST['bank']; // Get the bank name from the form
    $resultMessage = uploadQRCode($_FILES['qr_code_image'], $bankName, $org_id, $pdo);
    echo "<script>alert('$resultMessage'); window.location.href='org_account.php';</script>";
}
?>