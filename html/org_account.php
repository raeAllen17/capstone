<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id']; 
    $userData = getUserdata($pdo, $userId);
    $orgname = $userData['orgname'];
} else {
    echo "login ka muna.";
}

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
            -moz-appearance: textfield; /* Removes spinner for Firefox */
            -webkit-appearance: textfield; /* Removes spinner for Chrome, Safari, Edge */
            appearance: textfield; /* Standard property for modern browsers */
        }

        .no-spinner::-webkit-inner-spin-button,
        .no-spinner::-webkit-outer-spin-button {
            -webkit-appearance: none; /* Hides spinner buttons for WebKit browsers */
            margin: 0; /* Optional: Adjust layout */
        }
        input {
            padding: 10px;
            border-radius: 5px;
            border: 2px solid black;
        }

        legend {
            padding-top: 10px;
            font-weight: bold;
        }
        .blue_buttons {
            background: linear-gradient(to right, #5dbb63, #03ac13); /* Blue to Purple */
            border: none;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
            margin: 15px;
        }
        .blue_buttons:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<!--NAVBAR START -->
<nav id="nav" style="background-color: white;">
    <div class="nav_left">
        <ul class = "navbar">
            <li><input type="button" class="logo"></li>
            <li><a href="">Home</a></li>
            <li><a href="org_createAct.php">Activity</a></li>
            <li><a href="">Forum</a></li>
            <li><a href="">Marketplace</a></li>
            <li><a href="">Notification</a></li>
        </ul>
    </div>
    <div class="nav_right" id="nav_right_click" onclick="window.location.href='org_account.php';">  
        <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
        <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($orgname); ?></span>
    </div>         
</nav>
<!--NAVBAR END -->

<body style="height: 100vh; width: 100%; margin: 0; padding: 0;">
    <div class="container" style="display: grid; place-content: center; height: 100%; padding-top: 3vh;">
        <div class="content-wrapper">
            <h1 style="font-size: 3rem;">Your Profile</h1>
            <div style="width: 500px; border: 4px solid green; display: flex; flex-direction: column; padding: 40px; border-radius: 10px;">
                <div style=" display: flex; align-items: center; justify-content: center; gap: 20px; border-bottom: 2px solid green; padding: 20px;">
                    <img id="profileImage" src="../imgs/defaultuser.png" style="height: 150px; width: 150px; cursor: pointer; border-radius: 50%; object-fit: cover;" onclick="document.getElementById('imageInput').click();">
                    <h1><?php echo htmlspecialchars($orgname)?></h1>
                    <input id="imageInput" type="file" style="display: none;" accept="image/*" onchange="updateImage(this)">
                </div>  
                <div>
                    <form action="">
                        <div style=" display: flex; flex-direction: column; padding: 10px; gap:5px;">
                            <legend>Email</legend>
                            <input type="email" value="<?php echo htmlspecialchars($userData['orgemail'])?>">
                            <legend>CEO</legend>
                            <input type="text" value="<?php echo htmlspecialchars($userData['ceo'])?>">
                            <legend>Address</legend>
                            <input type="text" value="<?php echo htmlspecialchars($userData['orgadd'])?>">
                            <legend>Contact Number</legend>
                            <input type="number" value="<?php echo htmlspecialchars($userData['orgnumber'])?>" class="no-spinner">
                        </div> 
                        <div style="width: 100%; display: grid; place-content: center;">
                            <button class="blue_buttons">Confirm</button>
                        </div>  
                    </form>
                </div>    
            </div>
        </div>
    </div>

    <script>
        function updateImage(input) {
            const file = input.files[0];
            const reader = new FileReader(); 

            reader.onload = function (e) {
                document.getElementById('profileImage').src = e.target.result;
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>

</body>

</html>