<?php 
session_start();
require_once '../html/includes/dbCon.php';
require_once '../html/includes/modHandler.php';
$data = loadData($pdo);
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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'reject') {
        
        $recipient_email = $_POST['recipient_email'];
        $row_id = $_POST['row_id'];
        $documents = isset($_POST['documents']) ? $_POST['documents'] : [];
        $custom_message = isset($_POST['custom_message']) ? $_POST['custom_message'] : '';
    
        rejectRegis($pdo, $documents, $custom_message, $recipient_email, $row_id);
        $_SESSION['success_message'] = "Message sent to the user.";

    } elseif (isset($_POST['action']) && $_POST['action'] == 'accept') {
        $recipient_email = $_POST['recipient_email'];
        $row_id = $_POST['row_id']; 

        addRegis($pdo, $recipient_email, $row_id);
        $_SESSION['success_message'] = "Organizer accepted successfully!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JOYn: Admin Registration</title>
</head>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: Poppins;
    }

    body {
        height: 100vh;
        
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
    .header {
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: left;
        width: 1200px;
    }

    .container {
        height: auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .container_tile {
        box-shadow: 0 4px 6px 4px rgba(0, 0, 0, 0.3);
        border-radius: 20px;
        width: 1200px;
        min-height: 400px;
        padding: 20px;
        background-color: #98a675;
    }

    .container h1 {
        width: 1200px;
        margin-bottom: 10px;
        text-align: left;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background-color: white;
    }
    th, td {
        padding: 10px;
        text-align: left;
    }
    td {
        border-left: 1px solid black;
    }
    th:first-child, td:first-child {
        border-left: none;
    }
    th {
        background-color: #98a675;
        color: beige;
    }
    tbody tr td {
        height: 50px;
    }
    .button_reject {
        background-image: url('http://localhost/JOYn/JOyn/imgs/icon_cross.png');
        background-size: cover;
        background-position: center; 
        height: 30px;
        width: 30px;
        border: none;
        background-color: white;
        cursor: pointer;
        margin: 5px;
    }

    .button_accept {
        background-image: url('../imgs/icon_check.png');
        background-size: cover;
        background-position: center; 
        height: 30px;
        width: 30px;
        border: none;
        background-color: white;
        cursor: pointer;
        margin: 5px;
    }

    .button_reject:hover {
        transform: scale(1.05);
    }
    .button_accept:hover {
        transform: scale(1.05);
    }   
    .modal {
    display: none;
    position: fixed;
    z-index: 1; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0); 
    background-color: rgba(0,0,0,0.4);
    }

    .modal_content {
        border-radius: 20px;
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 500px;
    }
    .icon_arrow {
    display: flex;
    margin: 0;
    padding: 10px;
    }

    .button_arrow {
        height: 20px;
        width: 20px;
        background: url('../imgs/icon_backarrow.png') no-repeat center center;
        background-size: contain;
        border:none;
        cursor: pointer;
    }
</style>

<body>
<span id="errorMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: red; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $errorMessage; ?></span>
<span id="successMessage" style=" position: absolute; top: 10%; left: 50%; transform: translate(-50%); height: 3vw; width: 30vw; background-color: green; z-index: 999; border-radius: 20px; color: white; text-align: center; display: none; justify-content: center; align-items: center;"><?php echo $successMessage; ?></span>    

    <div class="background"></div>
    <section class="header"> 
        <img src= "../imgs/logo.png" alt="" height="250px" id="img_logo">
    </section>
    <div class="container">
        <div class="container_tile">
            <table>
                <thead>
                <tr>
                    <th>Organization</th>
                    <th>CEO</th>
                    <th>Email Address</th>
                    <th>Address</th>
                    <th>Contact No.</th>
                    <th>Documents</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <?php if ($data['success']):?>
                        <?php foreach( $data['data'] as $row):?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['orgname']); ?></td>
                                <td><?php echo htmlspecialchars($row['ceo']); ?></td>
                                <td><?php echo htmlspecialchars($row['orgemail']); ?></td>
                                <td><?php echo htmlspecialchars($row['orgadd']); ?></td>
                                <td><?php echo htmlspecialchars($row['orgnumber']); ?></td>
                                <td>
                                    <?php
                                    $file_paths_array = explode(',', $row['file_paths']);
                                    foreach ($file_paths_array as $file_path) {
                                        $filename = basename($file_path);
                                        $web_accessible_path = '/Capstone/files/' . htmlspecialchars($filename);
                                        echo '<a href="' . $web_accessible_path . '" target="_blank">' . htmlspecialchars($filename) . '</a><br>';
                                    }
                                    ?>                     
                                </td>                      
                                <td>
                                    <button class="button_reject" name="reject" onclick="openModal('modal_reject', '<?php echo htmlspecialchars($row['orgemail']); ?>', '<?php echo htmlspecialchars($row['id']); ?>')"></button>
                                    <button class="button_accept" name="accept" onclick="acceptRegistration('<?php echo htmlspecialchars($row['orgemail']); ?>', '<?php echo htmlspecialchars($row['id']); ?>')"></button>                                  
                                </td>
                            </tr> 
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                        <td colspan="7"><?php echo htmlspecialchars($data['failed_message']); ?></td>
                        </tr> 
                    <?php endif; ?> 
                </tbody>       
            </tbody>
            </table>
        </div>
    </div>

    <div id="modal_reject" class ="modal">      
        <div class="modal_content">
            <div class="icon_arrow">
                <button class = "button_arrow" onclick="closeModal('modal_reject')"></button>
            </div> 
            <form action="" method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="recipient_email" id="recipient_email">
                <input type="hidden" name="row_id" id="row_id">
                <div style="margin: 10px;">
                    <p style="color: grey;">Check documents for resubmission.</p>
                    <label style="font-size: 1.5em;">
                        <input type="checkbox" name="documents[]" value="DOT"> DOT
                    </label> <br>
                    <label style="font-size: 1.5em;">
                        <input type="checkbox" name="documents[]" value="BIR"> BIR
                    </label>
                </div>
                <div>
                    <p style="color: grey;">Add additional message. (Optional)</p>
                    <span style="width: 100%;">
                        <textarea name="custom_message" id="" cols="30" rows="10" style="width: 100%; border-radius: 10px; resize: none; padding: 10px;"></textarea>
                    </span>
                </div>  
                <div style="width: 100%; display: flex; justify-content: flex-end;">
                    <button type="submit" style="color: white; background-color: green; padding: 5px; border-radius: 10px; border: none; height: 50px; width: 100px;">Send</button>
                </div>               
            </form>              
        </div>
    </div>

    <script>

    //pass the value to the modal and the modal passes the value to the function
    function openModal(modalId, recipientEmail, rowId) {      
        const modal = document.getElementById(modalId);
        document.getElementById('recipient_email').value = recipientEmail;
        document.getElementById('row_id').value = rowId;
        modal.style.display = 'block';
    }

    //adding invisble forms to button since it does not contain any form format values which is needed for passing variable using a button
    function acceptRegistration(email, id) {
        if (confirm('Are you sure you want to accept this registration?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const emailField = document.createElement('input');
            emailField.type = 'hidden';
            emailField.name = 'recipient_email';
            emailField.value = email;
            
            const idField = document.createElement('input');
            idField.type = 'hidden';
            idField.name = 'row_id';
            idField.value = id;
            
            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'accept';
            
            form.appendChild(emailField);
            form.appendChild(idField);
            form.appendChild(actionField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'none'; 
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modal_reject');
        if (event.target === modal) {
            closeModal('modal_reject');
        }
    }
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