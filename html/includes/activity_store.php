<?php

function createActivity($pdo, $userData, $userId){
    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
    ];

    $org_id = $userId;
    $activity_name = $userData["activity_name"];
    $description = $userData["description"];
    $location = $userData["location"];
    $date = $userData["date"];
    $distance = $userData["distance"];
    $difficulty = $userData["difficulty"];
    $participants = $userData["participants"];

    $imagePaths = [];
    if (isset($_FILES['images'])) {
        $files = $_FILES['images'];
        for ($i = 0; $i < count($files['name']); $i++) {
            $target_dir = "C:/xampp/htdocs/Capstone/uploads/"; 
            $filename = basename($files['name'][$i]);
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                $imagePaths[] = $filename; 
            }
        }
    }

    // Convert image paths to a comma-separated string
    $images = implode(',', $imagePaths);

    // Prepare and bind
    $stmt = $pdo->prepare("INSERT INTO activities (org_id, activity_name, description, location, date, distance, difficulty, participants, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    

    // Execute the statement
    if ($stmt->execute([$org_id, $activity_name, $description, $location, $date, $distance, $difficulty, $participants, $images])) {
        $result['success'] = true;
        $result['success_message'] = "New activity created successfully";
    } else {
        $result['failed_message'] = "Error: " . $stmt->errorInfo()[2];
    }

    return $result;

}

function displayActivity($pdo){
    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
        'data' => []
    ];

    try {
        $query = "SELECT * FROM activities";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['success'] = true;
    } catch (PDOException $e) {
        $result['failed_message'] = $e->getMessage();
    }

    return $result;
}

function getactivities($pdo, $activityId)  {
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
    $stmt->execute([$activityId]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($activity) {
        $stmt_images = $pdo->prepare("SELECT images FROM activities WHERE id = ?");
        $stmt_images->execute([$activityId]);
        $activity['images'] = $stmt_images->fetchAll(PDO::FETCH_COLUMN);

    }

    return $activity;
}