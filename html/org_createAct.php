<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Activity</title>

    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 

    <style>
        body {
            
        }
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
            background: linear-gradient(to right, #007BFF, #A020F0); /* Blue to Purple */
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
<body>

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
        <div class="nav_right">   
            <input type="text" placeholder = "Search" class = "searchBar">
            <input type="button" class = "signup_button" value="Sign Up" onclick="openModal('modal_selection', event)">
        </div>          
    </nav>
    <!--NAVBAR END -->

    <div class="container" style=" padding-top: 7vh; display: flex; justify-content: center; background-color: lightgrey; height: 100vh; position: relative;">
        <div class="content" style="padding: 50px;">
            <h1 style=" margin-bottom: 10px; font-size: 2.4em;">Create your own Activity and feel the Thrill!</h1>
            <form action="">
                <div style="display: flex; justify-content: space-between; align-items: baseline; padding: 30px; border: 2px solid black; border-radius: 20px; width: 1200px; background-color: white;">
                    <div class="left" style="display: flex; flex-direction: column; justify-content: space-between; gap: 20px;">
                        <div>
                            <p>Activity Name</p>
                            <input type="text" class="input_fields">
                        </div>
                        <div style="width:100%; position: relative;">
                            <p>Images</p>
                            <div class="file-upload" onclick="document.getElementById('image').click();" style=" width:100%;">
                            <img src="../imgs/icon_image.png" alt="" style=" height: 100px; width: 100px;">
                            </div>
                            <input type="file" name="image" id="image" accept="image/*" multiple onchange="previewImages(event)"> 
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
                            <span ><textarea style="width: 100%; border: 2px solid black; border-radius: 10px; resize:none; padding: 20px;" name="" id="" cols="30" rows="10"></textarea></span>
                        </div>
                        <div style=" display: flex; justify-content: space-between; gap: 30px;">
                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                <div>
                                    <p>Location</p>
                                    <input type="text" class="input_fields">
                                </div>
                                <div>
                                    <p>Date</p>
                                    <input type="date" class="input_fields">
                                </div>
                                <div>
                                    <p>Distance</p>
                                    <input type="text" class="input_fields">
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                <div>
                                    <p>Difficulty</p>
                                    <input type="text" class="input_fields">
                                </div>
                                <div>
                                    <p>Participants</p>
                                    <input type="text" class="input_fields">
                                </div>
                                <div style="display: flex; width: 100%; justify-content: flex-end;">
                                    <button class="blue_buttons">Cancel</button>
                                    <button class="blue_buttons">Submit</button>
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
    </script>
</body>
</html>