<?php
header('Content-Type: application/json');

// Hide warnings to keep JSON valid
error_reporting(0);


$data = json_decode(file_get_contents("php://input"), true);
$text = $data['text'] ?? '';
$mood = $data['mood'] ?? null; // optional for song suggestion
$type = $data['type'] ?? 'sentiment'; // 'sentiment' or 'song'

if(!$text) {
    echo json_encode(['score'=>0]);
    exit;
}

$messages = [];

if($type === 'sentiment'){
    $messages = [
        ["role"=>"system","content"=>"You are a sentiment analyzer. Return a number from -1 (very negative) to 1 (very positive) only."],
        ["role"=>"user","content"=>"Analyze this text: \"$text\""]
    ];
} else if($type === 'song'){
    $messages = [
        ["role"=>"system","content"=>"You are a song recommender. Suggest one song title and artist based on user mood and sentiment."],
        ["role"=>"user","content"=>"User mood: $mood, entry text: $text. Reply in JSON format {\"title\": \"song title\",\"artist\":\"artist name\"}"]
    ];
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $OPENAI_KEY"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model"=>"gpt-4-mini",
    "messages"=>$messages
]));

$response = curl_exec($ch);
curl_close($ch);

$resData = json_decode($response,true);

if($type === 'sentiment'){
    $score = $resData['choices'][0]['message']['content'] ?? 0;
    echo json_encode(['score'=>floatval($score)]);
} else {
    $song = $resData['choices'][0]['message']['content'] ?? '{"title":"Unknown","artist":"Unknown"}';
    echo $song;
}
?>
?>