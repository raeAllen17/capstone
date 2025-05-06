<?php
require __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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
        $query = "SELECT * FROM activities WHERE status = 'pending'";
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

function actRegis($pdo, $userData, $file) {
    $result = [
        'success' => false,
        'message' => '',
    ];

    $joinerId = $userData['user_id'];
    $activityId = $userData['activity_id'];
    $orgId = $userData['org_id'];

    // Check current participants and max participants
    $stmt = $pdo->prepare("SELECT current_participants, participants FROM activities WHERE id = :activity_id");
    $stmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
    $stmt->execute();
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($activity) {
        $currentParticipants = $activity['current_participants'];
        $maxParticipants = $activity['participants'];

        // Determine status based on participant count
        $status = ($currentParticipants >= $maxParticipants) ? 'waitlist' : 'pending';

        // Handle image upload
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($file['tmp_name']);
        } else {
            $imageData = null; // Set to null if no image is uploaded
        }

        try {
            // Prepare the SQL statement
            $stmt = $pdo->prepare("INSERT INTO participants (org_id, activity_id, participant_id, image, status) VALUES (:org_id, :activity_id, :participant_id, :image, :status)");
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            $stmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
            $stmt->bindParam(':participant_id', $joinerId, PDO::PARAM_INT);
            $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB); // This will be NULL if no image is uploaded
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);

            // Begin transaction
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
        $result['message'] = "Activity not found.";
    }

    return $result;
}

function getParticipantRequest($pdo, $orgId, $activityId) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.participant_id, j.firstName, j.lastName, p.image 
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        WHERE p.org_id = ? AND p.activity_id = ? AND p.notified = 'no' AND p.status = 'pending'
    ");
    $stmt->execute([$orgId, $activityId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function displayOrgActivities($pdo, $orgId) {

    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
        'data' => []
    ];

    try {
        $query = "SELECT * FROM activities WHERE org_id = ? AND status = 'pending'";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$orgId]);
        $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['success'] = true;
    } catch (PDOException $e) {
        $result['failed_message'] = $e->getMessage();
    }

    return $result;
}

function getActivityforOrgDetails ($pdo, $activityId) {
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

function updateNotified ($pdo, $participantId, $activityId) {
    $stmt = $pdo->prepare("UPDATE participants SET notified = 'yes' WHERE participant_id = ? AND activity_id = ?");
    return $stmt->execute([$participantId, $activityId]);
}

function rejectRequest($pdo, $participantId, $activityId){
    $stmt = $pdo->prepare("UPDATE participants SET notified = 'cancel', image = null WHERE participant_id = ? AND activity_id = ?");
    return $stmt->execute([$participantId, $activityId]);
}

function getNotification($pdo, $participantId) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.participant_id, j.firstName, j.lastName, a.id, a.activity_name as activity_name
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        JOIN activities a ON p.activity_id = a.id 
        WHERE p.participant_id = ? AND p.notified = 'yes' AND p.status = 'pending'
    ");
    $stmt->execute([$participantId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateParticipantStatus ($pdo, $participantId, $activityId) {
    $stmt = $pdo->prepare("UPDATE participants SET status = 'active' WHERE participant_id = ? AND activity_id = ?");
    return $stmt->execute([$participantId, $activityId]);
}

function getNotificationCancelled($pdo, $participantId) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.participant_id, j.firstName, j.lastName, a.id, a.activity_name as activity_name
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        JOIN activities a ON p.activity_id = a.id 
        WHERE p.participant_id = ? AND p.notified = 'cancel'
    ");
    $stmt->execute([$participantId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateParticipantNumber($pdo, $activityId){
    $stmt = $pdo->prepare("UPDATE activities SET current_participants = current_participants + 1 WHERE id = ?");
    $stmt->execute([$activityId]);
}

function getActiveParticipants ($pdo, $orgId, $activityId) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.participant_id, j.firstName, j.lastName, p.image 
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        WHERE p.org_id = ? AND p.activity_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$orgId, $activityId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getWaitlistRequest($pdo, $userId, $activityId){
    $stmt = $pdo->prepare("
        SELECT p.id, p.participant_id, j.firstName, j.lastName, a.id, a.activity_name as activity_name
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        JOIN activities a ON p.activity_id = a.id 
        WHERE p.org_id = ? AND p.activity_id = ? AND p.notified = 'no' AND p.status = 'waitlist'
    ");
    $stmt->execute([$userId, $activityId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function actRegisUpdate($pdo, $userData, $file) {
    $result = [
        'success' => false,
        'message' => '',
    ];

    $joinerId = $userData['user_id'];
    $activityId = $userData['activity_id'];

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        error_log("File uploaded: " . print_r($file, true));
        $imageData = file_get_contents($file['tmp_name']);
    } else {
        error_log("File upload error or no file: " . print_r($file, true));
        $imageData = null;
    }

    try {
        $checkStmt = $pdo->prepare("
            SELECT id FROM participants 
            WHERE participant_id = :participant_id AND activity_id = :activity_id
        ");
        $checkStmt->execute([':participant_id' => $joinerId, ':activity_id' => $activityId]);
        if (!$checkStmt->fetch()) {
            $result['message'] = "Participant record not found for update.";
            return $result;
        }

        $stmt = $pdo->prepare("
            UPDATE participants 
            SET image = :image, 
                status = 'pending',
                notified = 'no'
            WHERE participant_id = :participant_id AND activity_id = :activity_id
        ");

        // Use bindValue here for blobs
        $stmt->bindValue(':image', $imageData, PDO::PARAM_LOB);
        $stmt->bindValue(':participant_id', $joinerId, PDO::PARAM_INT);
        $stmt->bindValue(':activity_id', $activityId, PDO::PARAM_INT);

        $pdo->beginTransaction();
        $success = $stmt->execute();
        $affectedRows = $stmt->rowCount();
        $pdo->commit();

        error_log("Update executed: success=$success, affectedRows=$affectedRows");

        if ($success && $affectedRows > 0) {
            $result['success'] = true;
            $result['message'] = "Update successful.";
        } elseif ($success) {
            $result['message'] = "Update executed but no rows changed (might be identical data).";
        } else {
            $result['message'] = "Failed to update participant.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $result['message'] = "Database error: " . $e->getMessage();
    }

    return $result;
}

function notifyParticipant($pdo, $participantId, $activityId) {
    $stmt = $pdo->prepare("SELECT email FROM account_joiner WHERE id = :participant_id");
    $stmt->bindParam(':participant_id', $participantId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $participant['email'];

        // Prepare the email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true; 
            $mail->Username = 'allenretuta10@gmail.com'; 
            $mail->Password = 'uzwk gggt nbff pdlj';
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587; 

            $mail->setFrom('allenretuta10@gmail.com', 'JOYn');
            $mail->addAddress($email);
            $mail->isHTML(true); 

            $mail->Subject = "Registration Notification";
            $mail->Body    = "Hello,<br><br>You can now try to register again for the activity. We look forward to your participation!<br><br>Best regards,<br>JOYn Team";
            $mail->AltBody = "Hello, You can now try to register again for the activity. We look forward to your participation! Best regards, JOYn Team";

            $mail->send();

            return [
                'success' => true,
                'message' => "Notification email has been sent to $email."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => "Participant not found."
        ];
    }
}

function getParticipantEmail($pdo, $participantId) {
    $stmt = $pdo->prepare("SELECT firstName, lastName, email FROM account_joiner WHERE id = ?");
    $stmt->execute([$participantId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function sendActivityReminderEmail($email, $subject, $message) {
    $mail = new PHPMailer(true); 
    try {
        $mail->isSMTP();                                    
        $mail->Host = 'smtp.gmail.com';                        
        $mail->SMTPAuth = true;                                   
        $mail->Username = 'allenretuta10@gmail.com';            
        $mail->Password = 'uzwk gggt nbff pdlj';               
        $mail->SMTPSecure = 'tls';                              
        $mail->Port = 587;                                        

        // Recipients
        $mail->setFrom('allenretuta10@gmail.com', 'JOYn');       
        $mail->addAddress($email);                                

        // Content
        $mail->isHTML(true);                                     
        $mail->Subject = $subject;                               
        $mail->Body    = $message;                               
        $mail->AltBody = strip_tags($message);           

        $mail->send();                                          
        return [
            'success' => true,
            'message' => "Notification email has been sent to $email."
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"
        ];
    }
}

function updateActivityStatus ($pdo, $activityId){
    $stmt = $pdo->prepare("UPDATE activities SET status = 'done' WHERE id = ?");
    return $stmt->execute([$activityId]);
}

function getActiveActivites($pdo, $participantId) {
    $stmt = $pdo->prepare("SELECT activity_id FROM participants WHERE participant_id = ? AND status = 'active'");
    $stmt->execute([$participantId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getActivityDetails($pdo, $activityId) {
    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
        'data' => []
    ];

    $stmt = $pdo->prepare("SELECT id, activity_name, date, org_id FROM activities WHERE id = ? AND status = 'done'");
    $stmt->execute([$activityId]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($activity) {
        $result['success'] = true;
        $result['success_message'] = 'Activity details retrieved successfully.';
        $result['data'] = [$activity];
    } else {
        $result['failed_message'] = 'No activity found or status is not "done".';
    }

    return $result;
}

function rateActivity($pdo, $participantId, $orgId, $activityId, $message, $rating, $participantName) {

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum WHERE activity_id = ? AND participant_id = ?");
    $stmt->execute([$activityId, $participantId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        return "You have already submitted a comment for this activity.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO forum (org_id, activity_id, participant_id, message, rating, participant_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$orgId, $activityId, $participantId, $message, $rating, $participantName]);
        return "Your comment has been submitted successfully.";
    }
}

function displayRating($pdo) {
    $result = [
        'success' => false,
        'data' => []
    ];

    $stmt = $pdo->prepare("
        SELECT activities.*, account_org.orgname
        FROM activities
        LEFT JOIN account_org ON activities.org_id = account_org.id
        WHERE activities.status = 'done'
    ");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($activities) {
        $result['success'] = true;
        $result['data'] = $activities;
    }

    return $result;
}

function getForumEntriesByActivityId($pdo, $activityId) {
    $result = [
        'success' => false,
        'data' => []
    ];

    $stmt = $pdo->prepare("
        SELECT message, rating, participant_name
        FROM forum
        WHERE activity_id = :activityId
    ");
    $stmt->execute(['activityId' => $activityId]);
    $forumEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($forumEntries) {
        $result['success'] = true;
        $result['data'] = $forumEntries;
    }

    return $result;
}




