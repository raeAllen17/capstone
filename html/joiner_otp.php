<?php 
session_start();
$email = $_SESSION['email'];
if (!isset($_SESSION['email'])) {
    header("Location: landing_page.php"); 
    exit();
} else {

    require "../html/includes/dbCon.php";
    require "../html/includes/formHandler.php";

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['verify_otp'])) {
            $userData = [
                'otp' => $_POST['otp']
            ];

            $result = joiner_otp($pdo, $userData);

            if ($result['success']) {
                $message = $result['success_message'];
                header("Location: joiner_homePage.php");
                exit();
            } else {
                $message = $result['failed_message'];
            }
        }

        
        if (isset($_POST['resend_otp'])) {
            $result = resendOtp($pdo); 

            if ($result['success']) {
                $message = $result['success_message']; 
            } else {
                $message = $result['message'];
            }
        }
    }
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
    <title>JOYn: Verification</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        * {
            font-family: Poppins;
        }
        body {
            margin: 0;
            height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;       
            background-image: url('http://localhost/JOYN/JOYn/imgs/landpage_bg.jpg');
            background-size: cover;
            background-position: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
            position: relative; 
            background-color: transparent;
            background: rgba(255, 255, 240, 0.2);
            backdrop-filter: blur(10px);
        }
        input[type="text"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 80%;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            color: red;
            margin-bottom: 10px;
        }
        #resendotp_button {
            color: blue;
            background-color: white;
            text-decoration: underline;
        }

        #continue_button {
            border-radius: 10px;
        }

    </style>
</head>

<body>
<span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
<span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    
    <div class="background"></div> 
        <div class="container">
            <h2>VERIFICATION</h2>
            <div class="message">
                <p><?php echo $message?></p>
            </div>
            <form method="POST" action="">
                <input type="text" name="otp" placeholder="Enter OTP" required pattern="\d{6}" title="Please enter a 6-digit OTP" maxlength="6">
                <button id="continue_button" type="submit" name="verify_otp">Continue</button>
            </form>
            <form method="POST" action="" style="margin-top: 10px;">
                <button id="resendotp_button" type="submit" name="resend_otp">Resend OTP</button>
            </form>
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