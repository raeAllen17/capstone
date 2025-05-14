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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trade_id'])) {
    if (isset($_POST['reject'])){
        $tradeId = $_POST['trade_id'];
        $stmt = $pdo->prepare("UPDATE trades SET status = 'rejected' WHERE id = :trade_id");
        $stmt->execute(['trade_id' => $tradeId]);
        $_SESSION['success_message']="Trade rejected.";
        header('Location: joiner_tradeOffers.php');
        exit;
    } else if (isset($_POST['accept'])){
        $tradeId = $_POST['trade_id'];
        $stmt = $pdo->prepare("UPDATE trades SET status = 'meetup' WHERE id = :trade_id");
        
        $stmt->execute(['trade_id' => $tradeId]);
        $stmt = $pdo->prepare("
            UPDATE marketplace 
            SET status = 'meetup' 
            WHERE id IN (
                SELECT trade_from_item_id FROM trades WHERE id = :trade_id
                UNION
                SELECT trade_to_item_id FROM trades WHERE id = :trade_id
            )
        ");
        $stmt->execute(['trade_id' => $tradeId]);
        $_SESSION['success_message'] = "Trade accepted and marketplace item status updated to meetup.";
        header('Location: joiner_tradeOffers.php');
        exit;
    }
} else {
}

$trades = getTrades($pdo, $userId);
$tradeStatus = getTradeStatus($pdo, $userId);

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
    </style>
</head>
<body style="height: auto;">
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
        <div style="height: 100%; display: flex; flex-direction: column; align-items:center; width: 100%; position: relative; background: linear-gradient(to right, #a1c4fd, #c2e9fb); position: relative; padding: 7vh;"> 
            <div style="width: 50%; text-align: left; margin-bottom: 1vw;">
                <h1>Trade Offers <br> to look at.</h1>
            </div>    
            <div style="display: flex; justify-content: space-between; align-items: center; width: 45%; margin-bottom: 1vw;">
                <div>
                    
                </div>
                <div style="display: flex; align-items: center; gap: 2vw;">
                    <a href="joiner_marketList.php" id="listing-button">Create Listing</a>                   
                </div>
            </div>
            <div style="height: 300px; width: 50%; border: 2px solid black; border-radius: 20px; background-color: whitesmoke; padding: 1vw; display: flex; flex-wrap: wrap; overflow: auto; gap: 1vw;">
                <?php foreach ($trades as $trade): ?>
                    <div style="border: 1px solid #ccc; padding: 1vw; border-radius: 10px; background-color: white; width: 100%; height: auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div style="width: 30%; height: auto; border: 1px solid black; display: flex; flex-direction: column; align-items: center; gap: 1vw; border-radius: 10px; padding: 1vw;">
                                <?php if (!empty($trade['from_item_blob'])): ?>
                                    <?php 
                                        $fromItemImage = base64_encode($trade['from_item_blob']); 
                                        echo '<img src="data:image/jpeg;base64,' . $fromItemImage . '" alt="From Item Image" style="width: 100%; height: 200px; object-fit: contain;">';
                                    ?>
                                <?php else: ?>
                                    <img src="../imgs/icon_money.png" alt="Default Item Image" style="width: 100%; height: 200px; object-fit: contain;">
                                <?php endif; ?>
                                <p><strong><?= htmlspecialchars($trade['from_item_name']) ?></strong></p>
                            </div>
                            <p>Trade to:</p>
                            <div style="width: 30%; height: auto; border: 1px solid black; display: flex; flex-direction: column; align-items: center; gap: 1vw; border-radius: 10px; padding: 1vw;">
                                <?php 
                                if (!empty($trade['to_item_blob'])): 
                                    $toItemImage = base64_encode($trade['to_item_blob']); 
                                    echo '<img src="data:image/jpeg;base64,' . $toItemImage . '" alt="To Item Image" style="width: 100%; height: 200px; object-fit: contain;">';
                                endif;
                                ?>
                                <p><strong> <?= htmlspecialchars($trade['to_item_name']) ?></strong></p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; margin-top: 1vw;">
                            <div style="display: flex; align-items: baseline;  flex-direction: column; width: 50%;">
                                <p><strong>From:</strong> <?= htmlspecialchars($trade['from_user_name']) ?> <?= htmlspecialchars($trade['from_user_last_name']) ?> (<?= htmlspecialchars($trade['from_user_location']) ?>)</p>
                                <p><?= (new DateTime($trade['created_at']))->format('l, F j, Y') ?></p>
                            </div>
                            <form action="" method="POST"  style=" width: 50%;  text-align: right;">
                                <input type="hidden" name="trade_id" value="<?= $trade['id'] ?>">
                                <button type="submit" name="reject" class="blue_buttons" style="background: linear-gradient(to right, #ff6b6b, #ffa07a);">Reject</button>
                                <button type="submit" name="accept" class="blue_buttons" style="background: linear-gradient(to bottom, #a8e6cf, #56ab91);">Accept</button>
                            </form>        
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="height: 300px; width: 50%; border: 2px solid black; border-radius: 20px; background-color: whitesmoke; padding: 1vw; display: flex; flex-wrap: wrap; overflow: auto; gap: 1vw; margin-top: 3vw;">
                <?php foreach ($tradeStatus as $statusTrade): ?>
                    <div style="width: 100%; padding: 1vw; border-radius: 10px; 
                        background: 
                            <?php
                                if ($statusTrade['status'] == 'pending') {
                                    echo 'linear-gradient(#6fa3d3)'; 
                                } elseif ($statusTrade['status'] == 'rejected') {
                                    echo 'linear-gradient(#f5c6cb)'; 
                                } elseif ($statusTrade['status'] == 'meetup') {
                                    echo 'linear-gradient(#78d56d)'; 
                                } else {
                                    echo 'white';
                                }
                            ?>; color: white; border: 1px solid #ccc;">
                        <p><strong>Trade to:</strong> <?= htmlspecialchars($statusTrade['to_item_name']) ?></p>
                        <p><strong>Your Item:</strong> <?= htmlspecialchars($statusTrade['from_item_name']) ?></p>
                        <p><strong>Owner:</strong> <?= htmlspecialchars($statusTrade['to_user_name']) ?> <?= htmlspecialchars($statusTrade['to_user_last_name']) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($statusTrade['from_user_location']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($statusTrade['status']) ?></p>
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