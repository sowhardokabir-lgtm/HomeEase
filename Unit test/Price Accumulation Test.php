<?php
use PHPUnit\Framework\TestCase;

class PriceCalculationTest extends TestCase {
    
    public function testMultipleServiceTotal() {
        // Mocking data that would normally come from the 'services' table
        $selected_services = [
            ['id' => 101, 'name' => 'AC Repair', 'price' => 1500.00],
            ['id' => 102, 'name' => 'Cleaning', 'price' => 800.00]
        ];

        $total_before_discount = 0;
        foreach ($selected_services as $service) {
            $total_before_discount += $service['price'];
        }

        $this->assertEquals(2300.00, $total_before_discount, "Total price accumulation failed.");
    }
}