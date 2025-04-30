<?php
session_start();
$error_message = "";
require_once 'includes/formHandler.php';
require_once 'includes/dbCon.php';
if (isset($_POST["submit"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $result = loginUser ($pdo, $email, $password);

    if ($result['success']) {
        $_SESSION["login"] = true;
        $_SESSION["id"] = $result['user']['id'];
        $_SESSION["email"] = $email;

        if (isset($result['user']['orgname'])) {
            $_SESSION["orgname"] = $result['user']['orgname'];
            header("Location: org_homePage.php");
        } else {
            header("Location: joiner_homePage.php");
        }
        exit();
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JOYn: Login</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css">
    <style>
        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            font-size: 2vh;
        }
        .container {
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('../imgs/landpage_bg.jpg');
            background-size: cover;
            background-position: center;
        }
        .login_tile {
            padding: 50px;
            width: 400px;
            text-align: center;
            border-radius: 20px;
            background-color: white;
            box-shadow: 0 4px 6px 2px rgba(0, 0, 0, 0.5);
        }
        .login_tile h1 {
            font-size: 2rem;
        }     
        .login_input {
            width: 250px;
            border-radius: 10px;
            border: 1px solid gray;
            margin: 5px;
            padding: 5px;
        }
        input::placeholder {
            color: gray; 
            opacity: 1; 
        }
        .button {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            width: 250px;
        }
        .login_button {
            background-color: rgb(0,180,0);
            border: none;
            padding: 11px;
            border-radius: 10px;
            color: white;
            cursor: pointer;
            width: 100%;
            margin: auto;
        }
        .login_button:hover {
            background-color: rgb(0,120,0);
        }
        #input_tile {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .button_arrow {
            height: 20px;
            width: 20px;
            background: url('http://localhost/JOYn/JOYn/imgs/icon_backarrow.png') no-repeat center center;
            background-size: contain;
            border:none;
            cursor: pointer;

            display: flex;
            justify-content: flex-start;
            margin-left: -15px;
            margin-top: -20px;
        }
    </style>
</head>
<body>

    <nav id="navbar_login">
        <div class="nav_left">
            <ul class="navbar">
                <li><input type="button" class="logo"></li>
                <li><a href="">Home</a></li>
                <li><a href="">Activity</a></li>
                <li><a href="">Forum</a></li>
                <li><a href="">Marketplace</a></li>
                <li><a href="">Notification</a></li>
            </ul>
        </div>
        <div class="nav_right">   
            
        </div>          
    </nav> 

    <div class="container">
        <div class="login_tile">
            <div class="icon_arrow">
                    <button class = "button_arrow" onclick="window.location.href=window.location.href='landing_page.php';"></button>
            </div>
            <h1>Login</h1>
            <?php if (isset($_GET['error'])): ?>
                <div id="toast" style="display: block;">
                    <span id="toast-message"><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>
            <form action="" method="post" autocomplete="off" id="input_tile">
                <input type="email" name="email" placeholder="Email Address" class="login_input" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" >
                <input type="password" name="password" placeholder="Password" class="login_input" required> 
                <div class="button">
                    <button type="submit" name="submit" class="login_button">Continue</button>
                </div>                  
            </form>           
        </div>
    </div>

    <div id="toast" style="display: none; position: fixed; background-color:red; color: white; padding: 10px; border-radius: 5px; bottom: 20px; right: 20px;">
        <span id="toast-message"></span>
    </div>
    <script>
        const errorMessage = "<?php echo addslashes($error_message); ?>";

        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        toastMessage.innerText = errorMessage;
        toast.style.display = 'block';

        document.querySelector('input[name="password"]').value = '';

        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000); 
    </script>
</body>
</html>
