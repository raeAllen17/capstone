<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function registerUser($pdo, $userData) {
    // RETURN RESULT VALUES
    $result = [
        'success' => false,
        'message' => '',
        'success_message' => '',
        'otp' => null
    ];
    
    
    $firstName = $userData["firstname"];
    $lastName = $userData["lastname"];
    $password = $userData["password"];
    $email = $userData["email"];
    $gender = $userData["gender"];
    $address = $userData["address"];
    $contactNumber = $userData["contactnumber"];
    $emergencyConName = $userData["emergencyConName"];
    $emergencyConNumber = $userData["emergencyConNumber"];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $otp = random_int(100000, 999999);
    $otp_sent_at = date('Y-m-d H:i:s');
    
    try {
        // Check for duplicate email
        $duplicate_query = "SELECT status,email FROM account_joiner WHERE email = :email";
        $stmt = $pdo->prepare($duplicate_query);
        $stmt->execute([':email' => $email]);
        
        if ($stmt->rowCount() > 0) {      
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                if ($user['status'] == 'pending') {
                    header("Location: admin_otp.php");
                    exit();
                } elseif ($user['status'] === 'active') {
                    $result['message'] = "This email is already registered and is active. Try logging in instead.";
                    return $result;  
                }               
            }
        } else {
            $query = "INSERT INTO account_joiner (firstName, lastName, pwd, email, gender, address, contactnumber, emergencyCname, emergencyCnumber, otp, otp_sent_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?);";
    
            $stmt = $pdo->prepare($query);
            if ($stmt->execute([$firstName, $lastName, $hashed_password, $email, $gender, $address, $contactNumber, $emergencyConName,$emergencyConNumber, $otp, $otp_sent_at
            ])) {
                try {
                    $result['success'] = true;
                    $result['success_message'] = "Registration successful!";
                    
                    $mail = new PHPMailer(true);

                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; 
                    $mail->SMTPAuth = true; 
                    $mail->Username = 'allenretuta10@gmail.com'; 
                    $mail->Password = 'uzwk gggt nbff pdlj';
                    $mail->SMTPSecure = 'tls'; 
                    $mail->Port = 587; 

                    $mail->setFrom('allenretuta10@gmail.com', 'JOYn');
                    $mail->addAddress($email, $firstName);
                    $mail->isHTML(true); 

                    $mail->Subject = "Your OTP Code";
                    $mail->Body    = "Your OTP code is: <b>$otp</b>Please do not share this code with anyone.The code expires within duration of miutes but you can click resend if the code expires.";
                    $mail->AltBody = "Your OTP code is: $otp";

                    $mail->send();
                    
                    $_SESSION['email'] = $email;
                    header("Location: joiner_otp.php");
                    exit();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                
            } else {
                echo "Message could not be sent. Mailer Error.";
            }  
        }          
    } catch (PDOException $e){
        $result['message'] = "Error: " . $e->getMessage();
    }
    
    return $result;
}