<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

header("Content-Type: application/json");
require_once __DIR__ . '/../../vendor/autoload.php'; 

$input = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
$habitName = $input["habitName"];
$frequency = $input["frequency"];
$description = $input["description"];

$prompt = "Give a short motivational AI suggestion to help someone improve their habit of '$habitName'. 
Habit frequency: $frequency. Description: $description.";



$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer apikey"
]);
try {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "You are a helpful habit improvement coach."],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 120
    ], JSON_THROW_ON_ERROR));
} catch (JsonException $e) {
    echo $e->getMessage();
}
$response = curl_exec($ch);
curl_close($ch);

try {
    $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    echo json_encode(["suggestion" => $data["choices"][0]["message"]["content"] ?? "No suggestion available"], JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo $e->getMessage();
}
?>
