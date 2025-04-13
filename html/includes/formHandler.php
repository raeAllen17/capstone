<?php
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
        $duplicate_query = "SELECT email FROM account_joiner WHERE email = :email";
        $stmt = $pdo->prepare($duplicate_query);
        $stmt->execute([':email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $result['message'] = "This email is already registered.";
            return $result;
        } 
        
        // Insert new user
        $query = "INSERT INTO account_joiner (firstName, lastName, pwd, email, gender, address, contactnumber, emergencyCname, emergencyCnumber, otp, otp_sent_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?);";

        $stmt = $pdo->prepare($query);
        if ($stmt->execute([$firstName, $lastName, $hashed_password, $email, $gender, $address, $contactNumber, $emergencyConName,$emergencyConNumber, $otp, $otp_sent_at
        ])) {
            $result['success'] = true;
            $result['success_message'] = "Registration successful!";
            $result['otp'] = $otp;
        } else {
            $result['message'] = "Registration failed.";
        }   
    } catch (PDOException $e){
        $result['message'] = "Error: " . $e->getMessage();
    }
    
    return $result;
}