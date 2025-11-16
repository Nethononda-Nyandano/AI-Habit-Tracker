<?php 


require_once "database.php";



class Auth {
    private string $email;
    private string $password;

    public function __construct(string $email, string $password) {
        // Trim and normalize inputs
        $this->email = strtolower(trim($email));
        $this->password = trim($password);
    }

    public function login(): array {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT user_id, name, surname, email, phone, password_hash 
                  FROM users 
                  WHERE email = :email
                  LIMIT 1";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($this->password, $user['password_hash'])) {
                // Remove sensitive info
                unset($user['password_hash']);

                
                session_start();
                $_SESSION['user_id'] = $user['user_id'];

                return ['status' => 'success', 'user' => $user];
            }

            return ['status' => 'error', 'message' => 'Invalid email or password'];

        } catch (PDOException $e) {
            // Log error in real application instead of exposing details
            return ['status' => 'error', 'message' => 'Database error'];
        }
    }
}
