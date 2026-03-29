<?php
// Hàm giải mã RC4 bằng PHP
function rc4Decrypt($key, $encrypted_hex) {
    // Chuyển HEX string về Binary string
    $str = hex2bin($encrypted_hex);
    
    $s = array();
    for ($i = 0; $i < 256; $i++) {
        $s[$i] = $i;
    }
    $j = 0;
    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
        $x = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $x;
    }
    $i = 0; $j = 0; $res = '';
    for ($y = 0; $y < strlen($str); $y++) {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        $x = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $x;
        $res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
    }
    return $res;
}

$conn = new mysqli("localhost", "root", "", "iot_database");
$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);
$secret_key = "IoTSecretKey"; // Khóa phải giống hệt trên ESP32
?>

<!DOCTYPE html>
<html>
<head>
    <title>IoT Sensor Dashboard</title>
    <style>
        table { border-collapse: collapse; width: 60%; margin: 20px auto; font-family: Arial;}
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Dữ Liệu Cảm Biến Từ ESP32</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Dữ liệu mã hóa trong CSDL (HEX)</th>
            <th>Nhiệt độ giải mã (°C)</th>
            <th>Thời gian thu thập</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Gọi hàm giải mã
                $decrypted_temp = rc4Decrypt($secret_key, $row["encrypted_temp"]);
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td><code>" . $row["encrypted_temp"] . "</code></td>";
                echo "<td><strong>" . htmlspecialchars($decrypted_temp) . " °C</strong></td>";
                echo "<td>" . $row["created_at"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Không có dữ liệu</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>