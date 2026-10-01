<?php
$conn = mysqli_connect("localhost", "root", "", "jadwal");
$result = mysqli_query($conn, "SELECT * FROM users");
while($row = mysqli_fetch_assoc($result)){
    echo $row['username'] . " - " . $row['password'] . "<br>";
}
?>