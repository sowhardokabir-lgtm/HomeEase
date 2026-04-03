
<?php
include "db.php"; // include database connection

$result = $conn->query("SELECT * FROM services");

echo "<h2>Available Services</h2>";
while ($row = $result->fetch_assoc()) {
    echo $row["name"] . " - " . $row["price"] . " Tk <br>";
}
?>
