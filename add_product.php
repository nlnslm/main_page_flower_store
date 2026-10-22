<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'dbconnect_flower.php';

// ตรวจสอบสิทธิ์ว่าล็อกอินหรือยัง และเป็น admin หรือไม่
$user_id = $_SESSION['user_id'] ?? 0;
$isAdmin = false;

if ($user_id > 0) {
    $chk = mysqli_query($conn, "SELECT role FROM users WHERE user_id = $user_id");
    if ($row = mysqli_fetch_assoc($chk)) {
        if ($row['role'] === 'admin') {
            $isAdmin = true;
        }
    }
}

// ถ้าไม่ใช่ Admin ไม่อนุญาตให้เพิ่มสินค้า
if (!$isAdmin) {
    echo json_encode([
        'success' => false, 
        'message' => 'คุณไม่มีสิทธิ์ในการเพิ่มสินค้า (เฉพาะผู้ดูแลระบบเท่านั้น)'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// รับข้อมูล JSON และบันทึกสินค้าตามเดิม
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

$name = trim($data['product_name'] ?? '');
$price = isset($data['product_price']) ? (float)$data['product_price'] : 0;
$image = trim($data['product_image'] ?? '');
$occasion = trim($data['product_occasion'] ?? 'birthday,congrats,marriage,graduate');
$description = trim($data['product_description'] ?? '');

if (empty($name) || $price <= 0 || empty($image)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "INSERT INTO products (product_name, product_price, product_image, product_occasion, product_description) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sdsss", $name, $price, $image, $occasion, $description);

if (mysqli_stmt_execute($stmt)) {
    $new_id = mysqli_insert_id($conn);
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกสำเร็จ',
        'data' => [
            'id' => $new_id,
            'name' => $name,
            'price' => $price,
            'occasions' => array_filter(array_map('trim', explode(',', $occasion))),
            'img' => $image,
            'description' => $description
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => 'บันทึกล้มเหลว: ' . mysqli_stmt_error($stmt)], JSON_UNESCAPED_UNICODE);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>