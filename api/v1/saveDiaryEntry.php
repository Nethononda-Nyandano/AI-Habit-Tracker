<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(['success'=>false, 'message'=>'No data received']);
    exit;
}

$user_id = filter_var($data['user_id'] ?? 0, FILTER_VALIDATE_INT);
$date = htmlspecialchars($data['date'] ?? '', ENT_QUOTES, 'UTF-8');
$entry_text = htmlspecialchars($data['entry_text'] ?? '', ENT_QUOTES, 'UTF-8');
$mood_level = filter_var($data['mood_level'] ?? '', FILTER_SANITIZE_NUMBER_INT);
$song_mood = htmlspecialchars($data['song_mood'] ?? '', ENT_QUOTES, 'UTF-8');
$sentiment_score = filter_var($data['sentiment_score'] ?? 0, FILTER_VALIDATE_FLOAT);
$suggested_song_title = htmlspecialchars($data['suggested_song_title'] ?? '', ENT_QUOTES, 'UTF-8');
$suggested_song_artist = htmlspecialchars($data['suggested_song_artist'] ?? '', ENT_QUOTES, 'UTF-8');
$ai_reflection = htmlspecialchars($data['ai_reflection'] ?? '', ENT_QUOTES, 'UTF-8');

if (!$user_id || !$date) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID and date are required']);
    exit;
}

if (strlen($entry_text) > 10000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Entry text is too long']);
    exit;
}

if ($sentiment_score !== '' && ($sentiment_score < -1 || $sentiment_score > 1)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sentiment score must be between -1 and 1']);
    exit;
}

if ($mood_level !== '' && (!is_numeric($mood_level) || $mood_level < 1 || $mood_level > 10)) {
    echo json_encode(['success' => false, 'message' => 'Mood level must be between 1 and 10']);
    exit;
}

try {
    // Check if entry exists
    $stmt = $pdo->prepare("SELECT id FROM diary_entries WHERE user_id=:user_id AND entry_date=:entry_date");
    $stmt->execute(['user_id'=>$user_id, 'entry_date'=>$date]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update
        $stmt = $pdo->prepare("UPDATE diary_entries SET 
            entry_text=:entry_text, mood_level=:mood_level, song_mood=:song_mood, 
            sentiment_score=:sentiment_score, suggested_song_title=:suggested_song_title,
            suggested_song_artist=:suggested_song_artist, ai_reflection=:ai_reflection,
            updated_at=NOW()
            WHERE id=:id
        ");
        $stmt->execute([
            'entry_text'=>$entry_text,
            'mood_level'=>$mood_level,
            'song_mood'=>$song_mood,
            'sentiment_score'=>$sentiment_score,
            'suggested_song_title'=>$suggested_song_title,
            'suggested_song_artist'=>$suggested_song_artist,
            'ai_reflection'=>$ai_reflection,
            'id'=>$existing['id']
        ]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO diary_entries 
            (user_id, entry_date, entry_text, mood_level, song_mood, sentiment_score, suggested_song_title, suggested_song_artist, ai_reflection, created_at, updated_at)
            VALUES (:user_id, :entry_date, :entry_text, :mood_level, :song_mood, :sentiment_score, :suggested_song_title, :suggested_song_artist, :ai_reflection, NOW(), NOW())
        ");
        $stmt->execute([
            'user_id'=>$user_id,
            'entry_date'=>$date,
            'entry_text'=>$entry_text,
            'mood_level'=>$mood_level,
            'song_mood'=>$song_mood,
            'sentiment_score'=>$sentiment_score,
            'suggested_song_title'=>$suggested_song_title,
            'suggested_song_artist'=>$suggested_song_artist,
            'ai_reflection'=>$ai_reflection
        ]);
    }

    echo json_encode(['success'=>true, 'message'=>'Entry saved successfully']);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'error' => 'An error occurred while saving the entry']);
}
