<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

$activityId ='';

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


//session messages
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

//fetching functions 
$data = displayOrgActivities($pdo, $userId);
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
        ::-webkit-scrollbar {
        width: 12px; 
        }
        ::-webkit-scrollbar-track {
            background: transparent; 
        }
        ::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.5); 
            border-radius: 10px;
            border: 3px solid transparent;
        }
        #calendar-img{
            background-color: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        #calendar-img:hover{
            transform: scale(1.2);
            cursor: pointer;
            background-color: none;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body style="background-color: white; height: 100vh; width: 100%; background: linear-gradient(120deg, #B8E1FF, #FFD1FF, #FFFFFF);">

    <span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
    <span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    

    <nav id="nav" style="background-color: white;">
        <div class="nav_left">
            <ul class="navbar">
                <li><input type="button" class="logo"></li>
                <li style=" border-bottom: 2px solid green;"><a href="">Home</a></li>
                <li><a href="org_createAct.php">Activity</a></li>
                <li><a href="org_forumPage.php">Forum</a></li>
                <li><a href="org_notification.php">Notification</a></li>
            </ul>
        </div>
        <div class="nav_right" id="nav_right_click" onclick="window.location.href='org_account.php';">  
            <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
            <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($orgname); ?></span>
        </div>         
    </nav>

    <div class="container" style=" padding-top: 7vh; height:100%; width: 100%; display: flex; justify-content: center; flex-grow:1 ;">
        <div class="container-wrap" style="height: auto; width: 100%; padding: 3vh; display: flex; flex-direction: column; align-items: center; gap: 2vw;">
            <div style=" height: auto; width: 100%; text-align: center;">
                <h1 style="color: white;">Hi <?php echo htmlspecialchars($orgname)?>, here to create an activity?</h1><br>
                <p><strong style="color: white;">Go to actvity page -></strong> <a href="org_createAct.php" class="link-button">Create Activity</a></p>
            </div>

            <div style="width: 70%; height: 30vw; box-shadow: 1px 2px 6px 0.1px; padding: 2vw; border-radius: 20px; display: flex; flex-direction: column; gap: 1vw; background-color: #A9BA9D;">
                <div style=" display: flex; justify-content:space-between; width: 100%;">
                    <h1 style=" color: azure;">Activities to Look Forward</h1>
                    <div style=" padding: 0vw 2vw;">
                        <a href="orgCalendarModule.php" id="calendar-button">
                            <img id="calendar-img" src="../imgs/icon_calendar.png" alt="Calendar" style="height: 50px; width: 50px;">
                        </a>
                    </div>  
                </div>
            
                <div style="width: 100%; height:100%; border: 2px solid black; border-radius: 10px; padding: 2vw; background-color: azure; overflow: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Location</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Participants</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($data['success']):?>
                            <?php foreach( $data['data'] as $row):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['activity_name']); ?></tdstlye>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']);?></td>
                                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                                    <td><?php echo htmlspecialchars($row['participants']);?></td>
                                    <td><?php $date = new DateTime($row['date']); echo $date->format('F j, Y');?></td>
                                    <td>
                                    <a href="org_manageParticipant.php?id=<?php echo htmlspecialchars($row['id']); ?>">
                                    <button class="button-buttons" id="join-button">Manage</button>
                                    </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                            <td colspan="7"><?php echo htmlspecialchars($data['failed_message']); ?></td>
                            </tr> 
                        <?php endif; ?> 
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="pop-up" style=" height: auto; width: 100%; display: none; flex-wrap:wrap;justify-content: space-evenly; align-items: center;">
                <div style="width: 30%; height: 20vw; background-color: gainsboro; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D; overflow: auto; ">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Requests</h2>
                    <table style=" height: auto; overflow: auto;">
                        <thead>
                            <tr>
                                <th>Participant Name</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($participants && count($participants) > 0): ?>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($participant['firstName'] . ' ' . $participant['lastName']); ?></td>
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
                <div style="width: 30%; height: 20vw; background-color: gainsboro; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D;">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Active</h2>
                </div>
                <div style="width: 30%; height: 20vw; background-color: gainsboro; border-radius: 20px; padding: 1.5vw; border: 2px solid #A9BA9D;">
                    <h2 style=" width: 100%; border-bottom: 1px solid black; text-align: left; padding-bottom: 1vw;">Refunds</h2>
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