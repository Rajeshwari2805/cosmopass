<?php
header('Access-Control-Allow-Origin: *');
require_once($_SERVER['DOCUMENT_ROOT'] . '/cosmopass/config.php');

try {
    $pdo  = getDB();
    $stmt = $pdo->query("SELECT * FROM events ORDER BY id ASC");
    $events = $stmt->fetchAll();
    jsonOut(['success' => true, 'events' => $events]);
} catch (Exception $e) {
    jsonOut(['success' => false, 'error' => $e->getMessage()], 500);
}