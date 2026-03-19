<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase {
    
    // Test 1: Fail if fields are empty
    public function testLoginFailsWithEmptyFields() {
        $mockDb = $this->createMock(PDO::class);
        $auth = new Auth($mockDb);

        $result = $auth->login("", "");
        $this->assertEquals("empty_fields", $result);
    }

    // Test 2: Successful Login
    public function testLoginSucceedsWithValidCredentials() {
        // 1. Create a fake Statement
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetch')->willReturn([
            'id' => 1,
            'password' => password_hash("secret123", PASSWORD_DEFAULT)
        ]);

        // 2. Create a fake DB that returns our fake Statement
        $mockDb = $this->createMock(PDO::class);
        $mockDb->method('prepare')->willReturn($mockStmt);

        $auth = new Auth($mockDb);

        // 3. Run the test
        $result = $auth->login("test@example.com", "secret123");
        $this->assertEquals("success", $result);
    }

    // Test 3: Fail if password is wrong
    public function testLoginFailsWithWrongPassword() {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('fetch')->willReturn([
            'id' => 1,
            'password' => password_hash("correct_password", PASSWORD_DEFAULT)
        ]);

        $mockDb = $this->createMock(PDO::class);
        $mockDb->method('prepare')->willReturn($mockStmt);

        $auth = new Auth($mockDb);

        $result = $auth->login("test@example.com", "wrong_password");
        $this->assertEquals("invalid_credentials", $result);
    }
}
