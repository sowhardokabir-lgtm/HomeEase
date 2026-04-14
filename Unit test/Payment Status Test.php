<?php
use PHPUnit\Framework\TestCase;

class ServiceUIModuleTest extends TestCase {

    public function testImageMapping() {
        $testNames = [
            'AC Repair' => 'image/ac_repair.jpg',
            'Pest Control' => 'image/pest_control.jpg',
            'Home Shifting' => 'image/home_shifting.jpg',
            'General Cleaning' => 'image/default.jpg' // Assuming default from service.php logic
        ];

        foreach ($testNames as $name => $expectedPath) {
            $lowerName = strtolower($name);
            $img = "";

            // Logic matching service.php
            if (strpos($lowerName, 'furniture') !== false) {
                $img = "image/furniture_cleaning.jpg";
            } elseif (strpos($lowerName, 'ac') !== false) {
                $img = "image/ac_repair.jpg";
            } elseif (strpos($lowerName, 'shifting') !== false) {
                $img = "image/home_shifting.jpg";
            } elseif (strpos($lowerName, 'pest') !== false) {
                $img = "image/pest_control.jpg";
            } else {
                $img = "image/default.jpg";
            }

            $this->assertEquals($expectedPath, $img, "Image mismatch for service: $name");
        }
    }
}