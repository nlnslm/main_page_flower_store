<?php
header('Content-Type: application/json; charset=utf-8');
include_once 'dbconnect_flower.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['product_name']) || empty($data['product_price']) || empty($data['product_image'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
    exit;
}

$name = trim($data['product_name']);
$price = (float)$data['product_price'];
$image = trim($data['product_image']);
$occasion = !empty($data['product_occasion']) ? trim($data['product_occasion']) : 'birthday,congrats';
$description = !empty($data['product_description']) ? trim($data['product_description']) : '';

// Insert ลงในตาราง product โดยระบุชื่อคอลัมน์ตรงตามตารางจริง
$stmt = mysqli_prepare($conn, "INSERT INTO products (product_name, product_price, product_image, product_occasion, product_description) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sdsss", $name, $price, $image, $occasion, $description);

if (mysqli_stmt_execute($stmt)) {
    $inserted_id = mysqli_insert_id($conn);
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกสำเร็จ',
        'data' => [
            'id' => $inserted_id,
            'name' => $name,
            'price' => $price,
            'occasions' => array_filter(array_map('trim', explode(',', $occasion))),
            'img' => $image,
            'description' => $description
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>