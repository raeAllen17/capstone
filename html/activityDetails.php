<?php
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/activity_store.php';

$joiner_id = $_SESSION['id'];

if (!isset($_SESSION['id'])) {
    header('Location: landing_page.php');
    exit();
}

//SESSION MESSAGES 
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

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT current_participants, participants, org_id FROM activities WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $activityParticipant = $stmt->fetch(PDO::FETCH_ASSOC);

    $currentParticipants = $activityParticipant['current_participants'];
    $totalParticipants = $activityParticipant['participants'];

    $isDisabled = ($currentParticipants >= $totalParticipants);

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
}

$qrCodeData = displayQRCodes($pdo, $org_id);
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
    ::-webkit-scrollbar {
    width: 12px; 
    }
    ::-webkit-scrollbar-track {
        background: transparent; 
    }
    ::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.5); 
        border-radius: 10px;
        border: 3px solid transparent;
    }
    </style>
</head>
<body style="height: 100vh; width: 100%;">

    <span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
    <span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    

    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li style=" border-bottom: 2px solid green;"><a href="joiner_homePage.php">Home</a></li>
                    <li><a href="joiner_activityPage.php" id="act-nav">Activity</a></li>
                    <li><a href="joiner_forumPage.php" id="forum-nav" >Forum</a></li>
                    <li><a href="joiner_marketplace.php" id="marketplace-nav">Marketplace</a></li>                    
                    <li><a href="joiner_notification.php" id="marketplace-nav">Notification</a></li>                    
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
                            <h2 style=" color: lightblue;">₱<?php echo htmlspecialchars($activities['price']); ?></h2>
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
            <div style=" height: auto; width: 800px; background-color: white; border: 2px solid black; border-radius: 20px; padding: 10px; display: flex; flex-direction:column;">
                <div style="">
                    
                </div>
                <div style=" height: 400px; width: 100%; border-radius: 10px; overflow: auto; padding: 20px; box-shadow: inset 3px 3px 8px 5px rgba(0, 0, 0, 0.3);">
                    <h2>Terms and Conditions for Travel Events</h2><br>           
                    <p><strong>1. Reservation and Payment Requirements</strong><br>To secure a spot in any travel event, individuals must pay either 50% of the total cost or the full amount in advance. This payment serves as confirmation of their reservation. The deposit must be completed at least 12 days before the scheduled trip. Additionally, participants must present proof of deposit on the day of travel. Any remaining balance must be fully settled before departure.</p><br>
                    <p><strong>2. Availability of Slots and Adjustments </strong><br>Seat availability is updated in real-time, and slots may be adjusted based on the trip's logistics and the number of confirmed participants. Travelers are advised to secure their reservations early to ensure participation.</p><br>
                    <p><strong>3. Rescheduling Due to Minimum Guest Requirement</strong><br>In cases where the number of registered participants falls below nine (9), the organizer has the right to reschedule the trip. If the trip is rescheduled, payments already made may be transferred to the new travel date.</p><br>
                    <p><strong>4. Cancellations Due to Unforeseen Circumstances</strong><br>The organizer reserves the right to cancel or reschedule a trip due to weather conditions or other unavoidable situations. These changes may occur up to a day before the scheduled departure for safety and logistical reasons.</p><br>
                    <p><strong>5. Refund Policy and Deduction Fees</strong><br>• If a participant cancels between one month and 12 working days before the event, a Php 400 deduction applies to their initial payment. The remainder will be refunded. <br> • If a participant withdraws at the last minute, they must pay the full amount unless they transfer their slot and payment to a designated proxy. <br> • Payments cannot be transferred between different participants already part of the trip.</p><br>
                    <p><strong>6. Rules for Transferring Reserved Slots</strong><br>• A participant may transfer their reserved slot to another person going to the same destination—provided the organizer is notified at least 10 working days before departure. Each reservation can only be transferred once.<br> • Transfers between different trips or destinations are not allowed within 10 days of departure. Once inside this period, payments cannot be moved to another trip. <br> • A participant can transfer their slot and deposit to another individual—as long as the recipient is not already registered for the scheduled trip.</p><br>
                    <p><strong>7. Refund Guidelines for Organizer-Initiated Cancellations</strong><br>• If the organizer cancels or reschedules a trip due to bad weather, low attendance, or other external reasons, participants can: a. Transfer their payment to another trip the organizer is offering. b. Apply the payment toward the rescheduled trip. <br> • If a trip is canceled mid-travel due to uncontrollable factors, the organizer may issue a refund, deducting reasonable fees already paid.</p><br>
                    <p><strong>8. No Refund Conditions</strong><br>Refunds will not be given under the following circumstances: <br> • The participant fails to arrive on the scheduled travel date. <br> • The participant does not arrive on time for departure. <br> • No request for slot transfer was submitted, and the participant misses the trip entirely. <br></p><br>
                    <p><strong>9. Use of Photos and Media Content</strong><br> All photos and videos captured during the trip may be used by the organizer for promotional and marketing purposes.</p><br>
                    <p><strong>10. Right to Refuse Participation</strong><br> The organizer reserves the right to deny or remove participants due to legitimate concerns, including but not limited to: <br> • Disorderly behavior or misconduct <br> •	Serious medical conditions that pose risks during the trip <br> • Intoxication or substance abuse<br> •	Possession of illegal or dangerous items<br></p><br>
                    <p><strong>11. Complaints and Issue Resolution</strong><br> If a participant encounters any issues or concerns during the trip, they must immediately inform the organizer or an authorized representative. Verbal complaints must be followed up in writing and submitted to the trip guide or local agent.</p><br>
                    <p><strong>12. Participant Responsibility and Preparedness</strong><br> • Participants are expected to act responsibly and prepare adequately for the trip based on weather conditions. <br> •	They should bring any necessary medication for personal health needs.<br> •	If a participant has a medical condition, they must inform the organizer before the trip to ensure proper arrangements.<br> • All participants should be physically and mentally capable of undertaking the travel activities<br>.</p><br>
                    <p><strong>13. Liability and Assumption of Risk</strong><br> By joining the trip, participants acknowledge that adventure travel carries risks. In the event of harm, injury, or property loss, participants assume all responsibility and will not hold the organizer liable.</p><br>
                    <p><strong>14. Limitations of Liability</strong><br> The organizer is not responsible for any injuries, illnesses, loss of belongings, or expenses resulting from: <br> • The participant's own actions.<br> • Unavoidable third-party incidents.<br> • Natural disasters or unforeseen circumstances.<br></p><br>
                    <p><strong>15. Non-Contractual Services</strong><br> The organizer is not responsible for any additional services provided by external suppliers that were not part of the contract.</p><br>
                    <p><strong>16. Agreement to Terms and Conditions</strong><br> By participating in the trip, the individual confirms that they have read, understood, and agreed to the organizer’s terms and conditions.</p><br>
                </div>
                <div style=" width: 100%; padding: 10px;">
                    <input type="checkbox" id="terms">
                    <label for="terms">Do you accept the Terms and Conditions?</label>
                </div>
                <div id="pop-up-container" style=" height: 300px; overflow: auto; display: none;">
                    <div class="pop-up" style="padding: 20px;">
                        <h4>As stated in Terms and Conditions above, half of the price are required to be deposited for slot registration.</h4><br>
                        <p>You can send your registrations registration fees here:</p>
                    </div>
                    <div class="pop-up" id="qr-codes" style=" display: flex; justify-content: center; align-items: center; width: 100%;">
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
                    </div>
                    <div style="padding: 10px; width: 100%; text-align: center;">
                        <h4>Activity price: </h4>
                        <h2 style=" color: lightseagreen;">₱<?php echo htmlspecialchars($activities['price']); ?></h2>
                        <h4>Required deposit amount:</h4>
                        <h2 style=" color: lightgreen;">₱<?php echo htmlspecialchars($activities['price']/2); ?></h2>
                    </div>
                    <div style=" padding: 10px; width: 100%; display: grid; place-content: center; margin-bottom: 1vw;">
                        <form action="../html/includes/post_methods.php" id="proofForm" method="POST" enctype="multipart/form-data">
                            <div style=" margin-bottom: 1vw;">
                                <?php
                                    $pickupArray = explode(',', $activities['pickup_locations']);
                                    ?>
                                    <?php if (!empty($pickupArray)): ?>
                                        <label for="pickup_location" style="margin-top: 10px; display: block;">Select Pickup Location:</label>
                                        <select name="pickup_location" id="pickup_location" 
                                                style="padding: 10px; border-radius: 10px; margin-top: 5px; width: 100%;" 
                                                <?php echo $isDisabled ? 'disabled' : ''; ?>>
                                            <?php foreach ($pickupArray as $location): ?>
                                                <option value="<?php echo trim($location); ?>"><?php echo ucfirst(trim($location)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                <?php endif; ?>
                            </div>
                            <div style=" margin-bottom: 1vw;">
                                <input type="file" accept="image/*" name="proof-image" style=" border: 1px solid black; padding: 10px; border-radius: 10px;" <?php echo $isDisabled ? 'disabled' : ''; ?>>
                            </div> 
                            <div style=" width: 100%; display: flex; justify-content: flex-end;">
                                <button style="padding: 10px; color: white; background-color: green; border: none; border-radius: 15px;" type="submit" form="proofForm">Send</button>
                            </div>
                            <input type="hidden" name="org_id" value="<?php echo htmlspecialchars($org_id); ?>">
                            <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($activityId); ?>">
                            <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($userId); ?>">
                        </form>                       
                    </div>
                    <p style=" padding: 1vw; border-top: 1px solid grey; color: #FF8B8B;">
                        **If the image upload button is disabled, it means the slot is currently full and the organizer does not recommend paying downpayment yet. The organizer will notify you when a slot opens. CLICK SEND ANYWAY IF YOU WISH TO LINE UP**
                    </p>
                </div>      
            </div>
        </div>
    </div>

    <script>

        const checkChanged = document.getElementById("terms");
        checkChanged.addEventListener("change", function() {
        if (this.checked) {
            document.getElementById("pop-up-container").style.display = "block";
        } else {
            document.getElementById("pop-up-container").style.display = "none";
        }
        });

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