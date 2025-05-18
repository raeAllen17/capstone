<?php
session_start();
require_once 'includes/dbCon.php';
require_once 'includes/activity_store.php';
require_once 'includes/formHandler.php';

// Capture filter values from the form submission
$price = isset($_POST['price']) ? $_POST['price'] : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';
$distance = isset($_POST['distance']) ? $_POST['distance'] : '';
$availability = isset($_POST['availability']) && $_POST['availability'] == 'yes' ? 'yes' : '';
// Display activities with filters passed
$data = displayActivity($pdo, $price, $date, $distance, $availability);

// Check if the form data was received via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Process filters
    $price = isset($_POST['price']) ? $_POST['price'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $distance = isset($_POST['distance']) ? $_POST['distance'] : '';
    $availability = isset($_POST['availability']) && $_POST['availability'] == 'yes' ? 'yes' : '';

    // Call the function to display filtered activities
    $data = displayActivity($pdo, $price, $date, $distance, $availability);

    // Output only the table HTML for AJAX response
    include('../html/includes/activity_table.php');  // Include a separate file with just the table rendering
    exit(); // End the script to avoid further output
}


if(isset($_SESSION['id'])){
    $userId = $_SESSION['id']; 
    $userData = getJoinerUserdata($pdo, $userId);
    $joinerName = $userData['firstName'];
} else {
    header('location: login_page.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        table {
            width: 100%;
        }
        table, tbody {
            border-collapse: collapse;
        }
        td {
            border-bottom: 1px solid black;
            padding: 15px;
            text-align: center;

        }
        th {
            font-size: 2.5vh;
        }
        #join-button {
            padding: 10px;
            border: none;border-radius: 10px;
            background-color: lightseagreen;
            color: white;
            transition: transform 0.2s;
        }
        #join-button:hover {
            transform: scale(1.05);
        }
        .sort-select {
            padding: 5px 10px;
            border-radius: 10px;
            border: 1px solid black;
        }
        #calendar-img{
            transition: transform 0.2s ease;
        }
        #calendar-img:hover{
            transform: scale(1.2);
            cursor: pointer;
        }
    </style>
</head>

<body style=" height: 100vh; background-color: white;">

    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li style=" border-bottom: 2px solid green;"><a href="" >Home</a></li>
                    <li><a href="joiner_activityPage.php" >Activity</a></li>
                    <li><a href="joiner_forumPage.php" >Forum</a></li>
                    <li><a href="joiner_marketplace.php" >Marketplace</a></li>
                    <li><a href="joiner_notification.php" >Notification</a></li>
                </ul>
            </div>
            <div class="nav_right" id="nav_right_click" onclick="window.location.href='joiner_account.php';">  
                <img src="../imgs/defaultuser.png" style="height: 30px; width: 30px;"> 
                <span style="display:flex; align-items:center;"><?php echo htmlspecialchars($joinerName); ?></span>
            </div>          
    </nav> 

    <div class="container" style=" padding-top: 7vh; display: grid; place-items: center;height: 100%; ">
        <div style=" padding: 40px; height: 100%; background-color: lightslategray ; width: 100%; background: linear-gradient(to right, #89CFF0, #A7C7E7);">
            <div style="width: 100%; display: grid; place-content: center;">
                <h1 style=" font-size: 2.8em; margin-bottom: 10px; color: azure     ;">Select and join the <br> adventure now!</h1>
                <div>
                    <form action="" id="filter-form" method= "POST" style=" display: flex; align-items: center; justify-content: space-between;">
                        <div style="padding: 2vw; display: flex; align-items: center; justify-content: space-between; color: whitesmoke; gap: 2vw;">
                            <span>Sort by:</span>
                            <div>   
                                <label for="price-select">Price:</label>
                                <select name="price" id="price-select" class="sort-select">
                                    <option value="">-- Select Price --</option>
                                    <option value="low-high">Low to High</option>
                                    <option value="high-low">High to Low</option>
                                </select>
                            </div>  
                            <div>
                                <label for="date-select">Date:</label>
                                <select name="date" id="date-select" class="sort-select">
                                    <option value="">-- Select Date --</option>
                                    <option value="soon">Soon</option>
                                    <option value="later">Later</option>
                                </select>
                            </div> 
                            <div>
                                <label for="distance-select">Distance:</label>
                                <select name="distance" id="distance-select" class="sort-select">
                                    <option value="">-- Select Distance --</option>
                                    <option value="longest">Longest</option>
                                    <option value="shortest">Shortest</option>
                                </select>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5vw;">
                                <p>Availability:</p>
                                <input type="checkbox" name="availability" value="yes" style="height: 20px; width: 20px;">
                            </div>
                        </div>
                        <div style=" padding: 0vw 2vw;">
                            <a href="calendarModule.php">
                                <img id="calendar-img" src="../imgs/icon_calendar.png" alt="Calendar" style="height: 50px; width: 50px;">
                            </a>
                        </div>               
                    </form>                        
                </div>
                <div id="results" style=" min-width: 1400px; height: 600px; padding: 20px; border: 2px solid black; border-radius: 20px; background-color:white;">
                <table>
                    <thead>
                    <tr>
                        <th>Activity Name</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Distance</th>
                        <th>Difficulty</th>
                        <th>Participants</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if ($data['success']):?>
                        <?php foreach( $data['data'] as $row):?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['activity_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php $date = new DateTime($row['date']); echo $date->format('F j, Y');?></td>
                                <td><?php echo htmlspecialchars($row['distance']); ?></td>
                                <td><?php echo htmlspecialchars($row['difficulty']); ?></td>
                                <td><?php echo htmlspecialchars($row['current_participants']);?>/<?php echo htmlspecialchars($row['participants']);?></td>
                                <td>
                                <a href="activityDetails.php?id=<?php echo htmlspecialchars($row['id']); ?>">
                                <button id="join-button">JOIN</button>
                                </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                        <td colspan="7"><?php echo htmlspecialchars($data['failed_message']); ?></td>
                        </tr> 
                    <?php endif; ?> 
                    </tbody>
                </table>

                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function () {
    $('#filter-form select, #filter-form input[type="checkbox"]').on('change', function (event) {
        event.preventDefault();

        const changedInput = $(this).attr('name');

        // Reset all filters except the one that was just changed
        $('#filter-form select, #filter-form input[type="checkbox"]').each(function () {
            if ($(this).attr('name') !== changedInput) {
                if ($(this).is('select')) {
                    $(this).val(''); // Reset dropdown
                } else if ($(this).is(':checkbox')) {
                    $(this).prop('checked', false); // Uncheck checkbox
                }
            }
        });

        // Send updated form via AJAX
        $.ajax({
            url: '', // Same page
            method: 'POST',
            data: $('#filter-form').serialize(),
            success: function (response) {
                $('#results').html(response); // Replace table
            },
            error: function () {
                alert("There was an error processing your request.");
            }
        });
    });
});
    </script>
</body>
</html>