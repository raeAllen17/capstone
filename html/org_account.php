<?php 
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';

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

<body style="height: 100vh; width: 100%; margin: 0; padding: 0; position: relative;">

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
            <div style=" width: 20%;  margin-top: 3%;">
                <div>
                    <form action="">
                        <div style="display: flex; flex-direction: column; padding: 10px; gap:5px;">
                            <legend>Organization Name</legend>
                            <input type="text" value="<?php echo htmlspecialchars($userData['orgname'])?>">
                            <legend>Email</legend>
                            <input type="email" value="<?php echo htmlspecialchars($userData['orgemail'])?>">
                            <legend>CEO</legend>
                            <input type="text" value="<?php echo htmlspecialchars($userData['ceo'])?>">
                            <legend>Address</legend>
                            <input type="text" value="<?php echo htmlspecialchars($userData['orgadd'])?>">
                            <legend>Contact Number</legend>
                            <input type="number" value="<?php echo htmlspecialchars($userData['orgnumber'])?>" class="no-spinner">
                        </div>                 
                    </form>                
                    <div style="width: 100%; display: flex; justify-content: flex-end;">
                        <form action="" method="POST">
                            <button type="submit" name="logout_button">Logout</button>
                        </form>
                        <button class="blue_buttons">Confirm</button>
                    </div> 
                </div>
            </div>
            <div style="width:80%; height: 100%; display: flex; flex-direction: column; justify-content: space-between; align-items: center; gap: 2%; padding: 30px 0px 30px 30px;">
                <div style="width: 100%; height: 50%;">
                    <div style="width: 100%; height: 100%; border: 2px solid black; padding: 30px;">
                        <div>
                            <h2>Your Past Events</h2>
                        </div>
                    </div>                
                </div>
                <div style="width: 100%; height: 100%; border: 2px solid black; border-bottom-right-radius: 20px; padding: 30px; position: relative">
                    <div>
                        <h2>QR Codes</h2>
                        <p style="color:lightgrey; font-size: 2vh;">**Upload images of your online banks (Maya, Gcash) for registration fees transaction**</p>
                    </div>
                    <div class="qr-code-container" style=" display:flex; flex-wrap: wrap; margin: 20px 0px;">
                        <?php if ($qrCodeData['success']): ?>
                            <?php foreach ($qrCodeData['data'] as $qrCode): ?>
                                <div class="qr-code-item" style=" background-color: lightgrey; border: 1px solid black; border-radius: 10px; padding: 10px; text-align: center; margin: 10px; transition: transform 0.2s;">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($qrCode['qr_code_image']); ?>" alt="QR Code" class="qr-code-image"/>
                                    <p class="bank-name"><?php echo htmlspecialchars($qrCode['bank_name']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p><?php echo htmlspecialchars($qrCodeData['failed_message']); ?></p>
                        <?php endif; ?>
                    </div>
                    <span style="position: absolute; bottom: 0; right: 0;">
                        <button class="blue_buttons" onclick="showModal('modal-overlay')">Upload</button>
                    </span> 
                </div>      
            </div>       
        </div>
    </div>

    <div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(128, 128, 128, 0.7); display: none; justify-content: center; align-items: center; z-index: 999;">
        <div id="modal-box" style="width: 400px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: azure; border-radius: 10px;">   
            <div style="height: 100%; position: relative; display: flex; flex-direction: column; justify-content: space-between; align-items: center; padding: 20px;">
                <div style="width: 100%;">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div style="display: flex; flex-direction: column; padding: 20px; height: 100%; gap: 10px;">
                            <label>Bank</label>
                            <input type="text" name="bank" placeholder="Maya - Gcash" required>
                            
                            <label for="modalImageInput" style="display: inline-block; padding: 5px 10px; background-color: #03ac13; color: white; font-size: 16px; border-radius: 5px; cursor: pointer; text-align: center; transition: background-color 0.3s ease;">Upload Image</label>
                            <input type="file" id="modalImageInput" name="qr_code_image" accept="image/*" onchange="updateImage(this, 'imagePreview')" required> 
                            <div style="width: 100%; display: grid; place-content: center;">
                                <img id="imagePreview" src="" alt="Preview" style="height: 500px; width: 300px; display: none; margin-top: 10px; object-fit: cover;">  
                            </div>                                                
                        </div> 
                        <div style="padding: 20px; width: 100%; display: flex; justify-content: flex-end;">
                            <button type="submit" style="padding: 10px; border: 2px solid black; width: 100px; border-radius: 5px;">Save</button>
                        </div>          
                    </form> 
                </div>
            </div>      
        </div>
    </div>

    <script>
        function updateImage(input, targetId) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                let targetElement = document.getElementById(targetId);
                if (targetElement.tagName === "BUTTON") {
                    targetElement.style.backgroundImage = `url('${e.target.result}')`;
                } else if (targetElement.tagName === "IMG") {
                    targetElement.src = e.target.result;
                    targetElement.style.display = 'block';
                }
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        function showModal() {
            document.getElementById("modal-box").style.display = "block";
            document.getElementById("modal-overlay").style.display = "flex";
        }

        document.getElementById("modal-overlay").addEventListener("click", function(event) {
            if (event.target === this) {
                this.style.display = "none";
                document.getElementById("modal-box").style.display = "none"; // Hide modal when overlay is clicked
            }
        });

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
