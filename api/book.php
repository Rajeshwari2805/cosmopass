<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once($_SERVER['DOCUMENT_ROOT'] . '/cosmopass/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['success' => false, 'error' => 'POST required'], 405);
}

// ── Parse input ──
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    jsonOut(['success' => false, 'error' => 'Invalid JSON'], 400);
}

// ── Validate required fields ──
$required = ['event_id','first_name','last_name','email','seats','total_price'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        jsonOut(['success' => false, 'error' => "Missing field: $field"], 400);
    }
}

$event_id   = (int)$data['event_id'];
$first_name = trim(htmlspecialchars($data['first_name']));
$last_name  = trim(htmlspecialchars($data['last_name']));
$email      = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$phone      = trim(htmlspecialchars($data['phone']      ?? ''));
$country    = trim(htmlspecialchars($data['country']    ?? ''));
$special    = trim(htmlspecialchars($data['special_req'] ?? ''));
$seat_list  = $data['seats'];          // array of {id, vip}
$total      = (int)$data['total_price'];
$zone       = trim(htmlspecialchars($data['zone'] ?? 'Standard'));

if (!$email) {
    jsonOut(['success' => false, 'error' => 'Invalid email address'], 400);
}
if (empty($seat_list) || !is_array($seat_list)) {
    jsonOut(['success' => false, 'error' => 'No seats selected'], 400);
}
if (count($seat_list) > 6) {
    jsonOut(['success' => false, 'error' => 'Maximum 6 seats per booking'], 400);
}

$seat_codes = array_map(fn($s) => strtoupper(trim($s['id'] ?? '')), $seat_list);
$seat_codes = array_filter($seat_codes);
$seats_csv  = implode(',', $seat_codes);

// ── Generate unique PASS ID ──
$pass_id = 'PASS-' . strtoupper(bin2hex(random_bytes(4)));

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // Lock & verify all seats are still available
    $placeholders = implode(',', array_fill(0, count($seat_codes), '?'));
    $lockStmt = $pdo->prepare(
        "SELECT seat_code, status FROM seats
         WHERE event_id = ? AND seat_code IN ($placeholders)
         FOR UPDATE"
    );
    $lockStmt->execute(array_merge([$event_id], $seat_codes));
    $rows = $lockStmt->fetchAll();

    if (count($rows) !== count($seat_codes)) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'error' => 'One or more seats not found for this event'], 400);
    }

    $alreadyBooked = array_filter($rows, fn($r) => $r['status'] === 'booked');
    if (!empty($alreadyBooked)) {
        $pdo->rollBack();
        $taken = implode(', ', array_column(array_values($alreadyBooked), 'seat_code'));
        jsonOut(['success' => false, 'error' => "Seats already booked: $taken"], 409);
    }

    // Mark seats as booked
    $updateStmt = $pdo->prepare(
        "UPDATE seats SET status='booked'
         WHERE event_id = ? AND seat_code IN ($placeholders)"
    );
    $updateStmt->execute(array_merge([$event_id], $seat_codes));

    // Insert booking record
    $insertStmt = $pdo->prepare(
        "INSERT INTO bookings
         (pass_id, event_id, first_name, last_name, email, phone, country, special_req, seats, zone, total_price)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insertStmt->execute([
        $pass_id, $event_id, $first_name, $last_name,
        $email, $phone, $country, $special,
        $seats_csv, $zone, $total
    ]);

    $pdo->commit();

    jsonOut([
        'success'     => true,
        'pass_id'     => $pass_id,
        'seats'       => $seats_csv,
        'total_price' => $total,
        'message'     => 'Booking confirmed!'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonOut(['success' => false, 'error' => $e->getMessage()], 500);
}