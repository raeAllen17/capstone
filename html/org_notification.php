<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id']; 
    $userData = getUserdata($pdo, $userId);
    $orgname = $userData['orgname'];
} else {
    session_unset();
    session_destroy();
    header('location: landing_page.php');
    exit;
}

$notifications = getNotificationOrg($pdo, $userId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 
</head>
<body>
    <nav id="nav" style="background-color: white;">
        <div class="nav_left">
            <ul class="navbar">
                <li><input type="button" class="logo"></li>
                <li><a href="org_homePage.php">Home</a></li>
                <li><a href="org_createAct.php">Activity</a></li>
                <li><a href="org_forumPage.php">Forum</a></li>
                <li><a href="org_marketplace.php">Marketplace</a></li>
                <li  style=" border-bottom: 2px solid green;"><a href="org_notification.php">Notification</a></li>
            </ul>
        </div>
        <div class="nav_right" id="nav_right_click" onclick="window.location.href='org_account.php';">  
            <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
            <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($orgname); ?></span>
        </div>         
    </nav>

    <div style=" padding-top: 7vh; height: 100%; width:  100%; display: flex; flex-direction: column;">
        <div style=" height: 100%; width: 100%; padding: 2vw; display: flex; justify-content: center; flex-direction: column; align-items: center;">
            <h1>Notifications</h1>
            <div style=" height: 300px; width: 600px; border: 2px solid black; border-radius: 20px; padding: 2vw;">
                <div>
                    <?php if ($notifications && count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div style=" display: flex; justify-content: space-between; width: 100%; border-bottom: 1px solid black; padding-bottom: 1vh; margin-top: 1vh;">
                                <p><?php echo htmlspecialchars($notification['message'])?></p>
                                <p><strong><?php echo date('H:i:s', strtotime($notification['created_at'])); ?></strong></p>
                            </div> 
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No notifications found.</td>
                        </tr>
                    <?php endif; ?>
                </div>       
            </div>
        </div>
    </div>
    
</body>
</html>