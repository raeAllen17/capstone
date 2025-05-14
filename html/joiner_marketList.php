<?php
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';

if(isset($_SESSION['id'])){
    $userId = $_SESSION['id']; 
    $userData = getJoinerUserdata($pdo, $userId);
    $joinerName = $userData['firstName'];
} else {
    header("location: landing_page.php");
    exit();
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 

    <style>
        * {
            margin: 0;
            padding: 0;
        }
        .file-upload {
            background-color: lightgrey;
            height: 428px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            border-radius: 10px;
            position: relative;
        }
        .file-upload:hover {
            border: 2px solid lightblue;
            box-shadow: 0px 0px 8px rgba(0, 0, 180, 0.6);
        }
        .slideshow-container {
            border: 2px solid grey;
            border-radius: 10px;
            overflow: hidden;
            position: absolute;
            z-index: 1;
            top: 0px;
            left: 0px;
            display: none;
            max-height: 428px;
            width: 100%;
            height: 428px;
            box-sizing: border-box; /* Ensures border is inside the container */
        }

        .slideshow-container img {
            height: 428px; /* Matches container height */
            width: 100%;
            object-fit: cover; /* Adjusts images to fit the container */
            cursor: pointer;
            transition: opacity 0.3s;
        }
        .slideshow-container img:hover {
            opacity: 0.8;
        }
        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 24px;
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            padding: 10px;
            border-radius: 5px;
        }
        .prev {
            left: 0px;
        }
        .next {
            right: 0px;
        }
        input[type="file"] {
            display: none;
        }
        .input-fields{
            padding: 10px;
            border: 2px solid black;
            border-radius: 10px;
            width: 400px;
        }
        .input-fields::placeholder {
            color: #888;
        }
        textarea {
            padding: 10px;
            border: 2px solid black;
            border-radius: 10px;
        }
        textarea::placeholder {
            color: #888;
        }
        .blue_buttons {
            background: linear-gradient(to right, #5dbb63, #03ac13);
            border: none;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
            margin: 0px 15px;
        }
        .blue_buttons:hover {
            transform: translateY(-2px);
        }
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            border: 2px solid lightblue !important;;
            box-shadow: 0px 0px 8px rgba(0, 0, 180, 0.6);
            outline: none;
        }
        select {
            padding: 10px;
            border: 2px solid black;
            border-radius: 10px;
            width: 400px;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
        input[type="number"] {
            appearance: none;
            -webkit-appearance: none; 
            -moz-appearance: none;
        }
        
    </style>
</head>
<body style="height: 100vh;">

    <span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
    <span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    
   
    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li><a href="joiner_homePage.php" >Home</a></li>
                    <li><a href="joiner_activityPage.php">Activity</a></li>
                    <li ><a href="joiner_forumPage.php" >Forum</a></li>
                    <li style=" border-bottom: 2px solid green;"><a href="joiner_marketplace.php" >Marketplace</a></li>
                    <li><a href="joiner_notification.php" >Notification</a></li>
                </ul>
            </div>
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='walapa.php';">  
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>          
    </nav>

    <div style="height: 100%; display: flex; justify-content: center; padding-top: 7vh;">
        <div style="height: 100%; padding-top: 7vh; display: flex; justify-content:center; align-items:center; width: 100%; position: relative; background: linear-gradient(to right, #ff7e5f, #feb47b);"> 
            <h1 style="position: absolute; top: 8vw; left: 12vw; font-size: 3em; color: whitesmoke;">Start listing!</h1>
            <form action="../html/includes/marketplace.php" method="POST" enctype="multipart/form-data" style=" width: 80%;">
                <div style=" width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 1vw; border-radius: 20px; border: 2px solid rgba(0, 255, 0, 0.8); background-color: whitesmoke;">
                    <div style="width: 35%;">
                        <div style="width:100%; position: relative;">
                            <div class="file-upload" onclick="document.getElementById('image').click();" style=" width:100%;">
                                <img src="../imgs/icon_image.png" alt="" style=" height: 100px; width: 100px;">
                            </div>                   
                            <div class="slideshow-container" id="slideshowContainer">              
                                <img id="slideshowImage" src="" alt="" style="display: block;">
                                <span class="prev" onclick="changeSlide(-1)">&#10094;</span>
                                <span class="next" onclick="changeSlide(1)">&#10095;</span>
                            </div>     
                            <input type="file" name="images[]" id="image" accept="image/*" multiple onchange="previewImages(event)">                                                       
                        </div>
                    </div>
                    <div style=" display: flex; width: 60%; flex-wrap: wrap; gap: 1vw; justify-content: space-between;">
                        <input type="text" class="input-fields" placeholder="Item Name" style="width: 100%;" name="item_name" required>
                        <input type="number" class="input-fields" placeholder="Price" name="price" required>
                        <select id="" name="condition" required>
                            <option value="" selected disabled>Condition</option>
                            <option value="0">Used</option>
                            <option value="1">Good</option>
                            <option value="2">New</option>
                        </select>
                        <input type="text" class="input-fields" placeholder="Location" name="location" required>
                        <select id="" name="category" required>
                            <option value="" selected disabled>Category</option>
                            <option value="0">Gears & Equipment</option>
                            <option value="1">Shelter & Sleeping</option>
                            <option value="2">Navigation & Safety</option>
                        </select>
                        <textarea id="" cols="30" rows="2" placeholder="Description" style="resize: none; width: 100%;" name="description" required></textarea>
                        <div style="color: #888; font-size: small;">
                            <p>Policy and Agreement</p>
                            <p> By listing an item on [Marketplace Name], you agree to provide accurate and
                                honest details about yourself and the item. Listings must not include illegal, 
                                counterfeit, stolen, or unsafe products. Misleading descriptions, photos, or pricing 
                                are not allowed. You are fully responsible for your item, including delivery and legal 
                                compliance.
                            </p>
                        </div> 
                        <div style="display: flex; justify-content: flex-end; width: 100%;">
                            <button type="reset" class="blue_buttons" style="background: linear-gradient(to right, #ff0000, #ff6347);">Cancel</button>
                            <button type="submit" name="list" class="blue_buttons" style="width: 110px;">List</button>                       
                        </div>                 
                    </div>
                </div>
            </form> 
        </div>
    </div>

    <script>
        let currentSlideIndex = 0;
        let images = [];
         function previewImages(event) {
            const files = event.target.files;
            images = []; 
            const slideshowContainer = document.getElementById('slideshowContainer');
            const slideshowImage = document.getElementById('slideshowImage');

            if (files.length > 0) {
                slideshowContainer.style.display = 'block';
                for (let i = 0; i < files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        images.push(e.target.result); 
                        if (i === 0) {
                            slideshowImage.src = e.target.result; 
                        }
                    }
                    reader.readAsDataURL(files[i]);
                }
            }
        }

        function changeSlide(direction) {
            if (images.length > 0) {
                currentSlideIndex += direction;
                if (currentSlideIndex < 0) {
                    currentSlideIndex = images.length - 1; 
                } else if (currentSlideIndex >= images.length) {
                    currentSlideIndex = 0; 
                }
                document.getElementById('slideshowImage').src = images[currentSlideIndex]; 
            }
        }
        document.getElementById('slideshowImage').addEventListener('click', function() {
            document.getElementById('image').click();
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