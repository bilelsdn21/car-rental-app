<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/auth.php';

class AuthTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        // Database connection (adjust database name)
        $this->conn = new mysqli("localhost", "root", "", "rental_site");
        if ($this->conn->connect_error) {
            $this->fail("MySQL connection failed: " . $this->conn->connect_error);
        }

        // Insert test user (run once or ensure user exists)
        $hashed_password = password_hash("password123", PASSWORD_DEFAULT);
        $this->conn->query("INSERT IGNORE INTO users (name, email, password, role) VALUES ('Test User', 'test@example.com', '$hashed_password', 'user')");
    }

   

    public function testSQLInjectionAttempt() {
        $result = check_login("' OR '1'='1", "anything", $this->conn);
        $this->assertFalse($result, "SQL injection attempt should be blocked.");
    }

    public function testMultipleInvalidUsers() {
        $invalidUsers = [
            ["email" => "hacker@example.com", "password" => "test"],
            ["email" => "test' OR '1'='1", "password" => "random"]
        ];
        foreach ($invalidUsers as $user) {
            $result = check_login($user["email"], $user["password"], $this->conn);
            $this->assertFalse($result, "Invalid user {$user['email']} should not authenticate.");
        }
    }

    protected function tearDown(): void {
        $this->conn->close();
    }
}
?>