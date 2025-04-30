<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function registerUser($pdo, $userData) {
    // RETURN RESULT VALUES
    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
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
    $otp_sent_at = (new DateTime())->format('Y-m-d H:i:s');
    
    try {
        // Check for duplicate email
        $duplicate_query = "
            SELECT 'org' AS source, status, orgemail 
            FROM account_org 
            WHERE orgemail = ?
            UNION ALL
            SELECT 'joiner' AS source, status, email 
            FROM account_joiner 
            WHERE email = ?;
        ";
        $stmt = $pdo->prepare($duplicate_query);
        $stmt->execute([$email, $email]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                if ($user['source'] === 'joiner') {
                    if ($user['status'] == 'pending') {
                        $result['failed_message'] = "This email is pending for approval.";
                        $_SESSION['failed_message'] = $result['failed_message']; 
                        header("Location: joiner_otp.php");
                        exit(); 
                    } elseif ($user['status'] === 'active') {             
                        $result['failed_message'] = "This email is already registered and is active. Try logging in instead.";
                        $_SESSION['failed_message'] = $result['failed_message']; 
                        header("Location: login.php");
                        exit(); 
                    }
                } elseif ($user['source'] === 'org') {
                    if ($user['status'] == "active") {
                        $result['failed_message'] = "This email is already registered as organizer account. Try logging in instead.";
                        $_SESSION['failed_message'] = $result['failed_message']; 
                        header("Location: landing_page.php");
                        exit();
                    }  
                }
            }
        } 
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
    } catch (PDOException $e){
        $result['message'] = "Error: " . $e->getMessage();
    }
    
    return $result;
}

function joiner_otp($pdo, $userData){
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
    ];
    
    $email = $_SESSION['email'];
    $otp = $userData['otp'];

    try {
        $query = "SELECT otp,email FROM account_joiner WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount()>0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['otp'] == $otp){
                $otp_sent_at = new DateTime($user['otp_sent_at']);
                $current_time = new DateTime();
                $interval = $otp_sent_at->diff($current_time);

                if($interval->i < 5 && $interval-> h == 0){
                    $update_query = "UPDATE account_joiner SET status = 'active' WHERE email = :email";
                    $stmt= $pdo->prepare($update_query);
                    $stmt->execute([':email' => $email]);

                    $result['success'] = true;
                    $result['success_message'] = "OTP verified successfully. You will be redirected to home page.";
                } else {
                    $result['failed_message'] = "OTP has expired. Please request a new OTP.";
                }     
            } else {
                $result['failed_message'] = "OTP mismatch, try again or resend OTP.";
            }
        } else {
            $result['failed_messagee'] = "No user found.";
        }

    } catch (PDOException $e) {
        $result['failed_message'] = "Error: " . $e->getMessage();
    }
    return $result;
} 

function resendOtp($pdo){
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $email = $_SESSION['email'];

    $result = [
        'success' => false,
        'message' => '',
        'success_message' => '',
    ];

    $otp = random_int(100000, 999999);
    $otp_sent_at = (new DateTime())->format('Y-m-d H:i:s');

    try {
        $query = "UPDATE account_joiner SET otp = :otp, otp_sent_at = :otp_sent_at WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt -> execute([
            ':otp' => $otp,
            ':otp_sent_at' => $otp_sent_at,
            ':email' => $email
        ]);

        if ($stmt->rowCount() > 0) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true; 
            $mail->Username = 'allenretuta10@gmail.com'; 
            $mail->Password = 'uzwk gggt nbff pdlj';
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587; 

            $mail->setFrom('allenretuta10@gmail.com', 'JOYn');
            $mail->addAddress($email);
            $mail->isHTML(true); 


            $mail->Subject = "Your New OTP Code";
            $mail->Body    = "Your new OTP code is: <b>$otp</b>. Please do not share this code with anyone. The code expires within a few minutes.";
            $mail->AltBody = "Your new OTP code is: $otp";

            $mail->send();

            $result['success'] = true;
            $result['success_message'] = "OTP has been resent to your email.";
        } else {
            $result['message'] = "Failed to update OTP in the database.";
        }
    } catch (PDOException $e) {
        $result['message'] = "Error: " . $e->getMessage();
    } catch (Exception $e) {
        $result['message'] = "Message could not be sent. Mailer Error: " . $e->getMessage();
    }
    return $result;
}

function registerOrg($pdo, $userData){
    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
    ];

    $orgname = $userData['orgname'];
    $orgpass = $userData['orgpass'];
    $orgemail= $userData['orgemail'];
    $ceo = $userData['CEO'];
    $orgadd = $userData['orgadd'];
    $orgnumber = $userData['orgnumber'];

    $duplicate_query = "
        SELECT 'org' AS source, status, orgemail 
        FROM account_org 
        WHERE orgemail = ?
        UNION ALL
        SELECT 'joiner' AS source, status, email 
        FROM account_joiner 
        WHERE email = ?;
    ";
    $stmt = $pdo->prepare($duplicate_query);
    $stmt->execute([$orgemail, $orgemail]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            if ($user['source'] === 'org') {
                if ($user['status'] == 'pending') {
                    $result['failed_message'] = "This email is pending for approval.";
                    $_SESSION['failed_message'] = $result['failed_message']; 
                    header("Location: landing_page.php");
                    exit(); 
                } elseif ($user['status'] === 'active') {             
                    $result['failed_message'] = "This email is already registered and is active. Try logging in instead.";
                    $_SESSION['failed_message'] = $result['failed_message']; 
                    header("Location: landing_page.php");
                    exit(); 
                }
            } elseif ($user['source'] === 'joiner') {
                if ($user['status'] == "active") {
                    $result['failed_message'] = "This email is already registered in the joiner account. Try logging in instead.";
                    $_SESSION['failed_message'] = $result['failed_message']; 
                    header("Location: landing_page.php");
                    exit();
                }  
            }
        }
    } 

    $file_paths = [];
    if (!empty($_FILES['orgpdf']['name'][0])) { 
        foreach ($_FILES['orgpdf']['name'] as $index => $file_name) {
            $file_tmp = $_FILES['orgpdf']['tmp_name'][$index];
            $file_error = $_FILES['orgpdf']['error'][$index];
            $upload_dir = "C:/xampp/htdocs/Capstone/files/";
            $file_path = $upload_dir . basename($file_name);
    
            if ($file_error === 0) {
                if (move_uploaded_file($file_tmp, $file_path)) {
                        $file_paths[] = $file_path;
                } else {
                        echo "<script>alert('Failed to move uploaded file: $file_name');</script>";
                }
            } else {
                    echo "<script>alert('Error uploading file: $file_name. Error code: $file_error');</script>";
            }
        }
    } else {
        echo "<script>alert('No files uploaded.');</script>";
    }
    $file_paths_string = implode(",", $file_paths);

    $hashed_password = password_hash($orgpass, PASSWORD_DEFAULT);

    $query = "INSERT INTO account_org (orgname, orgpass, orgemail, ceo, orgadd, orgnumber, file_paths) VALUES (?,?,?,?,?,?,?);";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$orgname, $hashed_password, $orgemail, $ceo, $orgadd, $orgnumber, $file_paths_string])){
        $result['success'] = true;
        $result['success_message'] = "Registration is sent for approval!";
        $_SESSION['success_message'] = $result['success_message'];

        header("Location: landing_page.php");
        exit();      
    } else {
        echo "error";
    }   
    return $result;
}

function loginUser ($pdo, $email, $password) {
    
    $result = [
        'success' => false,
        'message' => '',
        'joiner_fail' => true,
        'user' => null
    ];

    $stmt_joiner = $pdo->prepare("SELECT * FROM account_joiner WHERE email = :email");
    $stmt_joiner->execute(['email' => $email]);
    $row_joiner = $stmt_joiner->fetch(PDO::FETCH_ASSOC);

    $stmt_organization = $pdo->prepare("SELECT * FROM account_org WHERE orgemail = :email");
    $stmt_organization->execute(['email' => $email]);
    $row_organization = $stmt_organization->fetch(PDO::FETCH_ASSOC);

    
    if ($row_joiner > 0) {
        if (password_verify($password, $row_joiner["pwd"])) {
            if ($row_joiner['status'] == 'active'){
                $result['success'] = true;
                $result['user'] = $row_joiner;
            } else {
                $result['message'] = "The user registration is currently pending.";

                header("location: joiner_otp.php");
                exit();
            }
        } else {
            $result['message'] = 'Incorrect Password!';
            
        }
    } elseif ($row_organization > 0) {
        if (password_verify($password, $row_organization["orgpass"])) {
            if ($row_organization['status'] == 'active') {
                $result['success'] = true;
                $result['user'] = $row_organization;
            header("Location: org_createAct.php");
            } else {
                $result['message'] = 'Your account is still pending for approval!';
            }   
        } else {
            $result['message'] = 'Incorrect Password!';
        }
    } else {
        $result['message'] = 'User  is not registered';
    }
    return $result; 
}

function getOrgname($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT orgname FROM account_org WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $orgname = $stmt->fetch(PDO::FETCH_COLUMN);
    return $orgname;
}

function getUserdata($pdo, $userId) {

    $stmt = $pdo->prepare("SELECT * FROM account_org WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    return $userData;
}

function getJoinerUserData($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT * FROM account_joiner WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    return $userData;
}
