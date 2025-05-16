<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

$errorMessage = "";
$successMessage = "";
if (isset($_SESSION['error_message']) && $_SESSION['error_message'] !== "") {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); 
}
if (isset($_SESSION['success_message']) && $_SESSION['success_message'] !== "") {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); 
}

if(isset($_SESSION['id'])){
    $userId = $_SESSION['id']; 
    $userData = getJoinerUserdata($pdo, $userId);
    $joinerName = $userData['firstName'];
} else {
    header("location: landing_page.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    
}
$cancelledNotification = getNotificationJoiner($pdo, $userId)

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
<span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
<span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    

    <nav id="nav" style="background-color: white;">
            <div class="nav_left" style=" positition: relative;">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li><a href="joiner_homePage.php" >Home</a></li>
                    <li><a href="joiner_activityPage.php">Activity</a></li>
                    <li><a href="joiner_forumPage.php" >Forum</a></li>
                    <li><a href="joiner_marketplace.php" >Marketplace</a></li>
                    <li><a href="joiner_notification.php" id="notification-nav">Notification</a></li>
                </ul>
            </div>
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='joiner_account.php';">           
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>
    </nav>

    <div style=" padding-top: 7vh; height: 100%; width: 100%; display: flex; flex-direction: column; background: linear-gradient(to right, #FFE4C4, #F9D6A5, #F1E1C6, #E8E5D7);">
        <div style=" height: 100%; width: 100%; display: flex; padding-top: 2vw; flex-direction: column; align-items: center;">
            <h1 style="width: 35%; text-align: left; margin-bottom: 1vw;">Notifications</h1>
            <div style=" min-height: 300px; width: 700px; box-shadow: 0 4px 12px rgba(100, 149, 237, 0.3); border-radius: 20px; padding: 2vw; background-color: white;">
            <table>
                    <tbody>
                        <?php if ($cancelledNotification && count($cancelledNotification) > 0): ?>
                            <?php foreach ($cancelledNotification as $cancelledNotifs): ?>
                                <tr>
                                    <td><?php echo ($cancelledNotifs['message'])?></td>
                                    <td><strong><?php echo date('H:i', strtotime($cancelledNotifs['created_at'])) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2">No notifications found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
        var errorMsg = document.getElementById("errorMessage");
        var successMsg = document.getElementById("successMessage");

        if (errorMsg.innerHTML.trim() !== "") {
            errorMsg.style.display = "flex";
            setTimeout(() => {
            errorMsg.style.display = "none";
            errorMsg.innerHTML = "";
        }, 2000);
        }

        if (successMsg.innerHTML.trim() !== "") {
            successMsg.style.display = "flex";
            setTimeout(() => {
            successMsg.style.display = "none";
            successMsg.innerHTML = ""; 
        }, 2000);
        }
        });
    </script>
</body>
</html>