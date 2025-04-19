<?php
// Database connection
$servername = "localhost"; // Change as needed
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "capstone"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activity_name = $_POST['activity_name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    $distance = $_POST['distance'];
    $difficulty = $_POST['difficulty'];
    $participants = $_POST['participants'];

    // Handle image uploads
    $imagePaths = [];
    if (isset($_FILES['images'])) {
        $files = $_FILES['images'];
        for ($i = 0; $i < count($files['name']); $i++) {
            $target_dir = "C:/xampp/htdocs/Capstone/uploads/"; // Ensure this directory exists and is writable
            $target_file = $target_dir . basename($files['name'][$i]);
            if (move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                $imagePaths[] = $target_file;
            }
        }
    }

    // Convert image paths to a comma-separated string
    $images = implode(',', $imagePaths);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO activities (activity_name, description, location, date, distance, difficulty, participants, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssis", $activity_name, $description, $location, $date, $distance, $difficulty, $participants, $images);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New activity created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
}

$conn->close();
?>