<?php
$con = mysqli_connect("localhost", "root", "", "videoteka");

if (mysqli_connect_errno()) {
    echo "Greška pri spajanju na MySQL: " . mysqli_connect_error();
    exit();
}
?>
