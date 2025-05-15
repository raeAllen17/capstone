<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

if (isset($_GET['id'])){
    $activityId = $_GET['id'];
} 

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

//SESSION MESSAGES
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


//fetching data functions
$participants = getParticipantRequest($pdo, $userId, $activityId);
$waitlists = getWaitlistRequest($pdo, $userId, $activityId);
$actives = getActiveParticipants($pdo, $userId, $activityId);
$refunds = getRefundRequest($pdo, $userId, $activityId);
$activities = getactivities($pdo, $activityId);
$activityName = $activities['activity_name'];

//implode values with comma or space from activities
$distance_value = '';
$distance_unit = 'km';
$pickupPoints = '';

if (!empty($activities['pickup_locations'])) {
    $pickupPoints = $activities['pickup_locations']; 
} else {
    $pickupPoints = ''; 
}

$pickupPointsJson = htmlspecialchars($pickupPoints);

if (!empty($activities['distance'])) {
    list($distance_value, $distance_unit) = explode(' ', $activities['distance']);
}

//updating notified status
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['check']) && isset($_POST['participant_id']) && isset($_POST['activity_id'])) {
        if (isset($_POST['participant_id'])) {
            $participantId = $_POST['participant_id'];
            $activityId = $_POST['activity_id'];
            updateNotified($pdo, $participantId, $activityId);
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($activityId));
            exit();
        }
    } else if (isset($_POST['cross'])){
        if (isset($_POST['participant_id'])) {
            $participantId = $_POST['participant_id'];
            $activityId = $_POST['activity_id'];
            rejectRequest($pdo, $participantId, $activityId);
            
            //insert notification
            $stmt = $pdo->prepare("SELECT activity_name FROM activities WHERE id = ?");
            $stmt->execute([$activityId]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($activity) {
                $activityName = $activity['activity_name'];
                $message = "<b>$activityName</b> :Proof of payment unclear or downpayment did not meet the minimum value. Kindly resend your proof of payment.";
            
                $stmt = $pdo->prepare("INSERT INTO notification_joiner (participant_id, message) VALUES (?, ?)");
                $stmt->execute([$participantId, $message]);
            
                $_SESSION['success_message'] = "Notice successful.";
            } else {
                $_SESSION['error_message'] = "Activity not found.";
            }

            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($activityId));
            exit();
        }
    } else if (isset($_POST['notify'])){
        if (isset($_POST['participant_id'])) {
            $participantId = $_POST['participant_id'];
            $activityId = $_POST['activity_id'];
            
            $result = notifyParticipant($pdo, $participantId, $activityId);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        } else {
            $_SESSION['error_message'] = "participant unset.";
        }
    } else if (isset($_POST['notify_date_all'])) {
        if (isset($actives) && is_array($actives)) {
            foreach ($actives as $active) {
                $participantId = $active['participant_id'];
                $result = getParticipantEmail($pdo, $participantId); 
    
                if ($result && isset($result['email'], $result['firstName'], $result['lastName'])) {
                    $email = $result['email'];
                    $firstName = $result['firstName'];
                    $lastName = $result['lastName'];
    
                    $subject = "Your activity $activityName happening soon!";
                    $message = "Dear $firstName $lastName,<br><br>We would like to inform you about your participation in the activity <strong>$activityName</strong>.<br><br>Best regards,<br>JOYn";
    
                    if ($email) {
                        sendActivityReminderEmail($email, $subject, $message);
                        $_SESSION['success_message'] = "All users notified.";
                        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($activityId));
                        exit();
                    }
                } else {
                    echo "Could not retrieve email for participant ID: $participantId.";
                }
            }
        } else {
            echo "No active participants found.";
        }
    } else if (isset($_POST['notify_date'])){
        if (isset($_POST['participant_id'])){
            $participantId = $_POST['participant_id'];
            $result = getParticipantEmail($pdo, $participantId);
            if ($result && isset($result['email'], $result['firstName'], $result['lastName'])) {
                $email = $result['email'];
                $firstName = $result['firstName'];
                $lastName = $result['lastName'];
    
                $subject = "Your activity $activityName happening soon!";
                $message = "Dear $firstName $lastName,<br><br>We would like to inform you about your participation in the activity <strong>$activityName</strong>.<br><br>Best regards,<br>JOYn";
    
                if ($email) {
                    sendActivityReminderEmail($email, $subject, $message);
                    $_SESSION['success_message'] = "User notified."; 
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($activityId));
                    exit();
                }
            } else {
                echo "Could not retrieve email for participant ID: $participantId.";
            }
        }  
  
    } else if (isset($_POST['update_activityStatus'])){
        $activityId = $_GET['id'];
        $result= updateActivityStatus($pdo, $activityId);

        if ($result['success']){
            header("Location: org_homePage.php");
            exit();
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($activityId));
            exit();
        }
    } else if (isset($_POST['accept-refund'])){
        $activityId = $_GET['id'];
        $participantId = $_POST['participant_id'];

        updateRefundRequest($pdo, $participantId, $activityId);
        $_SESSION['success_message'] = "User successfully refunded.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($activityId));
        exit();
    } else if (isset($_POST['save-button'])) {
        $data = [
            'activity_name'     => $_POST['activity_name'],
            'description'       => $_POST['description'],
            'location'          => $_POST['location'],
            'date'              => $_POST['date'],
            'distance'          => $_POST['distance'],
            'difficulty'        => $_POST['difficulty'],
            'price'             => $_POST['price'],
            'participants'      => $_POST['participants'],
            'pickup_locations'  => $_POST['pickup_locations'],
        ];
    
        $result = updateActivityDetails($pdo, $userId, $activityId, $data);
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($activityId));
        exit();
        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css">
    
    <style>
        .link-button {
            display: inline-block;
            text-decoration: none;
            color: white;
            padding: 10px;
            border-radius: 20px;
            background: #6666FF;
        } 
        .link-button:hover {
            background: #2828FA;
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
        table {
            width: 100%;
        }
        table, tbody {
            border-collapse: collapse;
        }
        td {
            border-bottom: 1px solid black;
            padding: 15px;
            text-align: center;
        }
        th {
            font-size: 1.8vh;
        }
        .input_fields{
            padding: 15px;
            border: 2px solid black;
            border-radius: 10px;
            width: 350px;
        }
        .div-space{
            margin-top: 1vw;
        }
        ::-webkit-scrollbar {
            width: 10px; 
            appearance: none;
        }
        ::-webkit-scrollbar-track {
            background: transparent; 
            margin: 20px 0; 
        }
        ::-webkit-scrollbar-thumb {
            height: 10px;
            background-color: rgba(0, 0, 0, 0.5); 
            border-radius: 20px;
        }

    </style>
</head>
<body style="background-color: white; height: 100vh; width: 100%;">
    <span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
    <span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    
    
    <nav id="nav" style="background-color: white;">
        <div class="nav_left">
            <ul class="navbar">
                <li><input type="button" class="logo"></li>
                <li style=" border-bottom: 2px solid green;"><a href="org_homePage.php">Home</a></li>
                <li><a href="org_createAct.php">Activity</a></li>
                <li><a href="org_forumPage.php">Forum</a></li>
                <li><a href="org_marketplace.php">Marketplace</a></li>
                <li><a href="org_notification.php">Notification</a></li>
            </ul>
        </div>
        <div class="nav_right" id="nav_right_click" onclick="window.location.href='org_account.php';">  
            <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
            <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($orgname); ?></span>
        </div>         
    </nav>

    <div class="container" style=" padding-top: 7vh; width: 100%; display: flex; justify-content: center; flex-grow:1 ;">
        <div class="container-wrap" style="height: auto; width: 100%; padding: 3vh; display: flex; flex-direction: column; align-items: center; gap: 2vw;">
            <form action="" method="POST"id="activity-form" style="width: 100%; display: flex; justify-content: center;">
                <div style="position: relative;width: 70%; height: auto; box-shadow: 1px 2px 6px 0.1px; padding: 2vw; border-radius: 20px; display: flex; flex-direction: column; gap: 1vw; background-color: #A9BA9D;">
                <h1 style=" color: azure;">Manage your activity!</h1>
                    <div style="width: 100%; height:100%; border: 2px solid black; border-radius: 10px; padding: 2vw; background-color: azure;">          
                        <?php if(!empty($activities)): ?>  
                            <div style=" width: 100%; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;">
                                <div class="div-space">
                                    <p>Activity Name</p>
                                    <input readonly type="text" class="input_fields" name="activity_name" required value="<?php echo htmlspecialchars($activities['activity_name']);?>">
                                </div>                      
                                <div class="div-space">
                                    <p>Location</p>
                                    <input readonly type="text" class="input_fields" name="location" required value="<?php echo htmlspecialchars($activities['location'])?>">
                                </div>
                                <div class="div-space">
                                    <p>Date</p>
                                    <input readonly type="date" class="input_fields" name="date" required value="<?php echo htmlspecialchars($activities['date'])?>">
                                </div>
                                <div class="div-space">
                                    <p>Distance</p>
                                    <div style="position: relative;">
                                        <input readonly  type="number" step="0.1" id="distance_value" style="padding-right: 45px;" class="input_fields" required value="<?php echo htmlspecialchars($distance_value)?>">
                                        <select id="distance_unit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; -webkit-appearance: none; -moz-appearance: none; appearance: none;">
                                            <option value="km" <?php echo ($distance_unit === 'km') ? 'selected' : ''; ?>>km</option>
                                            <option value="miles" <?php echo ($distance_unit === 'miles') ? 'selected' : ''; ?>>miles</option>
                                        </select>
                                        <input type="hidden" name="distance" id="combined_distance">
                                    </div>
                                </div>
                                <div class="div-space">
                                    <p>Price</p>
                                    <div style="position: relative;">
                                        <input readonly type="number" step="0.1" id="price_value" style="padding-right: 45px;" class="input_fields" required value="<?php echo htmlspecialchars($activities['price'])?>">
                                        <select id="currency_symbol" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; -webkit-appearance: none; -moz-appearance: none; appearance: none;">
                                            <option value="PHP">₱</option>
                                        </select>
                                        <input type="hidden" name="price" id="final_price">
                                    </div>
                                </div>
                                <div class="div-space">
                                    <p>Difficulty</p>
                                    <select disabled class="input_fields" id="difficultySelect" name="difficulty_display">
                                        <option value="" disabled selected><?php echo htmlspecialchars($activities['difficulty'])?></option>
                                        <option value="Easy">Easy</option>
                                        <option value="Moderate">Moderate</option>
                                        <option value="Challenging">Challenging</option>
                                        <option value="Difficult">Difficult</option>
                                        <option value="Expert">Expert</option>
                                    </select>
                                    <input type="hidden" name="difficulty" id="difficultyHidden" value="<?php echo $activities['difficulty']; ?>">
                                </div>
                                <div class="div-space">
                                    <p>Participants</p>
                                    <input readonly type="number" class="input_fields" name="participants" required value="<?php echo htmlspecialchars($activities['participants'])?>">
                                </div>
                                <div class="div-space">
                                    <p>Pickup Points</p>
                                    <div style="display: flex; position: relative;">
                                        <input readonly type="text" id="pickup_input" class="input_fields" style="padding-right: 80px; color: black;" placeholder="Enter pickup point">
                                        <button type="button" id="add_pickup_btn" style="position: absolute; right: 0; top: 0; height: 100%; width: 40px; color: black; background-color: transparent; border: none; border-radius: 0 10px 10px 0; cursor: pointer; font-size: 20px;">+</button>
                                        <button type="button" id="view_pickup_btn" style="position: absolute; right: 45px; top: 0; height: 100%; width: 40px; color: black; background-color: transparent; border: none; cursor: pointer; font-size: 16px;">▼</button>
                                    </div>
                                    <div id="pickup_dropdown" style="display: none; position: absolute; background-color: white; border: 1px solid #ddd; max-height: 100px; overflow-y: scroll;width: 300px; z-index: 10; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                        <ul id="pickup_list" style="list-style: none; padding: 0; margin: 0;"></ul>
                                    </div>
                                    <input type="hidden" name="pickup_locations" id="pickup_locations_hidden" value='<?php echo $pickupPointsJson; ?>'>
                                </div>                           
                            </div>
                            <div class="div-space">
                                <p>Description</p>
                                <span>
                                    <textarea class="input-fields" disabled style="width: 100%; border: 2px solid black; border-radius: 10px; resize:none; padding: 20px;" name="description" id="" cols="30" rows="10" required><?php echo htmlspecialchars($activities['description']);?></textarea>
                                </span>
                            </div>
                            <div style=" margin-top: 1vw; display: flex; justify-content: space-between;" >                         
                                <div>
                                    <button class="button-buttons" name="update_activityStatus" style="background-color: #2828FA;">Done</button> 
                                </div>
                                <div>
                                    <button type="button" class="button-buttons" id="edit-button">Edit</button>
                                    <button type="submit" class="button-buttons" name="save-button" id="save-button" style="display: none;">Save</button>
                                </div>
                            </div>
                        <?php else: ?>
                            <h1>Sorry, no activities available at this time.</h1>
                            <p style="color:gray;">Try refreshing the page or logging in again.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            <div id="pop-up" style=" height: auto; width: 75%; display: flex; flex-wrap:wrap; justify-content: space-evenly; align-items: center; gap: 1vw;">
                <div style="width: 600px; height: 20vw; background-color: whitesmoke; border-radius: 20px; padding: 1.5vw; border: 2px solid lightblue; overflow: auto; box-shadow: 0 4px 10px rgba(173, 216, 230, 0.6);">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw; ">Requests</h2>
                    <table>
                        <tbody>
                            <?php if ($participants && count($participants) > 0): ?>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td style="text-align: left;"><?php echo htmlspecialchars($participant['firstName'] . ' ' . $participant['lastName']); ?></td>
                                        <td>
                                            <?php if (!empty($participant['image'])): ?>
                                                <?php
                                                $imgData = base64_encode($participant['image']);
                                                $src = 'data:image/jpeg;base64,' . $imgData;
                                                ?> 
                                                <img src="<?php echo $src; ?>" alt="Participant Image" style="max-width:100px; max-height:100px;">
                                            <?php else: ?>
                                                No image
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($participant['participant_id']); ?>">
                                                <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($activityId); ?>">
                                                <button class="button-buttons" name="cross" style="background: url('../imgs/icon_cross.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button>
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
                <div style="width: 600px; height: 20vw; background-color: whitesmoke; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D; position: relative; overflow: auto; box-shadow: 0 4px 10px rgba(0, 128, 0, 0.6);">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Active</h2>
                    <form action="" method="POST" style="position: absolute; top: 1.5vw; right: 2vw;">
                            <button class="button-buttons" name="notify_date_all" style="">notify</button> 
                    </form> 
                    <table>                       
                        <tbody>
                            <?php if ($actives && count($actives) > 0): ?>
                                <?php foreach ($actives as $active): ?>
                                    <tr>
                                        <td style="text-align: left;"><?php echo htmlspecialchars($active['firstName'] . ' ' . $active['lastName']); ?></td>
                                        <td>
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($active['participant_id']); ?>">
                                                <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($activityId); ?>">
                                                <button class="button-buttons" name="remove" style="background: url('../imgs/icon_cross.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button>
                                                <button class="button-buttons" name="notify_date" style="background: url('../imgs/icon_check.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button> 
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
                <div style="width: 600px; height: 20vw; background-color: whitesmoke; border-radius: 20px; padding: 1.5vw; border: 2px solid pink; overflow: auto; box-shadow: 0 4px 10px rgba(255, 192, 203, 0.6);">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Refunds</h2>
                    <table>
                        <tbody>
                            <?php if ($refunds && count($refunds) > 0): ?>
                                <?php foreach ($refunds as $refund): ?>
                                    <tr>
                                        <td style="text-align: left;"><?php echo htmlspecialchars($refund['firstName'] . ' ' . $refund['lastName']); ?></td>
                                        <td>
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($refund['participant_id']); ?>">
                                                <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($activityId); ?>">
                                                <button class="button-buttons" name="" style="background: url('../imgs/icon_cross.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button>
                                                <button class="button-buttons" name="accept-refund" style="background: url('../imgs/icon_check.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button> 
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
                <div style="width: 600px; height: 20vw; background-color: whitesmoke; border-radius: 20px; padding: 1.5vw; border: 2px solid goldenrod; overflow: auto; box-shadow: 0 4px 10px rgba(218, 165, 32, 0.6);">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Waitlist</h2>
                    <table>
                        <tbody>
                            <?php if ($waitlists && count($waitlists) > 0): ?>
                                <?php foreach ($waitlists as $waitlist): ?>
                                    <tr>
                                        <td style="text-align: left;"><?php echo htmlspecialchars($waitlist['firstName'] . ' ' . $waitlist['lastName']); ?></td>
                                        <td>
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($waitlist['participant_id']); ?>">
                                                <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($activityId); ?>">
                                                <button class="button-buttons" name="remove" style="background: url('../imgs/icon_cross.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button>
                                                <button class="button-buttons" name="notify" style="background: url('../imgs/icon_check.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button> 
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
        </div>
    </div>
                            
    <script>
    function showPopup(activityId) {
        const popup = document.getElementById("pop-up");

        if (popup) {
            popup.style.display = "flex";
        } else {
            console.error("Popup element not found!");
        }
    }

    
    document.addEventListener("DOMContentLoaded", function() {
        let pickupInput = document.getElementById("pickup_input");
        let addPickupBtn = document.getElementById("add_pickup_btn");
        let viewPickupBtn = document.getElementById("view_pickup_btn");
        let pickupDropdown = document.getElementById("pickup_dropdown");
        let pickupList = document.getElementById("pickup_list");
        let pickupHidden = document.getElementById("pickup_locations_hidden");

        let pickupPoints = pickupHidden.value.split(',').map(point => point.trim());

        // Function to update pickup list display
        function updatePickupList() {
            pickupList.innerHTML = "";

            if (pickupPoints.length === 0) {
                const li = document.createElement("li");
                li.textContent = "No pickup points added";
                li.style.padding = "10px";
                li.style.color = "#777";
                pickupList.appendChild(li);
            } else {
                pickupPoints.forEach((point, index) => {
                    const li = document.createElement("li");
                    li.style.padding = "8px 10px";
                    li.style.borderBottom = "1px solid #eee";
                    li.style.display = "flex";
                    li.style.justifyContent = "space-between";
                    li.style.alignItems = "center";

                    const pointText = document.createElement("span");
                    pointText.textContent = point;

                    const removeBtn = document.createElement("button");
                    removeBtn.textContent = "×";
                    removeBtn.style.background = "none";
                    removeBtn.style.border = "none";
                    removeBtn.style.color = "red";
                    removeBtn.style.fontSize = "18px";
                    removeBtn.style.cursor = "pointer";
                    removeBtn.onclick = function () {
                        pickupPoints.splice(index, 1);
                        updatePickupList();
                        updateHiddenInput();
                    };

                    li.appendChild(pointText);
                    li.appendChild(removeBtn);
                    pickupList.appendChild(li);
                });
            }
        }

        // Function to update hidden input
        function updateHiddenInput() {
            pickupHidden.value = pickupPoints.join(', ');
        }

        // Add new pickup point
        addPickupBtn.addEventListener("click", function() {
            let pickupPoint = pickupInput.value.trim();
            if (pickupPoint && !pickupPoints.includes(pickupPoint)) {
                pickupPoints.push(pickupPoint);
                updatePickupList();
                updateHiddenInput();
                pickupInput.value = ""; // Clear input
            }
        });

        // Allow adding via Enter key
        pickupInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                addPickupBtn.click();
            }
        });

        // Toggle pickup dropdown visibility
        viewPickupBtn.addEventListener("click", function() {
            pickupDropdown.style.display = (pickupDropdown.style.display === "block") ? "none" : "block";
        });

        // Close dropdown if clicked outside
        document.addEventListener("click", function(e) {
            if (!e.target.closest("#pickup_dropdown") && e.target !== viewPickupBtn && e.target !== addPickupBtn) {
                pickupDropdown.style.display = "none";
            }
        });

        // Initialize pickup list and hidden input
        updatePickupList();
        updateHiddenInput();
        });

        //edit button for enabling the fields
        document.addEventListener("DOMContentLoaded", function() {
            const editButton = document.getElementById('edit-button');
            const saveButton = document.getElementById('save-button');
            const container = document.querySelector('.container-wrap');
            editButton.addEventListener("click", function() {
                const container = document.querySelector('.container-wrap');
                const fields = container.querySelectorAll('input.input_fields, select.input_fields, textarea');
                fields.forEach(field => {
                    field.disabled = false;
                    field.readOnly = false;
                    field.style.borderColor = "lightblue";
                    field.style.boxShadow = "0 0 8px lightblue";
                });


                editButton.style.display = "none";
                saveButton.style.display = "inline-block";
            });
        });

        document.querySelector('form').addEventListener('submit', function(event) {
            const value = document.getElementById('distance_value').value;
            const unit = document.getElementById('distance_unit').value;
            
            if (value) {
                document.getElementById('combined_distance').value = value + ' ' + unit;
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            let priceInput = document.getElementById("price_value");
            let hiddenPrice = document.getElementById("final_price");

            priceInput.addEventListener("input", function() {
                hiddenPrice.value = priceInput.value;
            });

            hiddenPrice.value = priceInput.value;
        });
        document.getElementById('difficultySelect').addEventListener('change', function () {
            document.getElementById('difficultyHidden').value = this.value;
        });

        //session messages
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