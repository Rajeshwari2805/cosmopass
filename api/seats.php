<?php
header('Access-Control-Allow-Origin: *');
require_once($_SERVER['DOCUMENT_ROOT'] . '/cosmopass/config.php');

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($event_id <= 0) {
    jsonOut(['success' => false, 'error' => 'Invalid event_id'], 400);
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT seat_code, zone, status FROM seats WHERE event_id = ? ORDER BY seat_code ASC");
    $stmt->execute([$event_id]);
    $seats = $stmt->fetchAll();

    jsonOut(['success' => true, 'event_id' => $event_id, 'seats' => $seats]);
} catch (Exception $e) {
    jsonOut(['success' => false, 'error' => $e->getMessage()], 500);
}