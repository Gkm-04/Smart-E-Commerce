<?php
// $conn = new mysqli(
//     "sql102.infinityfree.com",      // Hostname من لوحة التحكم
//     "if0_41208171",           // Username من لوحة التحكم
//     "uZfv7326oIu",               // Password
//     "if0_41208171_ecommerce"  // Database Name
// );

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
//  $conn->set_charset("utf8mb4");
?> 
<?php
// لم نحذف أي شيء — فقط نقلناه لمجلد config

$host = "localhost";
$user = "root";
$pass = "";
$db   = "Ecommerce";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// تحسين احترافي: تحديد charset
$conn->set_charset("utf8mb4");
?>