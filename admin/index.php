<?php
class Users
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
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }

    public function getUserById($userId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?? [];
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
        // First delete related records to maintain referential integrity
        $tables = ['diary_entries', 'habits'];
        foreach ($tables as $table) {
            $stmt = $this->conn->prepare("DELETE FROM $table WHERE user_id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }

        // Then delete the user
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function addUser($surname, $name, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (surname, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $surname, $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function searchUsers($searchTerm): array
    {
        $searchTerm = "%$searchTerm%";
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE surname LIKE ? OR name LIKE ? OR email LIKE ? ORDER BY created_at DESC");
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'users.php';
    $user = new Users();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $success = $user->addUser($_POST['surname'], $_POST['name'], $_POST['email'], $_POST['password']);
                if ($success) {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?section=users&message=User added successfully');
                } else {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?section=users&error=Failed to add user');
                }
                exit;
                
            case 'update_user':
                $success = $user->updateUser($_POST['user_id'], $_POST['surname'], $_POST['name'], $_POST['email']);
                if ($success) {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?section=users&message=User updated successfully');
                } else {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?section=users&error=Failed to update user');
                }
                exit;
                
            case 'delete_user':
                $success = $user->deleteUser($_POST['user_id']);
                if ($success) {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?section=users&message=User deleted successfully');
                } else {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?section=users&error=Failed to delete user');
                }
                exit;
        }
    }
}

require_once 'users.php';
$user = new Users();
$usersList = $user->getAllUsers();
$habitCount = $user->getHabitUsersCount();
$userCount = $user->getTotalUsers();
$DiaryCount = $user->getEntry();

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $usersList = $user->searchUsers($_GET['search']);
}

// Real analytics data
$monthlyUsers = $user->getMonthlyUserGrowth();
$habitDistribution = $user->getHabitDistribution();
$userActivity = $user->getUserActivity();
$moodDistribution = $user->getMoodDistribution();
$completionRates = $user->getHabitCompletionRates();
$sentimentTrends = $user->getSentimentTrends();
$topSongs = $user->getTopSuggestedSongs();
$recentActivities = $user->getRecentActivities();

// Get user for editing
$editUser = [];
if (isset($_GET['edit_id'])) {
    $editUser = $user->getUserById($_GET['edit_id']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lexend Deca', sans-serif;
        }
        .active-nav {
            background-color: #000 !important;
            color: white !important;
        }
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
        .chart-colors-1 { background-color: #e63333ff; }
        .chart-colors-2 { background-color: #404040; }
        .chart-colors-3 { background-color: #808080; }
        .chart-colors-4 { background-color: #bfbfbf; }
        .chart-colors-5 { background-color: #e6e6e6; }
        .chart-colors-6 { background-color: #4a4a4a; }
        .chart-colors-7 { background-color: #8c8c8c; }
        .chart-colors-8 { background-color: #d9d9d9; }
    </style>
    <title>Admin Panel</title>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <div class="h-8 w-8 bg-black rounded-full flex items-center justify-center mr-2">
                    <span class="text-white font-bold text-sm">A</span>
                </div>
                <h1 class="text-lg font-bold text-gray-800">Admin Panel</h1>
            </div>

            <div class="flex items-center">
                <div class="h-6 w-6 bg-gray-800 rounded-full flex items-center justify-center mr-2">
                    <span class="text-white text-xs">T</span>
                </div>
                <span class="text-gray-700 text-sm">Temoso</span>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="flex flex-1 mt-1 h-full">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md">
            <nav class="mt-10 flex flex-col px-6">
                <a href="#" data-section="home" class="nav-link block p-3 rounded-md transition duration-200 hover:bg-gray-200 text-gray-700 mb-1">
                    Dashboard
                </a>
                <a href="#" data-section="users" class="nav-link block p-3 rounded-md transition duration-200 hover:bg-gray-200 text-gray-700 mb-1">
                    Users
                </a>
                <a href="#" data-section="analytics" class="nav-link block p-3 rounded-md transition duration-200 hover:bg-gray-200 text-gray-700 mb-1">
                    Analytics
                </a>
                <a href="#" data-section="settings" class="nav-link block p-3 rounded-md transition duration-200 hover:bg-gray-200 text-gray-700 mb-1">
                    Settings
                </a>
                <a href="#" data-section="reports" class="nav-link block p-3 rounded-md transition duration-200 hover:bg-gray-200 text-gray-700 mb-1">
                    Reports
                </a>

                <a href="../logout.php" class="mt-10 bg-black text-white m-2 p-3 rounded-md hover:bg-gray-800 transition duration-200 text-center">
                    Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 bg-white rounded-sm shadow-md m-4">
            <!-- Messages -->
            <?php if (isset($_GET['message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <section id="home-content" class="content-section active">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Dashboard Overview</h2>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <div class="flex items-center">
                            <div class="h-12 w-12 bg-black rounded-full flex items-center justify-center mr-4">
                                <span class="text-white font-bold">👥</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Users</h3>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($userCount); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <div class="flex items-center">
                            <div class="h-12 w-12 bg-black rounded-full flex items-center justify-center mr-4">
                                <span class="text-white font-bold">📊</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Active Habits</h3>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($habitCount); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <div class="flex items-center">
                            <div class="h-12 w-12 bg-black rounded-full flex items-center justify-center mr-4">
                                <span class="text-white font-bold">📝</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Diary Entries</h3>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($DiaryCount); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <div class="flex items-center">
                            <div class="h-12 w-12 bg-black rounded-full flex items-center justify-center mr-4">
                                <span class="text-white font-bold">📈</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Avg. Sentiment</h3>
                                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo number_format($user->getAverageSentiment(), 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Analytics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Mood Distribution -->
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">Mood Distribution</h3>
                        <div class="h-64">
                            <canvas id="moodChart"></canvas>
                        </div>
                    </div>

                    <!-- Habit Completion -->
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">Habit Completion Rate</h3>
                        <div class="h-64">
                            <canvas id="completionChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Recent Activity</h3>
                    <div class="space-y-4">
                        <?php foreach ($recentActivities as $activity): ?>
                        <div class="flex items-center border-b border-gray-100 pb-3">
                            <div class="h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-gray-700"><?php echo $activity['icon']; ?></span>
                            </div>
                            <div>
                                <p class="text-gray-800"><?php echo htmlspecialchars($activity['description']); ?></p>
                                <p class="text-gray-500 text-sm"><?php echo $activity['time_ago']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Users Section -->
            <section id="users-content" class="content-section">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
                    <button onclick="openAddModal()" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition duration-200">
                        Add User
                    </button>
                </div>

                <!-- Search and Filter -->
                <div class="mb-6 bg-white p-4 rounded-lg shadow-md border border-gray-200">
                    <form method="GET" class="flex gap-4">
                        <input type="hidden" name="section" value="users">
                        <input type="text" name="search" placeholder="Search users..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                               class="flex-1 border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        <button type="submit" class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 transition duration-200">
                            Search
                        </button>
                        <?php if (isset($_GET['search'])): ?>
                            <a href="?section=users" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition duration-200">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-3 text-left">ID</th>
                                <th class="border border-gray-300 px-4 py-3 text-left">Date</th>
                                <th class="border border-gray-300 px-4 py-3 text-left">Surname</th>
                                <th class="border border-gray-300 px-4 py-3 text-left">Name</th>
                                <th class="border border-gray-300 px-4 py-3 text-left">Email</th>
                                <th class="border border-gray-300 px-4 py-3 text-left">Habits</th>
                                <th class="border border-gray-300 px-4 py-3 text-left">Entries</th>
                                <th class="border border-gray-300 px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usersList as $userItem): 
                                $userStats = $user->getUserStats($userItem['user_id']);
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-3"><?php echo htmlspecialchars($userItem['user_id']); ?></td>
                                    <td class="border border-gray-300 px-4 py-3"><?php echo substr($userItem['created_at'], 0, 10); ?></td>
                                    <td class="border border-gray-300 px-4 py-3"><?php echo htmlspecialchars($userItem['surname']); ?></td>
                                    <td class="border border-gray-300 px-4 py-3"><?php echo htmlspecialchars($userItem['name']); ?></td>
                                    <td class="border border-gray-300 px-4 py-3"><?php echo htmlspecialchars($userItem['email']); ?></td>
                                    <td class="border border-gray-300 px-4 py-3"><?php echo $userStats['habit_count']; ?></td>
                                    <td class="border border-gray-300 px-4 py-3"><?php echo $userStats['entry_count']; ?></td>
                                    <td class="border border-gray-300 px-4 py-3">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars($userItem['user_id']); ?>)" class="edit-btn mr-2">
                                            <svg class="h-5 w-5 text-gray-600 hover:text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button onclick="openDeleteModal(<?php echo htmlspecialchars($userItem['user_id']); ?>)" class="delete-btn">
                                            <svg class="h-5 w-5 text-gray-600 hover:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Analytics Section -->
            <section id="analytics-content" class="content-section">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Analytics Dashboard</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- User Growth Chart -->
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">User Growth</h3>
                        <div class="h-64">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Habit Distribution Chart -->
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">Habit Distribution</h3>
                        <div class="h-64">
                            <canvas id="habitDistributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- User Activity Chart -->
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">User Activity</h3>
                        <div class="h-64">
                            <canvas id="userActivityChart"></canvas>
                        </div>
                    </div>

                    <!-- Sentiment Trends -->
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <h3 class="text-xl font-bold mb-4 text-gray-800">Sentiment Trends</h3>
                        <div class="h-64">
                            <canvas id="sentimentChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Songs -->
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Most Suggested Songs</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-3 text-left">Song Title</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left">Artist</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left">Times Suggested</th>
                                    <th class="border border-gray-300 px-4 py-3 text-left">Associated Mood</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topSongs as $song): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-3"><?php echo htmlspecialchars($song['song_title']); ?></td>
                                        <td class="border border-gray-300 px-4 py-3"><?php echo htmlspecialchars($song['song_artist']); ?></td>
                                        <td class="border border-gray-300 px-4 py-3"><?php echo $song['suggestion_count']; ?></td>
                                        <td class="border border-gray-300 px-4 py-3"><?php echo htmlspecialchars($song['mood_level']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Settings Section -->
            <section id="settings-content" class="content-section">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Settings</h2>
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <p class="text-gray-600 mb-6">Configure your application settings here.</p>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">General Settings</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-gray-700">Enable Notifications</label>
                                    <div class="relative inline-block w-12 h-6">
                                        <input type="checkbox" class="sr-only toggle-switch" id="notifications-toggle">
                                        <div class="block bg-gray-300 w-12 h-6 rounded-full toggle-bg"></div>
                                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition toggle-dot"></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <label class="text-gray-700">Dark Mode</label>
                                    <div class="relative inline-block w-12 h-6">
                                        <input type="checkbox" class="sr-only toggle-switch" id="darkmode-toggle">
                                        <div class="block bg-gray-300 w-12 h-6 rounded-full toggle-bg"></div>
                                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition toggle-dot"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Reports Section -->
            <section id="reports-content" class="content-section">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Reports</h2>
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <p class="text-gray-600 mb-6">View and generate various reports here.</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-md">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">User Activity Report</h3>
                                <p class="text-gray-600">Detailed analysis of user activities and habits</p>
                            </div>
                            <button class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition duration-200">
                                Generate
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-md">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Habit Completion Report</h3>
                                <p class="text-gray-600">Statistics on habit completion rates</p>
                            </div>
                            <button class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition duration-200">
                                Generate
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Add New User</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_user">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
                        <input type="text" name="surname" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded bg-black text-white hover:bg-gray-800">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Edit User</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
                        <input type="text" name="surname" id="edit_surname" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="edit_name" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded bg-black text-white hover:bg-gray-800">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
            <p class="mb-4">Are you sure you want to delete this user? This action cannot be undone.</p>
            <form method="POST" action="" id="deleteForm">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" id="delete_user_id">
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelDelete" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation logic
            const navLinks = document.querySelectorAll('.nav-link');
            const contentSections = document.querySelectorAll('.content-section');

            // Function to show section and update active nav
            function showSection(sectionId) {
                // Hide all sections
                contentSections.forEach(section => {
                    section.classList.remove('active');
                });

                // Remove active class from all nav links
                navLinks.forEach(link => {
                    link.classList.remove('active-nav');
                });

                // Show target section
                const targetSection = document.getElementById(sectionId + '-content');
                if (targetSection) {
                    targetSection.classList.add('active');
                }

                // Add active class to clicked nav link
                const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
                if (activeLink) {
                    activeLink.classList.add('active-nav');
                }

                // Store active section
                localStorage.setItem('activeSection', sectionId);

                // Initialize charts when analytics section is shown
                if (sectionId === 'analytics' || sectionId === 'home') {
                    setTimeout(initializeCharts, 100);
                }
            }

            // Add click event to nav links
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    showSection(section);
                });
            });

            // Restore active section from localStorage or default to home
            const activeSection = localStorage.getItem('activeSection') || 'home';
            showSection(activeSection);

            // Modal functions
            window.openAddModal = function() {
                document.getElementById('addModal').classList.remove('hidden');
            }

            window.closeAddModal = function() {
                document.getElementById('addModal').classList.add('hidden');
            }

            window.openEditModal = function(userId) {
                // In a real application, you would fetch user data via AJAX
                // For now, we'll redirect to the same page with edit parameter
                window.location.href = `?section=users&edit_id=${userId}`;
            }

            window.closeEditModal = function() {
                window.location.href = '?section=users';
            }

            window.openDeleteModal = function(userId) {
                document.getElementById('delete_user_id').value = userId;
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            // If we have edit parameters, open the edit modal
            <?php if (isset($_GET['edit_id']) && !empty($editUser)): ?>
                document.getElementById('edit_user_id').value = '<?php echo $editUser['user_id']; ?>';
                document.getElementById('edit_surname').value = '<?php echo $editUser['surname']; ?>';
                document.getElementById('edit_name').value = '<?php echo $editUser['name']; ?>';
                document.getElementById('edit_email').value = '<?php echo $editUser['email']; ?>';
                document.getElementById('editModal').classList.remove('hidden');
            <?php endif; ?>

            // Close delete modal
            document.getElementById('cancelDelete').addEventListener('click', function() {
                document.getElementById('deleteModal').classList.add('hidden');
            });

            // Toggle switches
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const toggleContainer = this.parentElement;
                    const dot = toggleContainer.querySelector('.toggle-dot');
                    const bg = toggleContainer.querySelector('.toggle-bg');
                    
                    if (this.checked) {
                        dot.style.transform = 'translateX(24px)';
                        bg.classList.remove('bg-gray-300');
                        bg.classList.add('bg-black');
                    } else {
                        dot.style.transform = 'translateX(0)';
                        bg.classList.remove('bg-black');
                        bg.classList.add('bg-gray-300');
                    }
                });
            });

            function initializeCharts() {
              
                Chart.helpers.each(Chart.instances, function(instance) {
                    instance.destroy();
                });

                
                const chartColors = [
                    '#747a72ff', '#404040', '#808080', '#bfbfbf', 
                    '#e6e6e6', '#4a4a4a', '#8c8c8c', '#d9d9d9'
                ];

                // Mood Distribution Chart
                const moodCtx = document.getElementById('moodChart');
                if (moodCtx) {
                    new Chart(moodCtx, {
                        type: 'pie',
                        data: {
                            labels: <?php echo json_encode(array_keys($moodDistribution)); ?>,
                            datasets: [{
                                data: <?php echo json_encode(array_values($moodDistribution)); ?>,
                                backgroundColor: chartColors
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }

                // Completion Rate Chart
                const completionCtx = document.getElementById('completionChart');
                if (completionCtx) {
                    new Chart(completionCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode(array_keys($completionRates)); ?>,
                            datasets: [{
                                label: 'Completion Rate (%)',
                                data: <?php echo json_encode(array_values($completionRates)); ?>,
                                backgroundColor: '#000000'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            }
                        }
                    });
                }

                // User Growth Chart
                const userGrowthCtx = document.getElementById('userGrowthChart');
                if (userGrowthCtx) {
                    new Chart(userGrowthCtx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_keys($monthlyUsers)); ?>,
                            datasets: [{
                                label: 'User Growth',
                                data: <?php echo json_encode(array_values($monthlyUsers)); ?>,
                                borderColor: '#9594aaff',
                                backgroundColor: 'rgba(174, 112, 202, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Habit Distribution Chart
                const habitDistributionCtx = document.getElementById('habitDistributionChart');
                if (habitDistributionCtx) {
                    new Chart(habitDistributionCtx, {
                        type: 'doughnut',
                        data: {
                            labels: <?php echo json_encode(array_keys($habitDistribution)); ?>,
                            datasets: [{
                                data: <?php echo json_encode(array_values($habitDistribution)); ?>,
                                backgroundColor: chartColors
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }

                // User Activity Chart
                const userActivityCtx = document.getElementById('userActivityChart');
                if (userActivityCtx) {
                    new Chart(userActivityCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode(array_keys($userActivity)); ?>,
                            datasets: [{
                                label: 'User Activity',
                                data: <?php echo json_encode(array_values($userActivity)); ?>,
                                backgroundColor: '#7d3b91ff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Sentiment Trends Chart
                const sentimentCtx = document.getElementById('sentimentChart');
                if (sentimentCtx) {
                    new Chart(sentimentCtx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_keys($sentimentTrends)); ?>,
                            datasets: [{
                                label: 'Average Sentiment Score',
                                data: <?php echo json_encode(array_values($sentimentTrends)); ?>,
                                borderColor: '#a7ce3bff',
                                backgroundColor: 'rgba(151, 219, 25, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    min: -1,
                                    max: 1
                                }
                            }
                        }
                    });
                }
            }

            // Initialize charts on page load
            initializeCharts();
        });
    </script>
</body>
</html>