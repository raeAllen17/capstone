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

//updating notified status
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['check']) && isset($_POST['participant_id']) && isset($_POST['activity_id'])) {
        if (isset($_POST['participant_id'])) {
            $participantId = $_POST['participant_id'];
            $activityId = $_POST['activity_id'];
            updateNotified($pdo, $participantId, $activityId);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else if (isset($_POST['cross'])){
        if (isset($_POST['participant_id'])) {
            $participantId = $_POST['participant_id'];
            $activityId = $_POST['activity_id'];
            rejectRequest($pdo, $participantId, $activityId);
        }
    }
}


//fetching functions 
$activities = getactivities($pdo, $activityId);
if (!$activities) {
    $activities = [];
}
$participants = getParticipantRequest($pdo, $userId, $activityId);
$waitlists = getWaitlistRequest($pdo, $userId, $activityId);
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
            <div style=" height: auto; width: 100%; text-align: center;">
                <h1>Hi <?php echo htmlspecialchars($orgname)?>, here to create an activity?</h1><br>
                <p><strong>Go to actvity page -></strong> <a href="org_createAct.php" class="link-button">Create Activity</a></p>
            </div>

            <div style="width: 70%; height: auto; box-shadow: 1px 2px 6px 0.1px; padding: 2vw; border-radius: 20px; display: flex; flex-direction: column; gap: 1vw; background-color: #A9BA9D;">
                <h1 style=" color: azure;">Manage your activity!</h1>
                <div style="width: 100%; height:100%; border: 2px solid black; border-radius: 10px; padding: 2vw; background-color: azure;">          
                    <?php if(!empty($activities)): ?>  
                        <div style=" width: 100%; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;">
                            <div class="div-space">
                                <p>Activity Name</p>
                                <input type="text" class="input_fields" name="activity_name" required value="<?php echo htmlspecialchars($activities['activity_name']);?>">
                            </div>                      
                            <div class="div-space">
                                <p>Location</p>
                                <input type="text" class="input_fields" name="location" required value="<?php echo htmlspecialchars($activities['location'])?>">
                            </div>
                            <div class="div-space">
                                <p>Date</p>
                                <input type="date" class="input_fields" name="date" required value="<?php echo htmlspecialchars($activities['date'])?>">
                            </div>
                            <div class="div-space">
                                <p>Distance</p>
                                <div style="position: relative;">
                                    <input type="number" step="0.1" id="distance_value" style="padding-right: 45px;" class="input_fields" required value="<?php echo htmlspecialchars($activities['distance'])?>">
                                    <select id="distance_unit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; -webkit-appearance: none; -moz-appearance: none; appearance: none;">
                                        <option value="km">km</option>
                                        <option value="miles">miles</option>
                                    </select>
                                    <input type="hidden" name="distance" id="combined_distance">
                                </div>
                            </div>
                            <div class="div-space">
                                <p>Price</p>
                                <div style="position: relative;">
                                    <input type="number" step="0.1" id="price_value" style="padding-right: 45px;" class="input_fields" required value="<?php echo htmlspecialchars($activities['price'])?>">
                                    <select id="currency_symbol" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; -webkit-appearance: none; -moz-appearance: none; appearance: none;">
                                        <option value="PHP">₱</option>
                                    </select>
                                    <input type="hidden" name="price" id="final_price">
                                </div>
                            </div>
                            <div class="div-space">
                                <p>Difficulty</p>
                                <select class="input_fields" name="difficulty" required>
                                    <option value="" disabled selected><?php echo htmlspecialchars($activities['difficulty'])?></option>
                                    <option value="Easy">Easy</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="Challenging">Challenging</option>
                                    <option value="Difficult">Difficult</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <div class="div-space">
                                <p>Participants</p>
                                <input type="number" class="input_fields" name="participants" required value="<?php echo htmlspecialchars($activities['participants'])?>">
                            </div>
                            <div class="div-space">
                                <p>Pickup Points</p>
                                <div style="display: flex; position: relative;">
                                    <input type="text" id="pickup_input" class="input_fields" style="padding-right: 80px;" placeholder="Enter pickup point">
                                    <button type="button" id="add_pickup_btn" style="position: absolute; right: 0; top: 0; height: 100%; width: 40px; color: black; background-color: transparent; border: none; border-radius: 0 10px 10px 0; cursor: pointer; font-size: 20px;">+</button>
                                    <button type="button" id="view_pickup_btn" style="position: absolute; right: 45px; top: 0; height: 100%; width: 40px; color: black; background-color: transparent; border: none; cursor: pointer; font-size: 16px;">▼</button>
                                </div>
                                <div id="pickup_dropdown" style="display: none; position: absolute; background-color: white; border: 1px solid #ddd; max-height: 100px; overflow-y: scroll;width: 300px; z-index: 10; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                    <ul id="pickup_list" style="list-style: none; padding: 0; margin: 0;"></ul>
                                </div>
                                <input type="hidden" name="pickup_locations" id="pickup_locations_hidden">
                            </div>                           
                        </div>
                        <div class="div-space">
                            <p>Description</p>
                            <span>
                                <textarea style="width: 100%; border: 2px solid black; border-radius: 10px; resize:none; padding: 20px;" name="description" id="" cols="30" rows="10" required><?php echo htmlspecialchars($activities['description']);?></textarea>
                            </span>
                        </div>
                    <?php else: ?>
                        <h1>Sorry, no activities available at this time.</h1>
                        <p style="color:gray;">Try refreshing the page or logging in again.</p>
                    <?php endif; ?>
                    </div>
                </div>
            <div id="pop-up" style=" height: auto; width: 100%; display: flex; flex-wrap:wrap; justify-content: space-evenly; align-items: center; gap: 1vw;">
                <div style="width: 600px; height: 20vw; background-color: gainsboro; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D; overflow: auto;">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Requests</h2>
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
                <div style="width: 600px; height: 20vw; background-color: gainsboro; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D;">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Active</h2>
                    
                </div>
                <div style="width: 600px; height: 20vw; background-color: gainsboro; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D;">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Refunds</h2>
                </div>
                <div style="width: 600px; height: 20vw; background-color: gainsboro; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D;">
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
                                                <button class="button-buttons" name="notify" style="background: url('../imgs/icon_cross.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button>
                                                <button class="button-buttons" name="remove" style="background: url('../imgs/icon_check.png'); background-size: cover; background-position: center; height: 30px; width: 30px;"></button> 
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
    </script>
    </body>
</html>