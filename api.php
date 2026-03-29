<?php
// Cấu hình CSDL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "iot_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $encrypted_temp = $_POST["temperature"];

    // Lưu chuỗi HEX mã hóa thẳng vào database
    $sql = "INSERT INTO sensor_data (encrypted_temp) VALUES ('$encrypted_temp')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Lưu dữ liệu thành công!";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
$conn->close();
?>