<?php
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/activity_store.php';
require_once 'includes/formHandler.php';

$userId = $_SESSION["id"];
$orgname = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {
        $result=createActivity($pdo, $_POST, $userId);
    } 
}

if (isset($_SESSION["id"])) {
    $userId = $_SESSION["id"];
    $orgname = getOrgname($pdo, $userId);
} else {
    echo "User  ID is not set in the session.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Activity</title>

    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 

    <style>

        .file-upload {
            background-color: gray;
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
        input[type="file"] {
            display: none;
        }
        .input_fields{
            padding: 15px;
            border: 2px solid black;
            border-radius: 10px;
            width: 350px;
        }
        .slideshow-container {
            border: 2px solid grey;
            border-radius: 10px;
            overflow: hidden;
            position: absolute;
            z-index: 1; 
            top: 23px;
            left: 0px;
            display: none;
            max-height: 428px;
            width: 100%;
            height: 428px;
        }
        .slideshow-container img {
            height: 429px;
            width: 100%; 
            object-fit: cover;
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
<body style=" height: 100vh; background-color: lightgrey; margin: 0; padding: 0;">

    <!--NAVBAR START -->
    <nav id="nav" style="background-color: white;">
        <div class="nav_left">
            <ul class = "navbar">
                <li><input type="button" class="logo"></li>
                <li><a href="">Home</a></li>
                <li style=" border-bottom: 2px solid green;"><a href="">Activity</a></li>
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

    <div class="container" style="padding-top: 5vh;display: flex; justify-content: center; height: 100vh; position: relative;">
        <div class="content" style="padding: 50px; height: 100vh;">
            <h1 style=" margin-bottom: 10px; font-size: 2.4em;">Create your own Activity and feel the Thrill!</h1>
            <form action="" method="POST" enctype="multipart/form-data">
                <div style="display: flex; justify-content: space-between; align-items: baseline; padding: 30px; border: 2px solid black; border-radius: 20px; width: 1200px; background-color: white; max-height: 100vh;">
                    <div class="left" style="display: flex; flex-direction: column; justify-content: space-between; gap: 20px;">
                        <div>
                            <p>Activity Name</p>
                            <input type="text" class="input_fields" name="activity_name" required>
                        </div>
                        <div style="width:100%; position: relative;">
                            <p>Images</p>
                            <div class="file-upload" onclick="document.getElementById('image').click();" style=" width:100%;">
                                <img src="../imgs/icon_image.png" alt="" style=" height: 100px; width: 100px;">
                            </div>
                            <input type="file" name="images[]" id="image" accept="image/*" multiple onchange="previewImages(event)"> 
                            <div class="slideshow-container" id="slideshowContainer">              
                                <img id="slideshowImage" src="" alt="" style="display: block;">
                                <span class="prev" onclick="changeSlide(-1)">&#10094;</span>
                                <span class="next" onclick="changeSlide(1)">&#10095;</span>
                            </div>                                                            
                        </div>
                    </div>
                    <div class="right" style=" display: flex; flex-direction: column;">
                        <div style="width: 100%;">
                            <p>Description</p>
                            <span>
                                <textarea style="width: 100%; border: 2px solid black; border-radius: 10px; resize:none; padding: 20px;" name="description" id="" cols="30" rows="10" required></textarea>
                            </span>
                        </div>
                        <div style=" display: flex; justify-content: space-between; gap: 30px;">
                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                <div>
                                    <p>Location</p>
                                    <input type="text" class="input_fields" name="location" required>
                                </div>
                                <div>
                                    <p>Date</p>
                                    <input type="date" class="input_fields" name="date" required>
                                </div>
                                <div>
                                <p>Distance</p>
                                    <div style="position: relative;">
                                        <input type="number" step="0.1" id="distance_value" style="padding-right: 45px; width: 100%;" class="input_fields" required>
                                        <select id="distance_unit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; -webkit-appearance: none; -moz-appearance: none; appearance: none;">
                                            <option value="km">km</option>
                                            <option value="miles">miles</option>
                                        </select>
                                        <input type="hidden" name="distance" id="combined_distance">
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                <div>
                                    <p>Difficulty</p>
                                    <select class="input_fields" name="difficulty" required>
                                        <option value="" disabled selected>Select difficulty</option>
                                        <option value="Easy">Easy</option>
                                        <option value="Moderate">Moderate</option>
                                        <option value="Challenging">Challenging</option>
                                        <option value="Difficult">Difficult</option>
                                        <option value="Expert">Expert</option>
                                    </select>
                                </div>
                                <div>
                                    <p>Participants</p>
                                    <input type="number" class="input_fields" name="participants" required>
                                </div>
                                <div>
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
                                <div style="display: flex; width: 100%; justify-content: flex-end; border-top: 1px solid black;">
                                    <button type="button" name="cancel" class="blue_buttons" onclick="window.location.href='org_createAct.php'">Cancel</button>
                                    <button type="submit" name="submit" class="blue_buttons">Submit</button>
                                </div>
                            </div>
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

        //DISTANCE FIELD FORMAT
        document.querySelector('form').addEventListener('submit', function(event) {
            const value = document.getElementById('distance_value').value;
            const unit = document.getElementById('distance_unit').value;
            
            if (value) {
                document.getElementById('combined_distance').value = value + ' ' + unit;
            }
        });


        document.addEventListener('DOMContentLoaded', function() {
        const addBtn = document.getElementById('add_pickup_btn');
        const viewBtn = document.getElementById('view_pickup_btn');
        const pickupInput = document.getElementById('pickup_input');
        const pickupDropdown = document.getElementById('pickup_dropdown');
        const pickupList = document.getElementById('pickup_list');
        const hiddenInput = document.getElementById('pickup_locations_hidden');

        const pickupPoints = [];

        // Add button for input
        addBtn.addEventListener('click', function() {
            const point = pickupInput.value.trim();
            if (point && !pickupPoints.includes(point)) {
                pickupPoints.push(point);
                updatePickupList();
                updateHiddenInput();
            }
            pickupInput.value = '';  // Clear input after adding
        });

        // Keyboard Enter key for adding point
        pickupInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBtn.click();  // Trigger the same action as clicking the "+" button
            }
        });

        // Dropdown visibility toggle
        viewBtn.addEventListener('click', function() {
            const isVisible = pickupDropdown.style.display === 'block';
            pickupDropdown.style.display = isVisible ? 'none' : 'block';
            viewBtn.setAttribute('aria-expanded', !isVisible);
        });

        // Close dropdown if clicked outside
        document.addEventListener('click', function(e) {
            if (!e.target.matches('#view_pickup_btn') && 
                !e.target.matches('#pickup_dropdown') && 
                !e.target.closest('#pickup_dropdown')) {
                pickupDropdown.style.display = 'none';
            }
        });

        // Update the pickup list display
        function updatePickupList() {
            pickupList.innerHTML = '';
            
            if (pickupPoints.length === 0) {
                const li = document.createElement('li');
                li.textContent = 'No pickup points added';
                li.style.padding = '10px';
                li.style.color = '#777';
                pickupList.appendChild(li);
            } else {
                pickupPoints.forEach((point, index) => {
                    const li = document.createElement('li');
                    li.style.padding = '8px 10px';
                    li.style.borderBottom = '1px solid #eee';
                    li.style.display = 'flex';
                    li.style.justifyContent = 'space-between';
                    li.style.alignItems = 'center';
                    
                    const pointText = document.createElement('span');
                    pointText.textContent = point;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = '×';
                    removeBtn.style.background = 'none';
                    removeBtn.style.border = 'none';
                    removeBtn.style.color = 'red';
                    removeBtn.style.fontSize = '18px';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.onclick = function() {
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

        function updateHiddenInput() {
            hiddenInput.value = JSON.stringify(pickupPoints);
        }

        // Initialize
        updatePickupList();
        updateHiddenInput();
    });

    </script>
</body>
</html>