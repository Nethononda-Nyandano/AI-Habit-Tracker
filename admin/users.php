<?php
class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli('localhost', 'root', 'Nyandano@13', 'habitdb');
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM users";
        $result = $this->conn->query($sql);
        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }

    public function getUserStats($userId): array
    {
        // Get habit count
        $habitSql = "SELECT COUNT(*) as habit_count FROM habits WHERE user_id = ?";
        $stmt = $this->conn->prepare($habitSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $habitResult = $stmt->get_result();
        $habitCount = $habitResult->fetch_assoc()['habit_count'];

        // Get diary entry count
        $entrySql = "SELECT COUNT(*) as entry_count FROM diary_entries WHERE user_id = ?";
        $stmt = $this->conn->prepare($entrySql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $entryResult = $stmt->get_result();
        $entryCount = $entryResult->fetch_assoc()['entry_count'];

        return [
            'habit_count' => $habitCount,
            'entry_count' => $entryCount
        ];
    }

    public function updateUser($id, $surname, $name, $email)
    {
        $stmt = $this->conn->prepare("UPDATE users SET surname=?, name=?, email=? WHERE user_id=?");
        $stmt->bind_param("sssi", $surname, $name, $email, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getTotalUsers(): int
    {
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    public function getHabitUsersCount(): int
    {
        $sql = "SELECT COUNT(DISTINCT user_id) as habit_users FROM habits";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['habit_users'];
    }

    public function getEntry(): int
    {
        $sql = "SELECT COUNT(*) as total_entries FROM diary_entries";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['total_entries'];
    }

    public function getAverageSentiment(): float
    {
        $sql = "SELECT AVG(sentiment_score) as avg_sentiment FROM diary_entries WHERE sentiment_score IS NOT NULL";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (float)$row['avg_sentiment'] ?? 0.0;
    }

    public function getMonthlyUserGrowth(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as user_count
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month";

        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['month']] = (int)$row['user_count'];
        }
        return $data;
    }

    public function getHabitDistribution(): array
    {
        $sql = "SELECT 
                    habit_name,
                    COUNT(*) as count
                FROM habits 
                GROUP BY habit_name 
                ORDER BY count DESC 
                LIMIT 10";

        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['habit_name']] = (int)$row['count'];
        }
        return $data;
    }

    public function getUserActivity(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as activity_count
                FROM (
                    SELECT created_at FROM diary_entries
                    UNION ALL
                    SELECT created_at FROM habits
                ) as activities
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month";

        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['month']] = (int)$row['activity_count'];
        }
        return $data;
    }

    public function getMoodDistribution(): array
    {
        $sql = "SELECT 
                    mood_level,
                    COUNT(*) as count
                FROM diary_entries 
                WHERE mood_level IS NOT NULL
                GROUP BY mood_level 
                ORDER BY count DESC";

        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['mood_level']] = (int)$row['count'];
        }
        return $data;
    }

    public function getHabitCompletionRates(): array
    {
        $sql = "SELECT 
                    habit_name,
                    ROUND((SUM(done_counts) / (SUM(done_counts) + SUM(skipped))) * 100, 2) as completion_rate
                FROM habits 
                WHERE (done_counts + skipped) > 0
                GROUP BY habit_name 
                ORDER BY completion_rate DESC 
                LIMIT 8";

        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['habit_name']] = (float)$row['completion_rate'];
        }
        return $data;
    }

    public function getSentimentTrends(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(entry_date, '%Y-%m') as month,
                    AVG(sentiment_score) as avg_sentiment
                FROM diary_entries 
                WHERE sentiment_score IS NOT NULL 
                    AND entry_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(entry_date, '%Y-%m')
                ORDER BY month";

        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['month']] = (float)$row['avg_sentiment'];
        }
        return $data;
    }

    public function getTopSuggestedSongs(): array
    {
        $sql = "SELECT 
                    suggested_song_title as song_title,
                    suggested_song_artist as song_artist,
                    mood_level,
                    COUNT(*) as suggestion_count
                FROM diary_entries 
                WHERE suggested_song_title IS NOT NULL 
                    AND suggested_song_title != ''
                GROUP BY suggested_song_title, suggested_song_artist, mood_level
                ORDER BY suggestion_count DESC 
                LIMIT 10";

        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'song_title' => $row['song_title'],
                'song_artist' => $row['song_artist'],
                'mood_level' => $row['mood_level'],
                'suggestion_count' => (int)$row['suggestion_count']
            ];
        }
        return $data;
    }

    public function getRecentActivities(): array
    {
        $sql = "(
                    SELECT 
                        'diary' as type,
                        '📝' as icon,
                        CONCAT('New diary entry by user ', user_id) as description,
                        created_at
                    FROM diary_entries 
                    ORDER BY created_at DESC 
                    LIMIT 3
                )
                UNION ALL
                (
                    SELECT 
                        'habit' as type,
                        '📊' as icon,
                        CONCAT('New habit created by user ', user_id) as description,
                        created_at
                    FROM habits 
                    ORDER BY created_at DESC 
                    LIMIT 2
                )
                ORDER BY created_at DESC 
                LIMIT 5";

        $result = $this->conn->query($sql);
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $timeAgo = $this->getTimeAgo($row['created_at']);
            $activities[] = [
                'icon' => $row['icon'],
                'description' => $row['description'],
                'time_ago' => $timeAgo
            ];
        }
        return $activities;
    }

    private function getTimeAgo($datetime)
    {
        $time = strtotime($datetime);
        $timeDiff = time() - $time;

        if ($timeDiff < 60) {
            return 'Just now';
        } elseif ($timeDiff < 3600) {
            return round($timeDiff / 60) . ' minutes ago';
        } elseif ($timeDiff < 86400) {
            return round($timeDiff / 3600) . ' hours ago';
        } else {
            return round($timeDiff / 86400) . ' days ago';
        }
    }

    public function getHabitCategories(): array
    {
        $sql = "SELECT DISTINCT habit_name FROM habits ORDER BY habit_name";
        $result = $this->conn->query($sql);
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['habit_name'];
        }
        return $categories;
    }
}
?>