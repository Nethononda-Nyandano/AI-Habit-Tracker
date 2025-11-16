<?php
require_once '../config/database.php';
class Functions
{
    public static function sanitizeInput($data): string
    {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    public function isLoggedIn(): bool
    {
        if (isset($_SESSION['user'])) {
            return true;
        }

        header('Location:../index.php');
        return false;
    }

    public function logout(): void
    {
        session_start();
        session_unset();
        session_destroy();
        header('Location:index.php');
    }

    public function info(): array
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        } else {
            return [];
        }

    }

    function getDiaryEntries(): int
    {
        $user = $this->info();
        if (empty($user)) {
            return 0;
        }
        $userId = $user['user_id'];

        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT COUNT(*) as count FROM diary_entries WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['count'];
    }

    function getHabitCount():int{
     $user = $this->info();
        if (empty($user)) {
            return 0;
        }
        $userId = $user['user_id'];

        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT COUNT(*) as count FROM habits WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['count'];
}

function getRecentHabit($userId): array
{
    $db = new Database();
    $conn = $db->getConnection();
    $sql = "SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC LIMIT 2";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



}




?>