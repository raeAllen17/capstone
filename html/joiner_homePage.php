<?php
require_once 'includes/dbCon.php';
require_once 'includes/activity_store.php';
$data = displayActivity($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 

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
        #join-button {
            padding: 10px;
            border: none;border-radius: 10px;
            background-color: lightseagreen;
            color: white;
        }
    </style>
</head>

<body style=" height: 100vh; background-color: lightgrey;">

    <nav id="nav">
            <div class="nav_left">
                <ul class = "navbar">
                    <li><input type="button" class="logo"></li>
                    <li style=" border-bottom: 2px solid green;"><a href="" >Home</a></li>
                    <li><a href="" >Activity</a></li>
                    <li><a href="" >Forum</a></li>
                    <li><a href="" >Marketplace</a></li>
                    <li><a href="" >Notification</a></li>
                </ul>
            </div>
            <div class="nav_right">   
                
            </div>          
        
    </nav> 

    <div class="container" style=" padding-top: 7vh; display: grid; place-items: center;">
        <div style=" padding: 40px;">
            <div>
                <h1 style=" font-size: 2.8em; margin-bottom: 10px;">Select and join the <br> adventure now!</h1>
                <div style=" min-width: 1600px; height: 600px; padding: 20px; border: 2px solid black; border-radius: 20px; background-color:white;">
                    <!-- php data here-->
                <table>
                    <thead>
                    <tr>
                        <th>Activity Name</th>
                        <th>Description</th>
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
                                <td style="font-weight: bold;"><?php echo htmlspecialchars($row['activity_name']); ?></tdstlye>
                                <td style="max-width: 600px;"><?php echo htmlspecialchars($row['description']); ?></td>
                                <td style="font-weight: bold;"><?php echo htmlspecialchars($row['location']); ?></td>
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
</body>
</html>