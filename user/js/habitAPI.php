<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Short-circuit OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

class HabitAPI
{
    private $db;
    private $validFrequencies = ['daily', 'weekly', 'monthly'];

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /* ---------- GET HABITS ---------- */
    public function getHabits($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------- ADD HABIT ---------- */
    public function addHabit($userId, $name, $description, $frequency)
    {
        if (!in_array($frequency, $this->validFrequencies, true)) {
            throw new InvalidArgumentException("Invalid frequency value");
        }

        $stmt = $this->db->prepare(
            "INSERT INTO habits (user_id, habit_name, description, frequency, created_at, done_counts, skipped)
             VALUES (?, ?, ?, ?, NOW(), 0, 0)"
        );
        $success = $stmt->execute([$userId, $name, $description, $frequency]);
        if ($success) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /* ---------- UPDATE HABIT ---------- */
    public function updateHabit($habitId, $name, $description, $frequency)
    {
        if (!in_array($frequency, $this->validFrequencies, true)) {
            throw new InvalidArgumentException("Invalid frequency value");
        }

        $stmt = $this->db->prepare(
            "UPDATE habits SET habit_name = ?, description = ?, frequency = ? WHERE habit_id = ?"
        );
        return $stmt->execute([$name, $description, $frequency, $habitId]);
    }

    /* ---------- DELETE HABIT ---------- */
    public function deleteHabit($habitId)
    {
        // Delete logs first to maintain integrity
        $stmt = $this->db->prepare("DELETE FROM habit_logs WHERE habit_id = ?");
        $stmt->execute([$habitId]);

        // Delete habit
        $stmt = $this->db->prepare("DELETE FROM habits WHERE habit_id = ?");
        return $stmt->execute([$habitId]);
    }

    /* ---------- PATCH: MARK HABIT DONE ---------- */
    public function markHabitDone($habitId, $status = 'done', $notes = '')
    {
        $validStatuses = ['done', 'missed', 'skipped'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid status value");
        }

        $date = date('Y-m-d');

        // Check if log already exists for today
        $stmt = $this->db->prepare("SELECT * FROM habit_logs WHERE habit_id = ? AND log_date = ?");
        $stmt->execute([$habitId, $date]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing log
            $stmt = $this->db->prepare("UPDATE habit_logs SET status = ?, notes = ? WHERE log_id = ?");
            $stmt->execute([$status, $notes, $existing['log_id']]);
        } else {
            // Insert new log
            $stmt = $this->db->prepare("INSERT INTO habit_logs (habit_id, log_date, status, notes) VALUES (?, ?, ?, ?)");
            $stmt->execute([$habitId, $date, $status, $notes]);
        }

        // Update habit counters
        if ($status === 'done') {
            $stmt = $this->db->prepare("UPDATE habits SET done_counts = done_counts + 1 WHERE habit_id = ?");
        } elseif ($status === 'skipped' || $status === 'missed') {
            $stmt = $this->db->prepare("UPDATE habits SET skipped = skipped + 1 WHERE habit_id = ?");
        }
        $stmt->execute([$habitId]);

        return true;
    }

    /* ---------- GET HABIT PROGRESS ---------- */
    public function getHabitProgress($habitId, $month = null, $year = null)
    {
        $month = $month ? intval($month) : intval(date('m'));
        $year = $year ? intval($year) : intval(date('Y'));

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_days,
                    SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped_days
             FROM habit_logs
             WHERE habit_id = ? AND MONTH(log_date) = ? AND YEAR(log_date) = ?"
        );
        $stmt->execute([$habitId, $month, $year]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* ---------- INIT ---------- */
try {
    $database = new Database();
    $dbConnection = $database->getConnection();
    $habitApi = new HabitAPI($dbConnection);

    $method = $_SERVER['REQUEST_METHOD'];
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $habitId = isset($_GET['habit_id']) ? intval($_GET['habit_id']) : 0;

    // Read JSON body (if present)
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);
    if (!is_array($data)) $data = [];

    switch ($method) {
        case 'GET':
            if ($habitId) {
                // Use GET params for month/year
                $month = $_GET['month'] ?? null;
                $year = $_GET['year'] ?? null;
                $progress = $habitApi->getHabitProgress($habitId, $month, $year);
                http_response_code(200);
                echo json_encode($progress);
            } else {
                if (!$userId) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing user_id"]);
                    exit;
                }
                $habits = $habitApi->getHabits($userId);
                http_response_code(200);
                echo json_encode($habits);
            }
            break;

        case 'POST':
            // Accept multiple possible key names from frontend:
            // prefer habitName/habitDescription/habitFrequency, fallback to name/description/frequency
            $name = $data['habitName'] ?? $data['name'] ?? null;
            $description = $data['habitDescription'] ?? $data['description'] ?? null;
            $frequency = $data['habitFrequency'] ?? $data['frequency'] ?? null;

            if (!$userId) {
                http_response_code(400);
                echo json_encode(["error" => "Missing user_id"]);
                exit;
            }

            if ($name && $description && $frequency) {
                $insertId = $habitApi->addHabit($userId, $name, $description, $frequency);
                if ($insertId !== false) {
                    http_response_code(201);
                    echo json_encode([
                        "message" => "Habit added successfully",
                        "habit_id" => $insertId
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to add habit"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields (name/description/frequency)"]);
            }
            break;

        case 'PUT':
            // Expect habit_id + name/description/frequency in JSON body
            $habit_id = $data['habit_id'] ?? $data['habitId'] ?? null;
            $name = $data['habitName'] ?? $data['name'] ?? null;
            $description = $data['habitDescription'] ?? $data['description'] ?? null;
            $frequency = $data['habitFrequency'] ?? $data['frequency'] ?? null;

            if (!$habit_id) {
                http_response_code(400);
                echo json_encode(["error" => "Missing habit_id"]);
                exit;
            }

            if ($name && $description && $frequency) {
                $success = $habitApi->updateHabit($habit_id, $name, $description, $frequency);
                if ($success) {
                    http_response_code(200);
                    echo json_encode(["message" => "Habit updated successfully"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to update habit"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields (name/description/frequency)"]);
            }
            break;

        case 'PATCH':
            $habit_id = $data['habit_id'] ?? $data['habitId'] ?? null;
            $status = $data['status'] ?? null;
            $notes = $data['notes'] ?? '';

            if (!$habit_id || !$status) {
                http_response_code(400);
                echo json_encode(["error" => "Missing habit_id or status"]);
                exit;
            }

            $success = $habitApi->markHabitDone($habit_id, $status, $notes);
            if ($success) {
                http_response_code(200);
                echo json_encode(["message" => "Habit status updated"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to update habit status"]);
            }
            break;

        case 'DELETE':
            if ($habitId) {
                $success = $habitApi->deleteHabit($habitId);
                if ($success) {
                    http_response_code(200);
                    echo json_encode(["message" => "Habit deleted successfully"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to delete habit"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Invalid habit ID"]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    // Log $e->getMessage() to server logs in production; return generic message to client
    error_log("DB ERROR: " . $e->getMessage());
    echo json_encode(["error" => "Database error"]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("ERROR: " . $e->getMessage());
    echo json_encode(["error" => "Internal server error"]);
}
