<?php
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase {
    
    /**
     * Tests the reward point deduction logic from bookservice.php
     */
    public function testDiscountCalculation() {
        $servicePrice = 500.00;
        $userPoints = 120.00; // 1 point = 1 unit of currency based on your code
        $redeemRequested = true;

        $discount = 0;
        if ($redeemRequested) {
            $discount = $userPoints;
        }

        $current_discount = 0;
        $remaining_discount = $discount;

        // Simulate the logic inside the foreach loop in bookservice.php
        if ($remaining_discount > 0) {
            if ($remaining_discount >= $servicePrice) {
                $current_discount = $servicePrice;
                $remaining_discount -= $servicePrice;
            } else {
                $current_discount = $remaining_discount;
                $remaining_discount = 0;
            }
        }

        $final_price = $servicePrice - $current_discount;

        $this->assertEquals(120, $current_discount);
        $this->assertEquals(380, $final_price);
        $this->assertEquals(0, $remaining_discount);
    }

    /**
     * Tests that past dates are rejected
     */
    public function testDateValidation() {
        $pastDate = "2020-01-01";
        $time = "12:00";
        
        $datetime = strtotime("$pastDate $time");
        $isValid = ($datetime >= time());

        $this->assertFalse($isValid, "Booking should not allow past dates.");
    }
}