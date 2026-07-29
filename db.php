<?php
$conn = mysqli_connect("localhost", "root", "", "smart_barber_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>