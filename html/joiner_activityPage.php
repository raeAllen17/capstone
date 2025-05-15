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

$currentActivities = getCurrentActivities($pdo, $userId);
$participants = getNotification($pdo, $userId);
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

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['rate'])){
        $orgId = $_POST['org_id'];
        $activityId = $_POST['activity_id'];
        $participantName = $userData['firstName'];
        $message = $_POST['message'];
        $rating = $_POST['rating'];

        rateActivity($pdo, $userId, $orgId, $activityId, $message, $rating, $participantName);
    } else if (isset($_POST['refund'])){
        $activityId = $_POST['activityId'];
        $result = setRefundYes($pdo, $userId, $activityId);
        $_SESSION['success_message'] = $result['message'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else if (isset($_POST['check']) && isset($_POST['participant_id']) && isset($_POST['activity_id'])) {
        if (isset($_POST['participant_id'])) {
            $participantId = $_POST['participant_id'];
            $activityId = $_POST['activity_id'];
            $orgId = $_POST['org_id'];
            updateParticipantStatus($pdo, $participantId, $activityId);
            updateParticipantNumber($pdo, $activityId);
            insertNotification($pdo, $activityId, $orgId, $participantId);

            $stmt = $pdo->prepare("SELECT activity_name FROM activities WHERE id = ?");
            $stmt->execute([$activityId]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($activity) {
                $activityName = $activity['activity_name'];
                $message = "Congrats! You are now part of <b>$activityName</b> activity";
            
                $stmt = $pdo->prepare("INSERT INTO notification_joiner (participant_id, message) VALUES (?, ?)");
                $stmt->execute([$participantId, $message]);
            
                $_SESSION['success_message'] = "Notice successful.";
            } else {
                $_SESSION['error_message'] = "Activity not found.";
            }

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
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
<body style="height: 100vh; ">
    <span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
    <span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>   

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
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='joiner_account.php';">  
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>          
    </nav>

    <div class="container" style=" height: 100%; width: 100%; padding-top: 7vh; position: relative; ">
        <div class="container-wrap" style="height: 100%; width: 100%; padding: 7vh; position: absolute; left: 50%; transform: translate(-50%); display: flex; flex-direction: column; align-items: center; background: linear-gradient(to right, #BEE3F8, #A6D8D3, #C9E6C7, #E3F9D8); gap: 2vw;">           
            <div style=" display: flex; justify-content: center; width: 100%; height: auto; gap: 3vw;">
                <div style=" height: auto; width: 50%;">
                    <h1>Past Activities</h1>
                    <div style="min-height: 300px; width: 100%; box-shadow: 0 4px 12px rgba(255, 165, 0, 0.3); border-radius: 20px; overflow: hidden; background-color: whitesmoke;">
                        <?php if (!empty($allActivityDetails)): ?>
                            <?php foreach ($allActivityDetails as $row): ?>
                                <div style=" width: 100%; display: flex; justify-content: space-between; align-items:center; margin: 1vw 0vw; border-bottom: 1px solid grey; padding: 1vw 2vw; background-color: rgba(245, 245, 245, 0.5);">
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
                            <p style="padding: 1vw 2vw;"><?php echo "No past activities found."; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <br>
                <div style="height: auto; width: 50%;">
                    <h1>Current Activities</h1>
                    <div style="min-height: 300px; width: 100%; box-shadow: 0 4px 12px rgba(144, 238, 144, 0.3); border-radius: 20px; overflow: hidden; background-color: whitesmoke;">
                        <?php if ($currentActivities['success']): ?>
                            <?php foreach ($currentActivities['data'] as $activity): ?>
                                <form action="" method="POST">
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 0.3px solid black; padding: 1vw 2vw; background-color: rgba(245, 245, 245, 0.5);">
                                        <?php echo htmlspecialchars($activity['activity_name']); ?>
                                        <input type="hidden" name="activityId" value="<?php echo htmlspecialchars($activity['id']); ?>">
                                        <button class="button-buttons" name="refund">Refund</button>
                                    </div>
                                </form>     
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 1vw 2vw;">No current activities available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div style=" height: auto; width: 100%;">
                <h1 style="width: 30%; text-align: left;">Confirmations</h1>
                <div style=" min-height: 300px; width: 600px; box-shadow: 0 4px 12px rgba(100, 149, 237, 0.3); border-radius: 20px; background-color: whitesmoke;">
                    <table>
                        <tbody>
                            <?php if ($participants && count($participants) > 0): ?>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($participant['activity_name'])?></td>
                                        <td style=" padding-left: 10%;">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($participant['participant_id']); ?>">
                                                <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($participant['activity_id']); ?>">
                                                <input type="hidden" name="org_id" value="<?php echo htmlspecialchars($participant['org_id']); ?>">
                                                <button class="button-buttons" name="check" style="background: url('../imgs/icon_check.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button> 
                                            </form>       
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" style="padding: 1vw 2vw;">No confirmations found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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