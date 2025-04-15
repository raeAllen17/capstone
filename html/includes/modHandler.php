<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function loadData($pdo) {
    $result = [
        'success' => false,
        'failed_message' => '',
        'success_message' => '',
        'data' => []
    ];

    try {
        $query = "SELECT id, orgname, ceo, orgemail, orgadd, orgnumber, file_paths FROM account_org WHERE status = 'pending'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['success'] = true;
    } catch (PDOException $e) {
        $result['failed_message'] = $e->getMessage();
    }

    return $result;
}

function rejectRegis($pdo, $documents, $custom_message, $recipient_email, $row_id) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP(); 
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true; 
        $mail->Username = 'allenretuta10@gmail.com'; 
        $mail->Password = 'uzwk gggt nbff pdlj'; 
        $mail->SMTPSecure = 'tls'; 
        $mail->Port = 587; 

        $mail->setFrom('allenretuta10@gmail.com', 'JOYn');
        $mail->addAddress($recipient_email);
    
        $mail->isHTML(true); 
        $mail->Subject = 'Document Resubmission Notification';

        $message = "The following documents need to be resubmitted:<br><br>";
        
        if (!empty($documents)) {
            $message .= implode(" and ", $documents) . "<br>";
        }
        if (!empty($custom_message)) {
            $message .= "<br>" . nl2br(htmlspecialchars($custom_message)) . "<br>";
        }

        $mail->Body = $message;

        $mail->send();

        $stmt = $pdo->prepare("DELETE FROM account_org WHERE id = :id");
        $stmt->bindParam(':id', $row_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: mod_page.php");
        return true; 
    } catch (Exception $e) {
        return false;
    }
}

function addRegis($pdo, $recipient_email, $row_id) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP(); 
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true; 
        $mail->Username = 'allenretuta10@gmail.com'; 
        $mail->Password = 'uzwk gggt nbff pdlj'; 
        $mail->SMTPSecure = 'tls'; 
        $mail->Port = 587; 

        $mail->setFrom('allenretuta10@gmail.com', 'JOYn');
        $mail->addAddress($recipient_email);
    
        $mail->isHTML(true); 
        $mail->Subject = 'Registration Approved';

        $message = "Congratulations! Your registration has been approved.<br><br>";
        $message .= "You can now log in to your account using your registered email address.<br>";
        $message .= "If you have any questions, please don't hesitate to contact us.<br><br>";
        $message .= "Thank you for joining JOYn!";

        $mail->Body = $message;

        $mail->send();

        $stmt = $pdo->prepare("UPDATE account_org SET status = 'active' WHERE id = :id");
        $stmt->bindParam(':id', $row_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: mod_page.php");
        return true; 
    } catch (Exception $e) {
        return false;
    }
}