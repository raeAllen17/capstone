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

<body style="height: 100vh; width: 100%; margin: 0; padding: 0; position: relative;">

    <div class="container" style=" height: 100%; width: 100%; padding: 12vh;">
        <div style=" height: 250px; width: 100%; background-color: skyblue; border-top-left-radius: 20px; border-top-right-radius: 20px; position: relative; margin: 0;">  
            <!--  the button on the right of the cover -->
            <span id="div-button" style="position: absolute; bottom: 10px; right: 10px; display: flex; align-items:center; padding: 10px; background-color: grey; color: white; border-radius: 10px; gap: 10px;"><img src="../imgs/icon_image.png" alt="" style=" height: 30px; width: 30px;"><p>Add cover photo</p></span>      
            <!--  the profile image on the left -->
            <div id="div-image" style="position: absolute; bottom: -40%; left: 3%; background-color: lightcoral; height:220px; width: 220px; border-radius: 50%; display: grid; place-content: center;">
                <button id="profileImage" style="cursor: pointer;background-image: url('../imgs/defaultuser.png'); height:200px; width: 200px; background-position: center; background-size: cover; border-radius: 50%; background-color:  transparent; border: none;" onclick="document.getElementById('profileInput').click();">
                </button>
                <input id="profileInput" type="file" style="display: none;" accept="image/*" onchange="updateImage(this, 'profileImage')">
            </div>       
        </div>
        <div style="width: 100%; height: 100%; position: relative; display: flex; justify-content: space-between; align-items: center; gap: 2%;">
            <div style=" width: 20%;  margin-top: 3%;">
                <div>
                    <form action="">
                        <div style=" display: flex; flex-direction: column; padding: 10px; gap:5px;">
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
                        <button class="blue_buttons">Confirm</button>
                    </div> 
                </div>
            </div>
            <div style=" width:80%; height: 100%; display: flex; flex-direction: column; justify-content: space-between; align-items: center; gap: 2%; padding: 30px 0px 30px 30px;">
                <div style=" width: 100%; height: 50%;">
                    <div style=" width: 100%; height: 100%; border: 2px solid black; padding: 30px;">
                        <div>
                            <h2>Your Past Events</label>
                        </div>
                    </div>                
                </div>
                <div style=" width: 100%; height: 50%; border: 2px solid black; border-bottom-right-radius: 20px; padding: 30px; position: relative;">
                        <div>
                            <h2>QR Codes</label>
                            <p style=" color:lightgrey; font-size: 2vh;">**Upload images of your online banks (Maya, Gcash) for registration fees transaction**</p>
                        </div>
                        <div>
                            <!-- images -->
                        </div>
                        <span style="position: absolute; bottom: 0; right: 0;"><button class="blue_buttons" onclick="showModal('modal-box')">Upload</button></span>
                </div>      
            </div>       
        </div>
    </div>

    <div id="modal-overlay" style=" position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(128, 128, 128, 0.7); display: none; justify-content: center; align-items: center; z-index: 999;">
        <div id="modal-box" style=" width: 400px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none; background-color: azure; border-radius: 10px;">   
            <div style=" height: 100%; position: relative; display: flex; flex-direction: column; justify-content: space-between; align-items: center; padding: 20px    ;">
                <div style=" width: 100%;">
                    <form action="" style="">
                        <div style=" display: flex; flex-direction: column; padding: 20px; height: 100%; gap: 10px;">
                            <label>Bank</label>
                            <input type="text" placeholder="Maya - Gcash">
                            
                            <label for="modalImageInput" style=" display: inline-block; padding: 5px 10px; background-color: #03ac13; color: white; font-size: 16px; border-radius: 5px; cursor: pointer; text-align: center; transition: background-color 0.3s ease;">Upload Image</label>
                            <input type="file" id="modalImageInput" accept="image/*" onchange="updateImage(this, 'imagePreview')"> 
                            <div style=" width: 100%; display: grid; place-content: center;">
                                <img id="imagePreview" src="" alt="Preview" style=" height: 300px; width: 300px; display: none; margin-top: 10px; object-fit: cover;">  
                            </div>                                                
                        </div> 
                        <div style="padding: 20px; width: 100%; display: flex; justify-content: flex-end;">
                            <button style=" padding: 10px; border: 2px solid black; width: 100px; border-radius: 5px; ">Save</button>
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
            document.getElementById("modal-overlay").style.display = "block";
        }

        function hideModal() {
            document.getElementById("modal-box").style.display = "none";
        }
        document.getElementById("modal-overlay").addEventListener("click", function(event) {
            if (event.target === this) {
                this.style.display = "none";
            }
        });
        
        function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function () {
                const imgPreview = document.getElementById('imagePreview');
                imgPreview.src = reader.result; arguments
                imgPreview.style.display = 'block';
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>

</body>

</html>