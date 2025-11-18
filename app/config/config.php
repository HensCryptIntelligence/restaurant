
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "restaurant";

if ($conn) {
  
  $conn = mysqli_connect($host, $user, $pass, $db);
  
} else {
  
  die("Koneksi gagal : " . mysqli_connect_error());
  
}
?>
