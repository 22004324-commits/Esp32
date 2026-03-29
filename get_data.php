<?php
// BẮT BUỘC: Cho phép trang web từ GitHub (hoặc bất kỳ đâu) gọi vào file này
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Hàm giải mã RC4
function rc4Decrypt($key, $encrypted_hex) {
    $str = hex2bin($encrypted_hex);
    $s = array();
    for ($i = 0; $i < 256; $i++) { $s[$i] = $i; }
    $j = 0;
    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
        $x = $s[$i]; $s[$i] = $s[$j]; $s[$j] = $x;
    }
    $i = 0; $j = 0; $res = '';
    for ($y = 0; $y < strlen($str); $y++) {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        $x = $s[$i]; $s[$i] = $s[$j]; $s[$j] = $x;
        $res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
    }
    return $res;
}

$secret_key = "IoTSecretKey";

// THAY ĐỔI THÔNG TIN DATABASE CỦA BẠN TRÊN CLOUD TẠI ĐÂY
$conn = new mysqli("localhost", "root", "", "iot_database");

if ($conn->connect_error) {
    die(json_encode(array("error" => "Connection failed")));
}

$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

$data = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Giải mã nhiệt độ trước khi đẩy ra dạng JSON
        $row['decrypted_temp'] = htmlspecialchars(rc4Decrypt($secret_key, $row["encrypted_temp"]));
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>