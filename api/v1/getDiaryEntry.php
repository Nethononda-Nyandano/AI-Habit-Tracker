<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id = intval($_GET['user_id'] ?? 0);
$month = intval($_GET['month'] ?? date('n'));
$year = intval($_GET['year'] ?? date('Y'));

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM diary_entries WHERE user_id=:user_id AND MONTH(entry_date)=:month AND YEAR(entry_date)=:year");
    $stmt->execute(['user_id'=>$user_id, 'month'=>$month, 'year'=>$year]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach($entries as $entry){
        $result[$entry['entry_date']] = [
            'entry_text'=>$entry['entry_text'],
            'mood_level'=>$entry['mood_level'],
            'song_mood'=>$entry['song_mood'],
            'sentiment_score'=>floatval($entry['sentiment_score']),
            'suggested_song_title'=>$entry['suggested_song_title'],
            'suggested_song_artist'=>$entry['suggested_song_artist'],
            'ai_reflection'=>$entry['ai_reflection']
        ];
    }

    echo json_encode($result);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
}
