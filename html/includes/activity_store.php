<?php
require __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function createActivity($pdo, $userData, $userId){
    $result = [
        'success' => false,  
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

    $activityDate = strtotime($date);
    $currentDate = strtotime(date('Y-m-d'));
    
    if ($activityDate < $currentDate) {
        $_SESSION['error_message'] = "The activity date cannot be in the past.";
        return $result;
    }

    $pickup_locations_raw = $userData["pickup_locations"] ?? '[]';
    $pickup_locations_array = json_decode($pickup_locations_raw, true);

    // Fallback if decoding fails or is not an array
    if (!is_array($pickup_locations_array)) {
        $pickup_locations_array = [];
    }

$pickup_locations = implode(',', $pickup_locations_array);

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
        $_SESSION['success_message'] = "New activity created successfully";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->errorInfo()[2];
    }

    return $result;

}
function displayActivity($pdo, $price = '', $date = '', $distance = '', $availability = '') {
    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
        'data' => []
    ];

    try {
        // Start building the SQL query
        $query = "SELECT * FROM activities WHERE status = 'pending'"; // Default filter: activities with 'pending' status
        
        // Initialize an array to store the ordering conditions
        $orderByConditions = [];

        // Apply filters if they are set

        // Price filter
        if ($price == 'low-high') {
            $orderByConditions[] = "price ASC"; // Ascending price
        } elseif ($price == 'high-low') {
            $orderByConditions[] = "price DESC"; // Descending price
        }

        // Date filter
        if ($date == 'soon') {
            $orderByConditions[] = "date ASC";  // Soonest activities first
        } elseif ($date == 'later') {
            $orderByConditions[] = "date DESC"; // Latest activities first
        }

        // Distance filter
        if ($distance == 'longest') {
            // We will strip the non-numeric characters from distance for sorting
            $orderByConditions[] = "CAST(SUBSTRING_INDEX(distance, ' ', 1) AS DECIMAL) DESC";  // Longest first
        } elseif ($distance == 'shortest') {
            // We will strip the non-numeric characters from distance for sorting
            $orderByConditions[] = "CAST(SUBSTRING_INDEX(distance, ' ', 1) AS DECIMAL) ASC";  // Shortest first
        }

        // Availability filter (checkbox)
        if ($availability == 'yes') {
            $query .= " AND current_participants < participants";  // Only activities that have available spots
        }

        // If any order conditions are set, append them to the query
        if (count($orderByConditions) > 0) {
            $query .= " ORDER BY " . implode(", ", $orderByConditions);  // Combine all order by conditions
        }
        // Prepare and execute the query
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
        $query = $pdo->prepare("SELECT id,qr_code_image, bank_name FROM qr_codes WHERE org_id = ?");
        $query->execute([$userId]);
        $result['data'] = $query->fetchAll(PDO::FETCH_ASSOC);
        $result['success'] = true;
    } catch (PDOException $e) {
        $result['failed_message'] = $e->getMessage();
    }

    return $result;
}

function actRegis($pdo, $userData, $pickup_location, $file) {
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
            $stmt = $pdo->prepare("INSERT INTO participants 
                (org_id, activity_id, pickup_location, participant_id, image, status) 
                VALUES 
                (:org_id, :activity_id, :pickup_location, :participant_id, :image, :status)");

            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            $stmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
            $stmt->bindParam(':pickup_location', $pickup_location, PDO::PARAM_STR);
            $stmt->bindParam(':participant_id', $joinerId, PDO::PARAM_INT);
            $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
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
        WHERE p.org_id = ? AND p.activity_id = ? AND p.notified = 'no' AND p.status = 'pending' AND p.refund = 'no'
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
        SELECT 
            p.id, 
            p.participant_id, 
            p.org_id,
            j.firstName, 
            j.lastName, 
            a.id AS activity_id, 
            a.activity_name
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        JOIN activities a ON p.activity_id = a.id 
        WHERE p.participant_id = ? 
          AND p.notified = 'yes' 
          AND p.status = 'pending'
    ");
    $stmt->execute([$participantId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function updateParticipantStatus ($pdo, $participantId, $activityId) {
    $stmt = $pdo->prepare("UPDATE participants SET status = 'active' WHERE participant_id = ? AND activity_id = ?");
    return $stmt->execute([$participantId, $activityId]);
}

function getNotificationJoiner(PDO $pdo, int $participantId): array {
    $sql = "
        SELECT *
        FROM notification_joiner
        WHERE participant_id = ?
        ORDER BY created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$participantId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function updateParticipantNumber($pdo, $activityId){
    $stmt = $pdo->prepare("UPDATE activities SET current_participants = current_participants + 1 WHERE id = ?");
    $stmt->execute([$activityId]);
}

function getActiveParticipants ($pdo, $orgId, $activityId) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.participant_id, j.firstName, j.lastName, j.contactNumber, p.image, p.pickup_location
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        WHERE p.org_id = ? AND p.activity_id = ? AND p.status = 'active' AND p.refund = 'no'
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
        WHERE p.org_id = ? AND p.activity_id = ? AND p.notified = 'no' AND p.status = 'waitlist' AND p.refund = 'no'
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

        $activityStmt = $pdo->prepare("SELECT activity_name FROM activities WHERE id = :activity_id");
        $activityStmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
        $activityStmt->execute();
        $activity = $activityStmt->fetch(PDO::FETCH_ASSOC);

        if (!$activity) {
            return [
                'success' => false,
                'message' => "Activity not found."
            ];
        }
        $activityName = htmlspecialchars($activity['activity_name']);

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

            $mail->Subject = "Registration Notification \"$activityName\"";
            $mail->Body    = "Hello,<br><br>You can now try to register again for the activity. We look forward to your participation!<br><br>Best regards,<br>JOYn Team";
            $mail->AltBody = "Hello, You can now try to register again for the activity. We look forward to your participation! Best regards, JOYn Team";

            $mail->send();

            $updateStmt = $pdo->prepare("UPDATE participants SET notified = 'yes' WHERE participant_id = :participant_id AND activity_id = :activity_id");
            $updateStmt->bindParam(':participant_id', $participantId, PDO::PARAM_INT);
            $updateStmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
            $updateStmt->execute();

            return [
                'success' => true,
                'message' => "Notification email has been sent to participant."
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
function updateActivityStatus($pdo, $activityId) {
    $result = [
        'success' => false,
        'error_message' => '',
        'success_message' => '',
    ];

    $stmt = $pdo->prepare("SELECT date FROM activities WHERE id = ?");
    $stmt->execute([$activityId]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activity) {
        $result['error_message'] = "Activity not found.";
        return $result;
    }

    $eventDate = $activity['date'];
    $today = date('Y-m-d');

    if ($today < $eventDate) {
        $result['error_message'] = "Activity is not yet done. Scheduled for {$eventDate}.";
        $_SESSION['error_message'] = $result['error_message'];
        return $result;
    }
    
    $updateStmt = $pdo->prepare("UPDATE activities SET status = 'done' WHERE id = ?");
    $updateSuccess = $updateStmt->execute([$activityId]);

    if ($updateSuccess) {
        $result['success'] = true;
        $result['success_message'] = "Congratulations on a successful activity!";
        $_SESSION['success_message'] = $result['success_message'];
    } else {
        $result['error_message'] = "Failed to update status.";
        $_SESSION['error_message'] = $result['error_message'];
    }

    return $result;
}
function getActiveActivites($pdo, $participantId) {
    $stmt = $pdo->prepare("SELECT activity_id FROM participants WHERE participant_id = ? AND status = 'active' AND refund = 'no'");
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
        $_SESSION['error_message']="You already rated this activity!";
        return "You have already submitted a comment for this activity.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO forum (org_id, activity_id, participant_id, message, rating, participant_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$orgId, $activityId, $participantId, $message, $rating, $participantName]);
        $_SESSION['success_message']="Thank you for rating the activity!";
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

    // SQL query to fetch forum entries with participant information (avatar, name)
    $stmt = $pdo->prepare("
        SELECT f.message, f.rating, f.participant_name, f.participant_id, aj.avatar 
        FROM forum f
        LEFT JOIN account_joiner aj ON f.participant_id = aj.id
        WHERE f.activity_id = :activityId
    ");
    $stmt->execute(['activityId' => $activityId]);
    $forumEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($forumEntries) {
        $result['success'] = true;
        $result['data'] = $forumEntries;
    }

    return $result;
}


function getCurrentActivities($pdo, $userId) {
    $result = [
        'success' => false,
        'data' => []
    ];
    
    $stmt = $pdo->prepare("
        SELECT activities.id, activities.activity_name, activities.date
        FROM participants
        JOIN activities ON participants.activity_id = activities.id
        WHERE participants.status = 'active' 
            AND participants.participant_id = ? 
            AND refund = 'no'
            AND activities.status != 'done'
    ");
    $stmt->execute([$userId]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($activities) {
        $result['success'] = true;
        $result['data'] = $activities;
    }
    
    return $result;
}

function setRefundYes($pdo, $userId, $activityId) {
    $result = [
        'success' => false,
        'message' => ''
    ];

    $stmt = $pdo->prepare("SELECT refund FROM participants WHERE participant_id = ? AND activity_id = ?");
    $stmt->execute([$userId, $activityId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['refund'] === 'yes') {
        $result['message'] = 'Refund has already been submitted.';
        return $result;
    }

    $stmt = $pdo->prepare("UPDATE participants SET refund = 'yes' WHERE participant_id = ? AND activity_id = ?");
    $stmt->execute([$userId, $activityId]);

    $result['success'] = true;
    $result['message'] = 'Refund request successfully sent.';

    return $result;
}

function getRefundRequest($pdo, $orgId, $activityId) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.participant_id, j.firstName, j.lastName, p.image 
        FROM participants p
        JOIN account_joiner j ON p.participant_id = j.id 
        WHERE p.org_id = ? AND p.activity_id = ? AND p.notified = 'yes' AND p.status = 'active' AND p.refund = 'yes'
    ");
    $stmt->execute([$orgId, $activityId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateRefundRequest($pdo, $participantId, $activityId){
    $stmt1 = $pdo->prepare("UPDATE participants SET notified = 'cancel', image = null, refund = 'done' WHERE participant_id = ? AND activity_id = ?");
    $stmt1->execute([$participantId, $activityId]);

    $stmt2 = $pdo->prepare("UPDATE activities SET current_participants = current_participants - 1 WHERE id = ? AND current_participants > 0");
    $stmt2->execute([$activityId]);

    return true;
}

function updateActivityDetails($pdo, $userId, $activityId, $data) {
    $result = [
        'success' => false,
        'error_message' => '',
        'success_message' => '',
        'data' => [],
    ];

    try {
        // Fetch existing activity
        $fetchSql = "SELECT date FROM activities WHERE id = :activity_id AND org_id = :user_id";
        $fetchStmt = $pdo->prepare($fetchSql);
        $fetchStmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
        $fetchStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $fetchStmt->execute();
        $existing = $fetchStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            $_SESSION['error_message'] = 'Activity not found.';
            return $result;
        }

        // Date validation and change detection
        $newDate = new DateTime($data['date']);
        $existingDate = new DateTime($existing['date']);
        $today = new DateTime();
        $today->setTime(0, 0);

        if ($newDate < $today) {
            $_SESSION['error_message'] = 'Activity date cannot be set in the past.';
            return $result;
        }

        $isDateChanged = $newDate != $existingDate;

        // Update query
        $sql = "UPDATE activities SET 
                    activity_name = :activity_name,
                    description = :description,
                    location = :location,
                    date = :date,
                    distance = :distance,
                    difficulty = :difficulty,
                    price = :price,
                    participants = :participants,
                    pickup_locations = :pickup_locations
                WHERE id = :activity_id AND org_id = :user_id";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':activity_name', $data['activity_name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':date', $data['date']);
        $stmt->bindParam(':distance', $data['distance']);
        $stmt->bindParam(':difficulty', $data['difficulty']);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_INT);
        $stmt->bindParam(':participants', $data['participants'], PDO::PARAM_INT);
        $stmt->bindParam(':pickup_locations', $data['pickup_locations']);
        $stmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $result['success'] = true;
                $_SESSION['success_message'] = 'Activity updated successfully.';
                $result['data'] = [
                    'activity_id' => $activityId,
                    'date_changed' => $isDateChanged
                ];
            } else {
                $_SESSION['error_message'] = 'Activity failed to upload, check input values.';
            }
        } else {
            $_SESSION['error_message'] = 'Failed to execute update statement.';
        }

    } catch (PDOException $e) {
        error_log("Activity update failed: " . $e->getMessage());
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }

    return $result;
}

function calendarModuleActivities($pdo, $year, $month) {
    $stmt = $pdo->prepare("
        SELECT DAY(date) AS day, COUNT(*) AS activity_count 
        FROM activities 
        WHERE YEAR(date) = :year AND MONTH(date) = :month 
        GROUP BY day
    ");
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    $stmt->execute();

    // Reformat as [day => activity_count]
    $results = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $results[(int)$row['day']] = (int)$row['activity_count'];
    }

    return $results;
}

function getNotificationOrg($pdo, $userId) {
    // Prepare the SQL query to get notifications based on org_id
    $stmt = $pdo->prepare("SELECT id, org_id, activity_id, message, created_at FROM notifications WHERE org_id = :org_id");
    $stmt->bindParam(':org_id', $userId, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the results as an associative array
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are any notifications
    if ($notifications) {
        return $notifications; // Return notifications if found
    } else {
        return []; // Return an empty array if no notifications found
    }
}
function orgForum($pdo, $userId) {
    $result = [
        'success' => false,
        'data' => []
    ];

    $stmt = $pdo->prepare("
        SELECT activities.*, account_org.orgname
        FROM activities
        LEFT JOIN account_org ON activities.org_id = account_org.id
        WHERE activities.status = 'done' AND activities.org_id = :org_id
    ");
    
    $stmt->execute(['org_id' => $userId]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($activities) {
        foreach ($activities as &$activity) {
            // Parse images from comma-separated string
            $images = [];
            if (!empty($activity['images'])) {
                $imagePaths = explode(',', $activity['images']);
                foreach ($imagePaths as $img) {
                    $basename = basename(trim($img));
                    $images[] = '../uploads/' . $basename;  // or '/uploads/' if used directly in browser
                }
            }

            $activity['image_urls'] = $images;
        }

        $result['success'] = true;
        $result['data'] = $activities;
    }

    return $result;
}
function insertNotification($pdo, $activityId, $orgId, $participantId){

    $stmtActivity = $pdo->prepare("SELECT activity_name FROM activities WHERE id = ?");
    $stmtActivity->execute([$activityId]);
    $activity = $stmtActivity->fetch(PDO::FETCH_ASSOC);

    //get joiner name for notifications table
    $stmtParticipant = $pdo->prepare("SELECT firstName FROM account_joiner WHERE id = ?");
    $stmtParticipant->execute([$participantId]);
    $participantName = $stmtParticipant->fetch(PDO::FETCH_ASSOC);

    $participantName = $participantName['firstName']; 
    $activity = $activity['activity_name'];

    $message = "$participantName confirmed to join $activity";

    $stmt = $pdo->prepare("INSERT INTO notifications (org_id, activity_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$orgId, $activityId, $message]);
}

function getMarketplace($pdo, $userId, $category = '') {
    $query = "
        SELECT 
            m.id AS marketplace_id,
            m.participant_id,
            aj.firstName,
            aj.lastName,
            m.item_name,
            m.price,
            m.location,
            m.condition,
            m.category,
            m.description,
            i.image
        FROM 
            marketplace m
        LEFT JOIN 
            marketplace_images i ON m.id = i.marketplace_id
        LEFT JOIN 
            account_joiner aj ON m.participant_id = aj.id
        WHERE 
            m.participant_id != :sessionId  -- Exclude listings by current user
            AND m.status = 'pending'       -- Only include pending listings
    ";

    $params = [':sessionId' => $userId];

    // Filter by category if provided
    if ($category !== '') {
        $query .= " AND m.category = :category";
        $params[':category'] = (int)$category; // Cast to int for safety
    }

    $query .= " ORDER BY m.id DESC";  // Sort by latest listings

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($rows as $row) {
        $id = $row['marketplace_id'];
        if (!isset($grouped[$id])) {
            $grouped[$id] = [
                'id' => $row['marketplace_id'],
                'participant_id' => $row['participant_id'],
                'firstName' => $row['firstName'],
                'lastName' => $row['lastName'],
                'item_name' => $row['item_name'],
                'price' => $row['price'],
                'location' => $row['location'],
                'condition' => $row['condition'],
                'category' => $row['category'],
                'description' => $row['description'],
                'images' => [],
            ];
        }

        if ($row['image']) {
            $grouped[$id]['images'][] = base64_encode($row['image']);
        }
    }

    return array_values($grouped);
}
function getUserListing($pdo, $userId, $category = '') {
    $query = "
        SELECT 
            m.id AS marketplace_id,
            m.participant_id,
            aj.firstName,
            aj.lastName,
            m.item_name,
            m.price,
            m.location,
            m.condition,
            m.category,
            m.description,
            i.image
        FROM 
            marketplace m
        LEFT JOIN 
            marketplace_images i ON m.id = i.marketplace_id
        LEFT JOIN 
            account_joiner aj ON m.participant_id = aj.id
        WHERE 
            m.participant_id = :sessionId
    ";

    $params = [':sessionId' => $userId];

    // Filter by category if provided (0, 1, 2 are valid)
    if ($category !== '') {
        $query .= " AND m.category = :category";
        $params[':category'] = (int)$category; // Cast to int for safety
    }

    $query .= " ORDER BY m.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($rows as $row) {
        $id = $row['marketplace_id'];
        if (!isset($grouped[$id])) {
            $grouped[$id] = [
                'id' => $row['marketplace_id'],
                'participant_id' => $row['participant_id'],
                'firstName' => $row['firstName'],
                'lastName' => $row['lastName'],
                'item_name' => $row['item_name'],
                'price' => $row['price'],
                'location' => $row['location'],
                'condition' => $row['condition'],
                'category' => $row['category'],
                'description' => $row['description'],
                'images' => [],
            ];
        }

        if ($row['image']) {
            $grouped[$id]['images'][] = base64_encode($row['image']);
        }
    }

    return array_values($grouped);
}

function getTrades($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.trade_from_user_id,
            t.trade_to_user_id,
            t.status,
            t.created_at,
            t.updated_at,
            af.firstName AS from_user_name,
            af.lastName AS from_user_last_name,
            mit.location AS from_user_location,
            -- Replace NULL from_item_name with 'purchase'
            IFNULL(mif.item_name, 'Purchase') AS from_item_name,
            mit.item_name AS to_item_name,
            -- Get the first image for both 'from' and 'to' items as BLOB
            (SELECT mi.image FROM marketplace_images mi WHERE mi.marketplace_id = mif.id LIMIT 1) AS from_item_blob,
            (SELECT mi.image FROM marketplace_images mi WHERE mi.marketplace_id = mit.id LIMIT 1) AS to_item_blob
        FROM trades t
        LEFT JOIN account_joiner af ON t.trade_from_user_id = af.id
        LEFT JOIN marketplace mif ON t.trade_from_item_id = mif.id
        LEFT JOIN marketplace mit ON t.trade_to_item_id = mit.id
        WHERE t.trade_to_user_id = :userId
          AND t.status = 'pending'
    ");
    
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTradeStatus($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.trade_from_user_id,
            t.trade_to_user_id,
            t.status,
            t.created_at,
            t.updated_at,
            af.firstName AS from_user_name,
            af.lastName AS from_user_last_name,
            at.firstName AS to_user_name, 
            at.lastName AS to_user_last_name, 
            mit.location AS from_user_location, 
            -- Replace NULL from_item_name with 'Purchase'
            IFNULL(mif.item_name, 'Purchase') AS from_item_name,
            mif.location AS from_item_location,
            mit.item_name AS to_item_name,
            mit.location AS to_item_location, -- Location of the 'to' item
            -- Get the first image for both 'from' and 'to' items as BLOB
            (SELECT mi.image FROM marketplace_images mi WHERE mi.marketplace_id = mif.id LIMIT 1) AS from_item_blob,
            (SELECT mi.image FROM marketplace_images mi WHERE mi.marketplace_id = mit.id LIMIT 1) AS to_item_blob
        FROM trades t
        LEFT JOIN account_joiner af ON t.trade_from_user_id = af.id
        LEFT JOIN account_joiner at ON t.trade_to_user_id = at.id  -- Join for trade-to user details
        LEFT JOIN marketplace mif ON t.trade_from_item_id = mif.id
        LEFT JOIN marketplace mit ON t.trade_to_item_id = mit.id
        WHERE t.trade_from_user_id = :userId
    ");
    
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRatedActivities($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT 
            f.participant_id, 
            f.participant_name, 
            f.activity_id, 
            a.activity_name,
            f.rating
        FROM forum f
        INNER JOIN activities a ON f.activity_id = a.id
        WHERE f.participant_id = :userId
    ");
    
    $stmt->execute(['userId' => $userId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $results;
}

function rejectRefundRequest($pdo, $participantId, $activityId){
    $stmt1 = $pdo->prepare("UPDATE participants SET notified = 'yes', refund = 'no' WHERE participant_id = ? AND activity_id = ?");
    $stmt1->execute([$participantId, $activityId]);

    return true;
}














