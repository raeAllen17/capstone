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
    header("location: landing_page.php");
    exit();
}

$data = displayRating($pdo);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 
</head>
<body style=" height:100vh; background-color: white;">
    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li><a href="joiner_homePage.php" >Home</a></li>
                    <li><a href="joiner_activityPage.php">Activity</a></li>
                    <li style=" border-bottom: 2px solid green;"><a href="" >Forum</a></li>
                    <li><a href="joiner_marketplace.php" >Marketplace</a></li>
                    <li><a href="joiner_notification.php" >Notification</a></li>
                </ul>
            </div>
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='walapa.php';">  
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>          
    </nav>

    <div class="container" style=" height: 100%; width: 100%; padding-top: 7vh; position: relative;">
        <div class="container-wrap" style="height: auto; width: 80%; padding: 7vh; position: absolute; left: 50%; transform: translate(-50%); display: flex; flex-direction: column; justify-content: space-between; align-items: center;">
            
            <div style=" height: auto; width: 60%;">
                <h1>Ratings</h1>
                    <?php if($data['success']): ?>
                        <?php foreach( $data['data'] as $row):?>
                            <div style="display: flex; flex-direction: column; width: 100%; border: 2px solid black; border-radius: 10px; padding: 1vw; margin-bottom: 1vw;">
                                <h2><?php echo htmlspecialchars($row['activity_name']); ?></h2>
                                <p><?php echo htmlspecialchars($row['orgname']); ?></p>
                                <p><?php echo htmlspecialchars($row['date']); ?></p>                             
                                <div style=" width: 100%; margin: 10px 0px;">
                                    <h3>Feedbacks</h3>
                                    <div style="width: 100%; border-radius: 10px; border: 1px solid black; padding:1vw ;">
                                    <?php 
                                        $forumEntries = getForumEntriesByActivityId($pdo, $row['id']);
                                        if ($forumEntries['success']): 
                                            foreach ($forumEntries['data'] as $feedback): ?>
                                                <div style="margin-bottom: 10px;">
                                                    <h3><?php echo htmlspecialchars($feedback['participant_name'])?></h3>
                                                    <h4><?php echo htmlspecialchars($feedback['rating'])?></h4>
                                                    <p><?php echo htmlspecialchars($feedback['message'])?></p>
                                                    
                                                </div>
                                            <?php endforeach; 
                                        else: ?>
                                            <p>No feedback available for this activity.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No activity is rated yet.</p>
                    <?php endif;?>
            </div>
        </div>
    </div>
</body>
</html>