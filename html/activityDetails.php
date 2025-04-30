<?php
require_once 'includes/dbCon.php';
require 'includes/activity_store.php';


if (isset($_GET['id'])) {

    $activityId = intval($_GET['id']);
    $activities = getactivities($pdo, $activityId);
    if (!$activities) {
        $activities = [];
    }
    
    $org_id = $activities['org_id'];

    $stmt = $pdo->prepare("SELECT orgname FROM account_org WHERE id = :org_id");
    $stmt->bindParam(':org_id', $org_id, PDO::PARAM_INT);
    $stmt->execute();
    $organization = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($organization) {
        $orgname = $organization['orgname'];
        $activities['orgname'] = $orgname;
    } else {
        $activities['orgname'] = "Unknown Organization";
    }

    $images = [];
    if (!empty($activities['images'])) {
        $imagesString = $activities['images'][0]; 

        $paths = explode(',', $imagesString);

        foreach ($paths as $path) {
            $basename = basename(trim($path));
            $images[] = '../uploads/' . $basename;
        }
    }
} else {

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Details</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css">
    
    <style>
        .slideshow {
        position: relative;
        height: 500px;
        width: 700px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        overflow: hidden;
    }
    .slideshow::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100px;
        background: linear-gradient(to right, rgba(124,124,124,1), rgba(0,0,0,0));
        pointer-events: none;
        z-index: 2;
    }
    #join-button {
        transition: transform 0.2s ease-in;
    }
    #join-button:hover {
        transform: scale(1.05);
    }
    </style>
</head>
<body style="height: 100vh; width: 100%;">

    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li style=" border-bottom: 2px solid green;"><a href="" >Home</a></li>
                    <li><a href="" >Activity</a></li>
                    <li><a href="" >Forum</a></li>
                    <li><a href="" >Marketplace</a></li>
                    <li><a href="" >Notification</a></li>
                </ul>
            </div>
            <div class="nav_right">                 
            </div>          
    </nav>

    <div class="container" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; width: 100%; gap: 30px; padding-top: 7vh;">  
        <div class="activities-list" style="width: 100%; display: grid; place-content: center;">
            <div style="width: 100%; font-size: 1.5em;">
                <h1 style="color: rgb(0, 80 ,0); font-size: 2.3em;">Select and join the <br> adventure now!</h1>
            </div>  
            <?php if (!empty($activities)): ?>
                <div style="background-color: gray; height: 500px; width: 1600px; border-radius: 20px; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                    <div style="background-image: linear-gradient(to right, rgba(0,0,0,1), rgba(255,0,0,0)); height: 500px; width: 900px; 
                     display: flex; flex-direction: column;justify-content: space-around;">
                        <div style=" color: white; padding-left: 50px; padding-top: 50px; display: flex; align-items: center; gap: 20px;">
                            <h1><?php echo htmlspecialchars($activities['activity_name']); ?></h1>
                            <p>by: <?php echo htmlspecialchars($activities['orgname']); ?></p>
                        </div>
                        <div style="color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; width: 600px; padding: 20px; text-align: center;">
                            <p style="white-space: normal; line-height: 1.5; margin: 0; font-size: 1.2em; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);"> 
                                <?php echo htmlspecialchars($activities['description']); ?>
                            </p>
                        </div>
                        <div style="color: white; width: 900px; display: flex; justify-content: center; align-items: center; gap: 70px;">
                            <h2><?php echo htmlspecialchars($activities['difficulty']); ?></h2>
                            <h2><?php echo htmlspecialchars($activities['distance']); ?></h2>
                            <h2><?php echo htmlspecialchars($activities['current_participants']); ?>/<?php echo htmlspecialchars($activities['participants']); ?></h2>
                            <h2><?php $date = new DateTime($activities['date']); echo $date->format('F j, Y');?></h2>
                        </div>
                        <div style="width: 100%; margin-left: 25%;">
                            <button type="button" id="3d-button" style="padding: 10px; border-radius: 10px; border: none; cursor: pointer;">3D MAP</button>
                        </div>
                    </div>
                    <div class="slideshow" style="background-image: linear-gradient(to right, rgba(0,0,0,0), rgba(210,210,210,1)); height:500px; width: 700px;">
                        <button id="join-button" style="position: absolute; bottom: 30px; right: 30px; padding: 10px 20px; background-color: rgba(0, 80, 0, 0.8); color: white; border: none; border-radius: 10px; cursor: pointer;"
                            onclick="showModal('modal-overlay')"> JOIN
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <h1>Sorry, no activities available at this time.</h1>
                <p style="color:gray;">Try refreshing the page or logging in again.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(128, 128, 128, 0.7); display: none; justify-content: center; align-items: center; z-index: 999;">
        <div id="modal-box" id="participate-modal" style=" position: absolute; top: 50%; left: 50%; z-index: 3; transform: translate(-50%, -50%); display: none ;'">
            <div style="height: 500px; width: 400px; background-color: white; border: 2px solid black; border-radius: 20px; padding: 10px;">
                <div style="">
                    <!-- all data including picture and a submission of proof of payment-->
                </div>
            </div>
        </div>
    </div>

    <script>
        const images = <?php echo json_encode($images); ?>;
        let currentIndex = 0;

        function showSlide() {
            const div = document.querySelector(".slideshow");
            div.style.backgroundImage = `url('${images[currentIndex]}')`;
            div.style.backgroundSize = "cover"; 
            div.style.backgroundPosition = "center"; 

            currentIndex = (currentIndex + 1) % images.length;
            setTimeout(showSlide, 1000);
        }
        document.addEventListener("DOMContentLoaded", showSlide);

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
    </script>
</body>
</html>