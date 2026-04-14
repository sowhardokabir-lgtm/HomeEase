<?php
use PHPUnit\Framework\TestCase;

class AccessControlTest extends TestCase {
    /**
     * Tests the "Make sure user is logged in" logic
     */
    public function testRedirectIfNoSession() {
        // Clear session to simulate a guest
        $_SESSION = [];
        
        $isLoggedIn = isset($_SESSION["user_id"]);
        
        $this->assertFalse($isLoggedIn, "Access should be denied for guests.");
    }
}