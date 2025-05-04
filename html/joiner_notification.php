<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

if(isset($_SESSION['id'])){
    $userId = $_SESSION['id']; 
    $userData = getJoinerUserdata($pdo, $userId);
    $joinerName = $userData['firstName'];
} else {

}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['check']) && isset($_POST['participant_id']) && isset($_POST['activity_id'])) {
        if (isset($_POST['participant_id'])) {
            $participantId = $_POST['participant_id'];
            $activityId = $_POST['activity_id'];
            updateParticipantStatus($pdo, $participantId, $activityId);
            updateParticipantNumber($pdo, $activityId);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

$participants = getNotification($pdo, $userId);
$cancelledNotification = getNotificationCancelled($pdo, $userId)

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 

    <style>
        table {
            width: 100%;
        }
        table, tbody {
            border-collapse: collapse;
        }
        td {
            border-bottom: 1px solid black;
            padding: 0.5vw 0vw;
            text-align: left;
        }
        th {
            font-size: 1.8vh;
            text-align: left;
        }
        .button-buttons {
            padding: 5px 10px;
            background-color: ;
            border: none;
            background-color: #A9BA9D;
            border-radius: 5px;
            transition: transform 0.2s ease;
            color: white;
        }
        .button-buttons:hover {
            background-color:#8A9A5B;
            transform: scale(1.05);
        }
    </style>
</head>
<body style=" height: 100vh;">
    <nav id="nav">
            <div class="nav_left" style=" positition: relative;">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li><a href="joiner_homePage.php" >Home</a></li>
                    <li><a href="joiner_activityPage.php">Activity</a></li>
                    <li><a href="joiner_forumPage.php" >Forum</a></li>
                    <li><a href="joiner_marketplace.php" >Marketplace</a></li>
                    <li style=" border-bottom: 2px solid green;"><a href="joiner_notification.php" >Notification</a></li>
                </ul>
            </div>
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='walapa.php';">
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>
    </nav>

    <div style=" padding-top: 7vh; height: 100%; width:  100%; display: flex; flex-direction: column;">
        <div style=" height: 100%; width: 100%; padding: 2vw; display: flex; justify-content: center; flex-direction: column; align-items: center;">
            <h1>Request Notifications</h1>
            <div style=" height: 300px; width: 600px; border: 2px solid black; border-radius: 20px; padding: 2vw;">
                <table>
                    <thead>
                        <tr>
                            <th>Activity Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($participants && count($participants) > 0): ?>
                            <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['activity_name'])?></td>
                                    <td style=" padding-left: 10%;">
                                        <form action="" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($participant['participant_id']); ?>">
                                            <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($participant['id']); ?>">
                                            <button class="button-buttons" name="check" style="background: url('../imgs/icon_check.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button> 
                                        </form>       
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2">No participants found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div style=" height: 100%; width: 100%; padding: 2vw; display: flex; justify-content: center; flex-direction: column; align-items: center;">
            <h1>Cancellation Notifications</h1>
            <div style=" height: 300px; width: 600px; border: 2px solid black; border-radius: 20px; padding: 2vw;">
            <table>
                    <thead>
                        <tr>
                            <th>Activity Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cancelledNotification && count($cancelledNotification) > 0): ?>
                            <?php foreach ($cancelledNotification as $cancelledNotifs): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cancelledNotifs['activity_name'])?></td>
                                    <td><p>Kindly resend your registration.</p></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2">No participants found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>