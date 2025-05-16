<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=capstone', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['id'];

    // Modified SQL query
    $sql = "
        SELECT 
        a.id AS activity_id,
        a.activity_name AS title,
        a.date AS start,
        a.status
        FROM 
            participants p
        LEFT JOIN 
            activities a ON a.id = p.activity_id
        WHERE 
            p.participant_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    $rawEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($rawEvents as $event) {
        $color = 'skyblue';
        $clickable = true;

        if ($event['status'] !== null) {
            if ($event['status'] === 'done') {
                $color = 'green';
                $clickable = false;
            } elseif ($event['status'] === 'pending') {
                $color = 'skyblue';
            }
        }

        $events[] = [
            'id' => $event['activity_id'], // Use the activity_id from the query
            'title' => $event['title'],
            'start' => $event['start'],
            'color' => $color,
            'clickable' => $clickable
        ];
    }

    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
