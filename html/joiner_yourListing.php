<?php
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/formHandler.php';
require_once 'includes/activity_store.php';


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

if(isset($_SESSION['id'])){
    $userId = $_SESSION['id']; 
    $userData = getJoinerUserdata($pdo, $userId);
    $joinerName = $userData['firstName'];
} else {
    header("location: landing_page.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $itemId = $_POST['item_id'];

    $stmt = $pdo->prepare("DELETE FROM marketplace WHERE id = ?");
    $stmt->execute([$itemId]);
    $stmt = $pdo->prepare("DELETE FROM trades WHERE trade_to_item_id = ?");
    $stmt->execute([$itemId]);

    $_SESSION['success_message'] = "Item successfully deleted.";
    header("location: joiner_yourListing.php");
} 

$userItems = getUserListing($pdo, $userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity</title>
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
            border: none;
            color: white;
            padding: 0.3vw 0.5vw;
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
        #listing-button {
            text-decoration: none;
            font-weight: 500;
            color: black;
            background-color: white;
            padding: 0.5vw 2vw;
            border-radius: 10px;
        }
        #listing-button:hover {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
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
        .item-card {
        width: 450px;
        border:1px solid #ccc;
        border-radius: 10px;
        padding: 10px;
        background-color: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: auto;
        }
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }
    </style>
</head>
<body style="height: 100vh; overflow: hidden;">
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
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='joiner_account.php';">  
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>          
    </nav>

    <div style="height: 100%; display: flex; justify-content: center; padding-top: 7vh;">
        <div style="height: 100%; display: flex; flex-direction: column; justify-content:center; align-items:center; width: 100%; position: relative; background: linear-gradient(to right, #a1c4fd, #c2e9fb); position: relative;"> 
            <div style="width: 80%; text-align: left; margin-bottom: 1vw;">
                <h1>Your GEARS <br> to trade. </h1>
            </div>    
            <div style="display: flex; justify-content: space-between; align-items: center; width: 70%; margin-bottom: 1vw;">
                <div>
                    
                </div>
                <div style="display: flex; align-items: center; gap: 2vw;">
                    <select name="" id="" style=" width: 200px; border: 2px solid white;">
                        <option value="" disabled selected>Category</option>
                        <option value="">Gear & Equipment</option>
                        <option value="">Shelter & Sleeping</option>
                        <option value="">Navigation & Safety</option>
                    </select>
                    <a href="joiner_marketList.php" id="listing-button">Create Listing</a>                   
                </div>
            </div>
            <div style="height: 60%; width: 80%; border: 2px solid black; border-radius: 20px; background-color: whitesmoke; padding: 1vw; display: flex; flex-wrap: wrap; overflow: auto; gap: 1vw;">
                <?php foreach ($userItems as $item): ?>
                    <div class="item-card">
                        <?php if (!empty($item['images'])): ?>
                            <img src="data:image/jpeg;base64,<?= $item['images'][0] ?>" alt="Item image" style="width: 100%; height: 300px; object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                            <div style="width: 100%; height: auto; background-color: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">No Image</div>
                        <?php endif; ?>
                        <h3 style="margin: 10px 0 5px;"><?= htmlspecialchars($item['item_name']) ?></h3>
                        <p style="margin: 0; color: blueviolet;">₱ <?= htmlspecialchars($item['price']) ?></p>
                        <p style="margin: 0; color: red;"> <?= htmlspecialchars($item['location']) ?></p>
                        <div style="overflow: auto;">
                            <p style="margin: 0; color: grey;"> <?= htmlspecialchars($item['description']) ?></p>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="blue_buttons" style="background: linear-gradient(to right, #ff6b6b, #ffa07a);">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

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
    </script>
</body>
</html>