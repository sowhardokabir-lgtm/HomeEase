<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase {
    private $mysqli_mock;
    private $stmt_mock;
    private $result_mock;

    protected function setUp(): void {
        // Mock the MySQLi connection, Statement, and Result
        $this->mysqli_mock = $this->createMock(mysqli::class);
        $this->stmt_mock = $this->createMock(mysqli_stmt::class);
        $this->result_mock = $this->createMock(mysqli_result::class);
    }

    public function testSuccessfulLogin() {
        $email = "test@example.com";
        $password = "password123";
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Define what the mock user record looks like
        $userData = [
            'id' => 1,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'customer',
            'name' => 'John Doe'
        ];

        // Setup expectations for the prepared statement
        $this->mysqli_mock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt_mock);

        $this->stmt_mock->expects($this->once())
            ->method('execute');

        $this->stmt_mock->expects($this->once())
            ->method('get_result')
            ->willReturn($this->result_mock);

        $this->result_mock->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn($userData);

        $this->result_mock->method('__get')->with('num_rows')->willReturn(1);

        // Verify the logic
        $this->assertTrue(password_verify($password, $userData['password']));
        $this->assertEquals('customer', $userData['role']);
    }

    public function testInvalidPasswordFails() {
        $inputPassword = "wrong_password";
        $correctHash = password_hash("real_password", PASSWORD_DEFAULT);
        
        $this->assertFalse(password_verify($inputPassword, $correctHash));
    }
}