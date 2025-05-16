<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id']; 
    $userData = getJoinerUserdata($pdo, $userId);

    if ($userData && $userData['avatar']) {
        $mimeType = 'image/jpeg';
        $base64Image = base64_encode($userData['avatar']);
        $avatarUrl = "data:$mimeType;base64,$base64Image";
    } else {
        $avatarUrl = "../imgs/defaultuser.png";
    }

    $joinerName = $userData['firstName'];
} else {
    session_unset();
    session_destroy();
    header('location: landing_page.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout_button'])) {
    session_unset();
    session_destroy(); 
    header('location: landing_page.php');
    exit;
}

$errorMessage = "";
$successMessage = "";


//form handling for images, function found in ACTIVITY_STORE
if (isset($_SESSION['error_message']) && $_SESSION['error_message'] !== "") {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); 
}
if (isset($_SESSION['success_message']) && $_SESSION['success_message'] !== "") {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])){
        session_destroy();
        header('location: landing_page.php');
        exit;
    }
}


//retrieve images
$qrCodeData = displayQRCodes($pdo, $userId);
//retrieve rated activities
$ratedActivities = getRatedActivities($pdo, $userId);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 

    <style>
        .no-spinner {
            -moz-appearance: textfield; 
            -webkit-appearance: textfield; 
            appearance: textfield; 
        }
        .no-spinner::-webkit-inner-spin-button,
        .no-spinner::-webkit-outer-spin-button {
            -webkit-appearance: none; 
            margin: 0; 
        }
        input {
            padding: 10px;
            border-radius: 5px;
        }
        legend {
            padding-top: 10px;
            font-weight: bold;
        }
        #div-button {
            cursor: pointer;
            transition: transform 0.1s;
        }
        #div-button:hover {
            transform: scale(1.03);
        }
        #div-button:active {
            transform: scale(1.01);
        }
        input[type="file"] {
            display: none;
        }
        .custom-upload-button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            #div-image {
                height: 15vw;
                width: 15vw;
                bottom: -30%;
            }
            #profileImage {
                height: 13vw;
                width: 13vw;
            }
        }
        @media (max-width: 480px) {
            #div-image {
                height: 20vw;
                width: 20vw;
                bottom: -20%;
            }
            #profileImage {
                height: 18vw;
                width: 18vw;
            }
        }
        .qr-code-item:hover {
            transform: scale(1.05); /* Slightly enlarge on hover */
        }

        .qr-code-image {
            height: 200px;
            width: 200px;
            object-fit: cover; 
            border-radius: 5px; 
        }

        .bank-name {
            margin-top: 10px; /* Space above the bank name */
            font-weight: bold; /* Bold text */
            color: #333; /* Darker text color */
        }
        .input_fields {
            width: 400px;
        }
        .blue_buttons {
            border: none;
            color: white;
            padding: 0.5vw 0.7vw;
            font-size: 12px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
            margin: 0.5vw  0vw;
        }
        .blue_buttons:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<!--NAVBAR START -->
<nav id="nav" style="background-color: white;">
    <div class="nav_left">
        <ul class="navbar">
            <li><input type="button" class="logo"></li>
            <li><a href="joiner_homePage.php" >Home</a></li>
            <li><a href="">Activity</a></li>
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
<!--NAVBAR END -->

<body style="height: 100vh; width: 100%; margin: 0; padding: 0; position: relative; background: linear-gradient(to right, #f0f8ff, #fef9e4, #f5f5f5);">

    <span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
    <span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    

    <div class="container" style=" height: 100%; width: 100%; padding: 12vh;">
        <div style=" height: 30%; width: 100%; background-color: skyblue; border-top-left-radius: 20px; border-top-right-radius: 20px; position: relative; margin: 0;">  
            <!--  the button on the right of the cover -->
            <span id="div-button" style="position: absolute; bottom: 10px; right: 10px; display: flex; align-items:center; padding: 10px; background-color: grey; color: white; border-radius: 10px; gap: 10px;"><img src="../imgs/icon_image.png" alt="" style=" height: 30px; width: 30px;"><p>Add cover photo</p></span>      
            <!--  the profile image on the left -->
            <form id="uploadForm" action="../html/includes/upload_avatarJoiner.php" method="POST" enctype="multipart/form-data">
                <div id="div-image" style="position: absolute; bottom: -45%; left: 3%; background-color: lightcoral; height:11vw; width:11vw; border-radius: 50%; display: grid; place-content: center;">
                    <button type="button" id="profileImage" 
                        style="cursor: pointer; background-image: url('<?php echo $avatarUrl; ?>'); 
                            height:10vw; width: 10vw; background-position: center; background-size: cover; 
                            border-radius: 50%; background-color: transparent; border: none;" 
                        onclick="document.getElementById('profileInput').click();">
                    </button>
                    <input id="profileInput" name="avatar" type="file" style="display: none;" accept="image/*" onchange="document.getElementById('uploadForm').submit();">
                </div>
            </form>  
        </div>
        <form action="" method="POST" style="width: 100%; height: 70%; position: relative; display: flex; justify-content: space-between; gap: 2%; margin-top: 1vw;">
            <div style="padding: 5vw 1vw 1vw 1vw; display: flex; flex-direction: column; gap: 1vw; height: 100%; width: 20%; position: relative;">
                <h4>Rated Activities:</h4> 
                <div style=" box-shadow: 0 4px 8px rgba(255, 165, 0, 0.2); padding: 1vw; max-height: 50%; overflow: auto; border-radius: 10px;">
                    <?php if (!empty($ratedActivities)): ?>
                        <?php foreach ($ratedActivities as $activity): ?>
                            <p>
                                <strong><?= htmlspecialchars($activity['activity_name']) ?></strong><br> 
                                <?php
                                    $rating = (int)$activity['rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '⭐' : '✩';
                                    }
                                ?>
                            </p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No rated activities yet.</p>
                    <?php endif; ?>
                </div>   
                <div style="position: absolute; bottom: 1vw; left: 1vw;">
                    <button type="submit" name="logout" class="blue_buttons" style="background: linear-gradient(to right, #ff6b6b, #ffa07a);">Logout</button>
                </div>        
            </div>
            <div style="border: 2px solid black; width: 80%; border-bottom-right-radius: 20px; padding: 2vw; background-color: whitesmoke;">
                <h2>Personal Information</h2>
                <div style="display: flex; flex-wrap: wrap; gap: 1vw; justify-content: space-between; margin: 1vw 0vw; width: 100%;">
                    <input readonly class="input_fields" type="text" name="firstName" value="<?php echo htmlspecialchars($userData['firstName']); ?>">
                    <input readonly class="input_fields" type="text" name="lastName" value="<?php echo htmlspecialchars($userData['lastName']); ?>">
                    <input readonly class="input_fields" type="text" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>">
                    <input readonly class="input_fields" type="text" name="address" value="<?php echo htmlspecialchars($userData['address']); ?>">
                    <input readonly class="input_fields" type="text" name="gender" value="<?php echo htmlspecialchars($userData['gender']); ?>">
                    <input readonly class="input_fields" type="text" name="number" value="<?php echo htmlspecialchars($userData['contactNumber']); ?>">
                </div>
                <div style="display: flex; width: 100%; justify-content: flex-end; gap: 1vw;">
                    <button type="reset" class="blue_buttons" style="background: linear-gradient(to right, #ff6b6b, #ffa07a);">Cancel</button>
                    <button type="button" class="blue_buttons" style="background: linear-gradient(to bottom, #a8e6cf, #56ab91);">Edit</button>
                </div>
            </div>
        </form>
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

        const editButton = document.getElementById('edit-button');
        function editDetails (){
            
        }
    </script>

</body>
</html>
