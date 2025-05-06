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

$ActiveActivities = getActiveActivites($pdo, $userId);
$allActivityDetails = []; 

if (!empty($ActiveActivities)) {
    foreach ($ActiveActivities as $activityId) {
        $details = getActivityDetails($pdo, $activityId);
        if ($details['success']) {
            $allActivityDetails = array_merge($allActivityDetails, $details['data']); // Merge the data into the array
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['rate'])){
        $orgId = $_POST['org_id'];
        $activityId = $_POST['activity_id'];
        $participantName = $userData['firstName'];
        $message = $_POST['message'];
        $rating = $_POST['rating'];

        rateActivity($pdo, $userId, $orgId, $activityId, $message, $rating, $participantName);
    }
}

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
<body>
    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li ><a href="joiner_homePage.php" >Home</a></li>
                    <li style=" border-bottom: 2px solid green;"><a href="">Activity</a></li>
                    <li><a href="joiner_forumPage.php">Forum</a></li>
                    <li><a href="joiner_marketplace.php">Marketplace</a></li>
                    <li><a href="joiner_notification.php">Notification</a></li>
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
                <h1>Past Activities</h1>
                <div style=" height:auto; width: 100%; border: 2px solid black; border-radius: 10px; padding: 1vw;">
                    <?php if (!empty($allActivityDetails)): ?>
                        <?php foreach ($allActivityDetails as $row): ?>
                            <div style=" width: 100%; display: flex; justify-content: space-between; align-items:center; margin: 1vw 0vw; border-bottom: 1px solid grey; padding: 1vw 0vw;">
                                <div style=" width: 60%; display: flex; justify-content: space-between;">
                                    <h3><?php echo htmlspecialchars($row['activity_name']); ?></h3>
                                    <h3><?php echo htmlspecialchars($row['date']); ?></h3>
                                </div>                       
                                <div>
                                    <button class="button-buttons" type="button" onclick="showModal(event, 'modal-overlay', '<?php  echo htmlspecialchars($row['org_id']);?>', '<?php  echo htmlspecialchars($row['id']);?>')">Rate</button>
                                </div>                                       
                            </div>                                   
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><?php echo "No past activities found."; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-overlay" style="display: none; justify-content: center; align-items: center; height:100%; width: 100%; background-color: rgba(0,0,0,0.5); position: absolute; top: 0; left:0;">
        <div style="height: auto; width: 500px;  border: 2px solid black; background-color: white; border-radius: 20px; display: flex; flex-direction: column; align-items: center; padding: 1vw;">
            <div style="width: 90%;">   
                
            </div>
            <div style=" width:100%; display:flex; justify-content: center;">
                <form action="" method="POST" style="width: 100%; padding: 1vw;">
                    <div style="width: 100%; margin: 1vw 0vw;">
                        <h4>Comment</h4>
                        <span><textarea name="message" id="message" cols="30" rows="10" style="resize: none; width: 100%; padding : 1vw;"></textarea></span>
                        <h4>Rating</h4>
                        <select name="rating" id="rating" style=" width: 50%;">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div style=" width:100%; display:flex; justify-content: flex-end;">
                        <input type="hidden" id="orgId" name="org_id" value="">
                        <input type="hidden" id="activityId" name="activity_id" value="">
                        <button class="button-buttons" name="rate" type="submit">Submit</button>
                    </div> 
                </form>     
            </div>
        </div>
    </div>

    <script>

        function showModal(event, modalId, orgId, activityId){
            event.preventDefault();
            document.getElementById('orgId').value = orgId;
            document.getElementById('activityId').value = activityId;
            const popup = document.getElementById(modalId);
            popup.style.display = "flex";
        }

    </script>

</body>
</html>