<?php
session_start();
require 'dbCon.php';
require 'activity_store.php';


if (!isset($_SESSION['id'])) {
    header('Location: ../activityDetails.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $org_id = $_POST['org_id'];
    $activity_id = $_POST['activity_id'];
    $joiner_id = $_SESSION['id'];

    $stmt = $pdo->prepare("SELECT status, notified FROM participants WHERE participant_id = :participant_id AND activity_id = :activity_id");
    $stmt->bindParam(':participant_id', $joiner_id, PDO::PARAM_INT);
    $stmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
    $stmt->execute();
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    $currentStatus = $participant['status'];
    $notified = $participant['notified'];

    $userData = [
        'user_id' => $joiner_id,
        'activity_id' => $activity_id,
        'org_id' => $org_id
    ];

    if ($currentStatus === 'active') {
        $_SESSION['error_message'] = "You already joined this activity.";
    } elseif ($currentStatus === 'pending' && $notified === 'no') {
        $_SESSION['error_message'] = "Registration already pending, please wait for confirmation notification.";
    } elseif ($currentStatus === 'pending' && $notified === 'cancel') {
        $result = actRegisUpdate($pdo, $userData, $_FILES['proof-image']); 
        if ($result['success']) {
            $_SESSION['success_message'] = "Your request has been updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update your request: " . htmlspecialchars($result['message']);
        }
    } elseif ($currentStatus === 'waitlist') {
        $result = actRegisUpdate($pdo, $userData, $_FILES['proof-image']);
        if ($result['success']) {
            $_SESSION['success_message'] = "Your request has been updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update your request: " . htmlspecialchars($result['message']);
        }
    } else {
        if (isset($_FILES['proof-image']) && $_FILES['proof-image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $result = actRegis($pdo, $userData, $_FILES['proof-image']);
        } else {
            $result = actRegis($pdo, $userData, null);
        }

        if ($result['success']) {
            $_SESSION['success_message'] = "Your reservation is up for approval!";
        } else {
            $_SESSION['error_message'] = "Registration unsuccessful!";
        }
    }
    header("Location: ../activityDetails.php?id=" . urlencode($activity_id));
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: ../activityDetails.php");
    exit();
}
