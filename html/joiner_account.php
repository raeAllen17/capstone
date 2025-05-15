<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id']; 
    $userData = getJoinerUserdata($pdo, $userId);
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['qr_code_image'])) {
    $uploadData = [
        'id' => $_SESSION['id'],
        'bank' => htmlspecialchars($_POST['bank'])
    ];

    if (uploadQRCode($_FILES['qr_code_image'], $uploadData, $pdo)) {
        $_SESSION['success_message'] = "QR code uploaded successfully!";
    } else {
        $errorMessage = $_SESSION['error_message'];
    }

    header("Location: org_account.php");
    exit();
}


//retrieve images
$qrCodeData = displayQRCodes($pdo, $userId);

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
        .blue_buttons {
            background: linear-gradient(to right, #5dbb63, #03ac13); 
            border: none;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
            margin: 15px;
        }
        .blue_buttons:hover {
            transform: translateY(-2px);
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
            <div id="div-image" style="position: absolute; bottom: -45%; left: 3%; background-color: lightcoral; height:11vw; width:11vw; border-radius: 50%; display: grid; place-content: center;">
                <button id="profileImage" style="cursor: pointer;background-image: url('../imgs/defaultuser.png'); height:10vw; width: 10vw; background-position: center; background-size: cover; border-radius: 50%; background-color:  transparent; border: none;" onclick="document.getElementById('profileInput').click();">
                </button>
                <input id="profileInput" type="file" style="display: none;" accept="image/*" onchange="updateImage(this, 'profileImage')">
            </div>  
        </div>
        <div style="width: 100%; height: 100%; position: relative; display: flex; justify-content: space-between; align-items: center; gap: 2%;">
                  
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

        const editButton = document.getElementById('edit-button');
        function editDetails (){
            
        }
    </script>

</body>
</html>
