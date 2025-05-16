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
<body style=" min-height:100vh; background: linear-gradient(140deg, #CBD5D9, #CDECC9, #FFFFFF); background-repeat: no-repeat;">
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
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='joiner_account.php';">  
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>          
    </nav>

    <div class="container" style=" height: 100%; width: 100%; position: relative; padding-top:7vh; display: flex; flex-direction: column; align-items: center;">
        <div class="container-wrap" style=" padding-top:1vw; height: auto; width: 40%;">
        <h1 style="font-size: 3rem;">Ratings</h1>
        <?php if ($data['success'] && count($data['data']) > 0): ?>
            <div style="">
                <?php foreach ($data['data'] as $index => $activity): ?>
                    <div style=" box-shadow: 0 6px 20px rgba(0, 0, 128, 0.7); margin: 1vw 0; border-radius: 20px; overflow: hidden;">
                        <div style=" background-color: #154472; color: azure; padding: 0.5vw 1vw; border-top-right-radius: 18px; border-top-left-radius: 18px;">
                            <div style=" margin: 1vw 0vw;">
                                <h2><?php echo htmlspecialchars($activity['activity_name']); ?></h2>
                                <p> <?php echo htmlspecialchars($activity['date']); ?></p>  
                                <?php
                                $orgId = $activity['org_id']; 
                                $orgData = getUserdata($pdo, $orgId); 
                                ?>
                                <div style="display: flex; gap: 1vw; align-items: center; margin: 1vw 0vw;">
                                    <img src="<?php echo isset($orgData['avatar']) ? 'data:image/jpeg;base64,' . base64_encode($orgData['avatar']) : '../imgs/defaultuser.png'; ?>" 
                                        alt="Avatar" 
                                        style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover;">
                                    <p style="font-weight: bold; color: goldenrod;"><?php echo htmlspecialchars($orgData['orgname']); ?></p>                  
                                </div>
                            </div>

                            <div id="slideshow-<?php echo $index; ?>" class="slideshow" style="height:400px; width:100%; border-radius: 5px;"></div>
                            <?php 
                                // Prepare images
                                $images = [];
                                if (!empty($activity['images'])) {
                                    $paths = explode(',', $activity['images']);
                                    foreach ($paths as $path) {
                                        $basename = basename(trim($path));
                                        $images[] = '../uploads/' . $basename;
                                    }
                                }
                            ?>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    const images<?php echo $index; ?> = <?php echo json_encode($images); ?>;
                                    const slideshow<?php echo $index; ?> = document.getElementById("slideshow-<?php echo $index; ?>");

                                    if (slideshow<?php echo $index; ?> && images<?php echo $index; ?>.length > 0) {
                                        let i = 0;
                                        function showSlide<?php echo $index; ?>() {
                                            slideshow<?php echo $index; ?>.style.backgroundImage = `url('${images<?php echo $index; ?>[i]}')`;
                                            i = (i + 1) % images<?php echo $index; ?>.length;
                                            setTimeout(showSlide<?php echo $index; ?>, 2000);
                                        }
                                        showSlide<?php echo $index; ?>();
                                    }
                                });
                            </script>
                        </div>                   
                        <?php 
                            $feedbacks = getForumEntriesByActivityId($pdo, $activity['id']);
                            if ($feedbacks['success'] && count($feedbacks['data']) > 0): 
                                foreach ($feedbacks['data'] as $entry): ?>
                                    <div style="padding: 0.8vw 1vw; display: flex; gap: 1vw; align-items: flex-start; background-color: white;">
                                        <div>
                                            <img src="<?php echo isset($entry['avatar']) ? 'data:image/jpeg;base64,' . base64_encode($entry['avatar']) : '../imgs/defaultuser.png'; ?>" 
                                            alt="Avatar" 
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        </div>
                                        <div style=" padding: 0.5vw 1vw; background-color: #CBD5D9; width: 100%; border-radius: 20px;">
                                            <h4><?php echo htmlspecialchars($entry['participant_name']); ?>
                                            <?php
                                            $rating = (int)$entry['rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rating ? '⭐' : '✩';
                                            }
                                            ?></h4>
                                            <p><?php echo htmlspecialchars($entry['message']); ?></p>
                                        </div>                                      
                                    </div>
                                <?php endforeach;
                                else: ?>
                                <p style=" padding: 1vw;">No feedback for this activity yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>  
            </div>
        <?php else: ?>
            <p>No activities with feedback found.</p>
        <?php endif; ?>
    </div>

</body>
</html>