<?php
session_start();
require_once 'dbCon.php';
require_once 'activity_store.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../activityDetails.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['proof-image'])) {
    $org_id = $_POST['org_id'];
    $activity_id = $_POST['activity_id'];
    $joiner_id = $_SESSION['id'];

    $userData = [
        'user_id' => $joiner_id,
        'activity_id' => $activity_id,
        'org_id' => $org_id
    ];

    $result = actRegis($pdo, $userData, $_FILES['proof-image']);

    if ($result['success']) {
        $_SESSION['success_message'] = "Your reservation is up for approval!";
    } else {
        $_SESSION['error_message'] = "Registration unsuccessful!";
    }

    header("Location: ../activityDetails.php?id=" . urlencode($activity_id));
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: ../activityDetails.php");
    exit();
}
?>