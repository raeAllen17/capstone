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

    $orgId = $_SESSION['id'];

    $sql = "SELECT id, activity_name AS title, date AS start, status 
            FROM activities 
            WHERE org_id = :org_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['org_id' => $orgId]);
    $rawEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($rawEvents as $event) {
        $events[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $event['start'],
            'color' => $event['status'] === 'done' ? 'green' : null,  // Gray for "done"
            'clickable' => $event['status'] !== 'done'
        ];
    }

    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
