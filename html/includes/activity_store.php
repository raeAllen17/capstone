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
    $price = $userData["price"];
    $participants = $userData["participants"];

    $pickup_locations = isset($userData["pickup_locations"]) ? json_decode($userData["pickup_locations"], true) : [];
    $pickup_locations = implode(',', $pickup_locations);

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
    $stmt = $pdo->prepare("INSERT INTO activities (org_id, activity_name, description, location, date, distance, difficulty, price, participants, pickup_locations, images) VALUES (?,?,?, ?, ?, ?, ?, ?, ?, ?, ?)");
    

    // Execute the statement
    if ($stmt->execute([$org_id, $activity_name, $description, $location, $date, $distance, $difficulty, $price, $participants,$pickup_locations, $images])) {
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

//FUNCTIONS FOR PROFILE SETUPS

function uploadQRCode($file, $uploadData, $pdo) {
    session_start(); 

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 10 * 1024 * 1024;

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        $_SESSION['error_message'] = "File size exceeds the maximum limit of 10 MB.";
        return false;
    }

    // Validate file type
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['error_message'] = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
        return false;
    }

    // Handle upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "Error uploading file.";
        return false;
    }

    // Ensure file is readable
    if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        $_SESSION['error_message'] = "Uploaded file is unreadable.";
        return false;
    }

    try {
        // Extract data
        $userId = $uploadData['id'];
        $bankName = htmlspecialchars($uploadData['bank']);

        // Read file contents and store in chunks (prevents packet size errors)
        $imageData = file_get_contents($file['tmp_name']);

        $stmt = $pdo->prepare("INSERT INTO qr_codes (org_id, qr_code_image, bank_name) VALUES (:org_id, :qr_code_image, :bank_name)");

        $stmt->bindParam(':org_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':qr_code_image', $imageData, PDO::PARAM_LOB); 
        $stmt->bindParam(':bank_name', $bankName, PDO::PARAM_STR);

        $pdo->beginTransaction();
        $success = $stmt->execute();
        $pdo->commit();

        return $success;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        return false;
    }
}


function displayQRCodes($pdo, $userId) {
    $result = [
        'success' => false,
        'failed_message' => '',
        'data' => []
    ];

    try {
        $query = $pdo->prepare("SELECT qr_code_image, bank_name FROM qr_codes WHERE org_id = ?");
        $query->execute([$userId]);
        $result['data'] = $query->fetchAll(PDO::FETCH_ASSOC);
        $result['success'] = true;
    } catch (PDOException $e) {
        $result['failed_message'] = $e->getMessage();
    }

    return $result;
}

function actRegis($pdo, $userData, $file){
    $result = [
        'success' => false,
        'message' => '',
    ];

    $joinerId = $userData['user_id'];
    $activityId = $userData['activity_id'];
    $orgId = $userData['org_id'];

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($file['tmp_name']);

        try {
            $stmt = $pdo->prepare("INSERT INTO participants (org_id, activity_id, participant_id, image) VALUES (:org_id, :activity_id, :participant_id, :image)");
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            $stmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
            $stmt->bindParam(':participant_id', $joinerId, PDO::PARAM_INT);
            $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);

            $pdo->beginTransaction();
            $success = $stmt->execute();
            $pdo->commit();

            $result['success'] = $success;
            $result['message'] = $success ? "Registration successful." : "Failed to register.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $result['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $result['message'] = "Error uploading image.";
    }

    return $result;
}

function getParticipantRequest($pdo, $orgId, $activityId) {
    $stmt = $pdo->prepare("
        SELECT p.id, j.firstName, j.lastName, p.image 
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        WHERE p.org_id = ? AND p.activity_id = ?
    ");
    $stmt->execute([$orgId, $activityId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all participants
}

function displayOrgActivities($pdo, $orgId) {

    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
        'data' => []
    ];

    try {
        $query = "SELECT * FROM activities WHERE org_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$orgId]);
        $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['success'] = true;
    } catch (PDOException $e) {
        $result['failed_message'] = $e->getMessage();
    }

    return $result;
}

function notifyParticipant($pdo, $participantId){

$sql = "UPDATE your_table
            SET notified = 'yes'
            WHERE participant_id = :participantId
            AND notified = 'no'";


    $stmt = $pdo->prepare($sql);
    $stmt->execute(['participantId' => $participantId]);


    if ($stmt->rowCount() > 0) {
        echo "Notification status updated to 'yes' for participant ID $participantId.";
    } else {
        echo "No rows updated (either already notified or no matching record).";
    }
}     