<?php 

if (!isset($_SESSION['email'])) {
    header("Location: landing_page.php"); 
    exit();
} else {
    
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
            filter: blur(10px);
            z-index: -1; 
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
            position: relative; 
            z-index: 1;
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
    <div class="background"></div> <!-- Background Image with Blur -->
    <div class="container">
        <h2>VERIFICATION</h2>
        <div class="message">
            
        </div>
        <form method="POST" action="">
            <input type="text" name="otp" placeholder="Enter OTP" required pattern="\d{6}" title="Please enter a 6-digit OTP" maxlength="6">
            <button id="continue_button" type="submit" name="verify_otp">Continue</button>
        </form>
        <form method="POST" action="" style="margin-top: 10px;">
            <button id="resendotp_button" type="submit" name="resend_otp">Resend OTP</button>
        </form>
    </div>
</body>
</html>