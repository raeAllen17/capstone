<?php
require_once 'includes/dbCon.php'; // Include your database connection
require 'includes/activity_store.php'; // Include your activity functions

// Check if the ID is set in the URL

if (isset($_GET['id'])) {

    $activityId = intval($_GET['id']); // Get the ID and ensure it's an integer

    // Fetch activity details from the database

    $activities = getactivities($pdo, $activityId); // Fetch activity details


    // Check if activity details were retrieved successfully

    if (!$activities) {

        $activities = []; // Set to empty if not found

    }

} else {

    // Redirect or handle the case where no ID is provided

    header("Location: error_page.php"); // Redirect to an error page or handle accordingly

    exit();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Details</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 
</head>
<body>

    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li style=" border-bottom: 2px solid green;"><a href="" >Home</a></li>
                    <li><a href="" >Activity</a></li>
                    <li><a href="" >Forum</a></li>
                    <li><a href="" >Marketplace</a></li>
                    <li><a href="" >Notification</a></li>
                </ul>
            </div>
            <div class="nav_right">                 
            </div>          
    </nav>

    <div class="container" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; width: 100%; gap: 45px;">
        <div style="width: 1600px; font-size: 1.5em;">
            <h1 style="color: rgb(0, 80 ,0); font-size: 2.3em;">Select and join the <br> adventure now!</h1>
        </div>    
        <div class="activities-list" style="width: 1600px;">
            <?php if (!empty($activities)): ?>
                <div style="background-color: gray; height: 500px; width: 1600px; border-radius: 20px; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                    <div style="background-image: linear-gradient(to right, rgba(0,0,0,1), rgba(255,0,0,0)); height: 500px; width: 900px; 
                    border-right: 2px solid red; display: flex; flex-direction: column; gap: 50px;">
                        <div style=" color: white; padding-left: 50px; padding-top: 50px; display: flex; align-items: center; gap: 20px;">
                            <h1><?php echo htmlspecialchars($activities['activity_name']); ?></h1>
                            <p><?php echo htmlspecialchars($activities['orgname']); ?></p>
                        </div>
                        <div style="color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; width: 600px; padding: 20px; text-align: center;">
                            <p style="white-space: normal; line-height: 1.5; margin: 0; font-size: 1.2em; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);"> 
                                <?php echo htmlspecialchars($activities['description']); ?>
                            </p>
                        </div>
                        <div style="color: white; width: 900px; display: flex; justify-content: center; align-items: center; gap: 70px;">
                            <h2><?php echo htmlspecialchars($activities['difficulty']); ?></h2>
                            <h2><?php echo htmlspecialchars($activities['distance']); ?></h2>
                            <h2><?php echo htmlspecialchars($activities['participants']); ?>/<?php echo htmlspecialchars($activities['counter']); ?></h2>
                            <h2><?php echo htmlspecialchars($activities['date']); ?></h2>
                        </div>
                        <div>
                            <button type="button" class="button">3D MAP</button>
                        </div>
                    </div>
                    <div style="background-image: linear-gradient(to right, rgba(0,0,0,0), rgba(210,210,210,1)); height:500px; width: 700px;">

                    </div>
                </div>
            <?php else: ?>
                <h1>Sorry, no activities available at this time.</h1>
                <p style="color:gray;">Try refreshing the page or logging in again.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>