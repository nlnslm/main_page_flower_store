<?php
session_start();
require_once 'dbconnect_flower.php';

// ดึง user_id ของผู้ใช้ที่ล็อกอินอยู่
$user_id = (int)($_SESSION['user_id'] ?? 1);

// รับค่าจากแบบฟอร์มที่ส่งมาจาก order.php
$recipient      = trim($_POST['receiver_name'] ?? '');
$phone          = trim($_POST['receiver_phone'] ?? '');
$address        = trim($_POST['receiver_address'] ?? '');
$delivery_date  = !empty($_POST['delivery_date']) ? $_POST['delivery_date'] : null;
$delivery_time  = trim($_POST['delivery_time'] ?? '');
$card_message   = trim($_POST['card_message'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'โอนเงินผ่านธนาคาร');
$shipping_fee   = 80.00;

$order_items = [];
$subtotal = 0;

// 1. ตรวจสอบสินค้าจากรถเข็น (แก้ไขชื่อตารางเป็น product ไม่มี s)
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $p_ids = array_map('intval', array_keys($_SESSION['cart']));
    if (!empty($p_ids)) {
        $p_res = mysqli_query($conn, "SELECT product_id, product_name, product_price, product_image FROM products WHERE product_id IN (" . implode(',', $p_ids) . ")");
        if ($p_res) {
            while ($row = mysqli_fetch_assoc($p_res)) {
                $pid = $row['product_id'];
                $qty = is_array($_SESSION['cart'][$pid]) ? (int)($_SESSION['cart'][$pid]['qty'] ?? 1) : (int)$_SESSION['cart'][$pid];
                $qty = max(1, $qty);

                $row['quantity'] = $qty;
                $row['line_total'] = $row['product_price'] * $qty;
                $subtotal += $row['line_total'];
                $order_items[] = $row;
            }
        }
    }
} 
// กรณีสั่งซื้อชิ้นเดียวโดยตรง
elseif (isset($_POST['product_id']) && (int)$_POST['product_id'] > 0) {
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    $p_res = mysqli_query($conn, "SELECT product_id, product_name, product_price, product_image FROM products WHERE product_id = $pid");
    if ($p_res && ($row = mysqli_fetch_assoc($p_res))) {
        $row['quantity'] = $qty;
        $row['line_total'] = $row['product_price'] * $qty;
        $subtotal += $row['line_total'];
        $order_items[] = $row;
    }
}

$grand_total = $subtotal + $shipping_fee;

// 2. บันทึกคำสั่งซื้อลงตาราง orders จริง (ถ้ามีการส่งข้อมูลมา)
if (!empty($order_items) && !empty($recipient)) {
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, product_id, quantity, unit_price, total_amount, recipient, phone, address, delivery_date, delivery_time, card_message, payment_method, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

    foreach ($order_items as $item) {
        $pid   = $item['product_id'];
        $qty   = $item['quantity'];
        $price = $item['product_price'];
        $total = $item['line_total'] + $shipping_fee;

        mysqli_stmt_bind_param($stmt, "iiiddsssssss", 
            $user_id, 
            $pid, 
            $qty, 
            $price, 
            $total, 
            $recipient, 
            $phone, 
            $address, 
            $delivery_date, 
            $delivery_time, 
            $card_message, 
            $payment_method
        );
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    // เก็บประวัติล่าสุดไว้ใน Session ชั่วคราวเพื่อป้องกันการกดรีเฟรชแล้วข้อมูลหาย
    $_SESSION['last_order_summary'] = [
        'recipient'      => $recipient,
        'phone'          => $phone,
        'address'        => $address,
        'delivery_date'  => $delivery_date,
        'delivery_time'  => $delivery_time,
        'card_message'   => $card_message,
        'payment_method' => $payment_method,
        'shipping_fee'   => $shipping_fee,
        'subtotal'       => $subtotal,
        'grand_total'    => $grand_total,
        'items'          => $order_items
    ];

    // ล้างสินค้าในรถเข็น
    unset($_SESSION['cart']);
}

// หากเป็นการรีเฟรชหน้าเว็บ ให้ดึงข้อมูลล่าสุดจาก Session มาแสดง
if (empty($order_items) && isset($_SESSION['last_order_summary'])) {
    $last           = $_SESSION['last_order_summary'];
    $recipient      = $last['recipient'];
    $phone          = $last['phone'];
    $address        = $last['address'];
    $delivery_date  = $last['delivery_date'];
    $delivery_time  = $last['delivery_time'];
    $card_message   = $last['card_message'];
    $payment_method = $last['payment_method'];
    $shipping_fee   = $last['shipping_fee'];
    $subtotal       = $last['subtotal'];
    $grand_total    = $last['grand_total'];
    $order_items    = $last['items'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สรุปคำสั่งซื้อ | TERMTEM+</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Bai Jamjuree', sans-serif; }
    body { background-color: #fffdf8; color: #333; padding: 25px 15px; }
    .container { max-width: 680px; margin: 0 auto; }
    header {
      display: flex; justify-content: space-between; align-items: center;
      padding-bottom: 15px; border-bottom: 2px solid #8a4b0f; margin-bottom: 25px;
    }
    .logo { font-size: 20px; font-weight: bold; color: #8a4b0f; text-decoration: none; }
    .nav-links a { color: #8a4b0f; text-decoration: none; font-weight: 600; margin-left: 15px; }

    .summary-card {
      background: #ffffff;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
      padding: 30px;
      box-shadow: 0 4px 15px rgba(138, 75, 15, 0.08);
    }
    .check-icon {
      width: 55px; height: 55px; line-height: 55px;
      background-color: #eaf7ec; color: #28a745;
      border-radius: 50%; font-size: 26px; text-align: center;
      margin: 0 auto 12px;
    }
    .card-title { text-align: center; color: #8a4b0f; font-size: 24px; font-weight: 700; }
    .card-subtitle { text-align: center; font-size: 14px; color: #888; margin-bottom: 25px; }

    .section-title {
      font-size: 16px; font-weight: 700; color: #8a4b0f;
      border-bottom: 1px solid #f2ede7; padding-bottom: 8px; margin-bottom: 15px;
    }
    .item-row {
      display: flex; align-items: center; gap: 15px; margin-bottom: 12px;
    }
    .item-img {
      width: 65px; height: 65px; object-fit: cover; border-radius: 6px; background: #fff5c8;
    }
    .item-detail { flex: 1; font-size: 14px; }
    .item-price { font-weight: 600; color: #8a4b0f; }

    .info-grid {
      display: grid; grid-template-columns: 120px 1fr; row-gap: 8px; font-size: 14px; color: #555; margin-bottom: 20px;
    }
    .total-box {
      border-top: 1px dashed #ebd9c8; padding-top: 12px; margin-top: 15px;
    }
    .total-row {
      display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px;
    }
    .grand-total {
      font-size: 18px; font-weight: bold; color: #8a4b0f;
    }

    .btn-group {
      display: flex; gap: 12px; margin-top: 25px;
    }
    .btn-main {
      flex: 1; text-align: center; background-color: #8a4b0f; color: #fff;
      text-decoration: none; padding: 12px; border-radius: 4px; font-weight: 600;
    }
    .btn-sub {
      flex: 1; text-align: center; border: 1px solid #8a4b0f; color: #8a4b0f;
      text-decoration: none; padding: 12px; border-radius: 4px; font-weight: 600;
    }
  </style>
</head>
<body>

  <div class="container">
    <header>
      <a href="index.php" class="logo">🌸 TERMTEM+</a>
      <div class="nav-links">
        <a href="index.php">หน้าร้านค้า</a>
        <a href="my_orders.php">📦 คำสั่งซื้อของฉัน</a>
      </div>
    </header>

    <div class="summary-card">
      <div class="check-icon">✓</div>
      <h1 class="card-title">สรุปคำสั่งซื้อ</h1>
      <p class="card-subtitle">ตรวจสอบรายละเอียดที่คุณกรอกในหน้าสั่งซื้อ ♡</p>

      <?php if (!empty($order_items)): ?>
        <div class="section-title">สรุปรายการสั่งซื้อ</div>
        <?php foreach ($order_items as $item): ?>
          <div class="item-row">
            <img src="<?= htmlspecialchars($item['product_image']) ?>" class="item-img" onerror="this.src='https://placehold.co/100x100?text=Flower';">
            <div class="item-detail">
              <strong><?= htmlspecialchars($item['product_name']) ?></strong>
              <div style="color: #777; font-size: 13px;">จำนวน: <?= $item['quantity'] ?> ช่อ (฿<?= number_format($item['product_price']) ?> / ช่อ)</div>
            </div>
            <div class="item-price">฿<?= number_format($item['line_total']) ?></div>
          </div>
        <?php endforeach; ?>

        <div class="total-box">
          <div class="total-row">
            <span>ราคาสินค้า</span>
            <span>฿<?= number_format($subtotal) ?></span>
          </div>
          <div class="total-row">
            <span>ค่าจัดส่ง</span>
            <span>฿<?= number_format($shipping_fee) ?></span>
          </div>
          <div class="total-row grand-total" style="margin-top: 8px;">
            <span>ยอดรวม</span>
            <span>฿<?= number_format($grand_total) ?></span>
          </div>
        </div>

        <div class="section-title" style="margin-top: 25px;">ข้อมูลจัดส่ง</div>
        <div class="info-grid">
          <div>ชื่อผู้รับ:</div>
          <div><strong><?= htmlspecialchars($recipient) ?></strong></div>

          <div>เบอร์โทรศัพท์:</div>
          <div><?= htmlspecialchars($phone) ?></div>

          <div>ที่อยู่จัดส่ง:</div>
          <div><?= htmlspecialchars($address) ?></div>

          <div>วันที่จัดส่ง:</div>
          <div><?= htmlspecialchars($delivery_date ?: 'ตามที่กำหนด') ?> (ช่วงเวลา <?= htmlspecialchars($delivery_time) ?>)</div>

          <?php if (!empty($card_message)): ?>
            <div>ข้อความบนการ์ด:</div>
            <div style="font-style: italic;">"<?= htmlspecialchars($card_message) ?>"</div>
          <?php endif; ?>

          <div>วิธีชำระเงิน:</div>
          <div><?= htmlspecialchars($payment_method) ?></div>
        </div>

        <div class="btn-group">
          <a href="my_orders.php" class="btn-main">ไปยังคำสั่งซื้อของฉัน</a>
          <a href="index.php" class="btn-sub">กลับสู่หน้าร้าน</a>
        </div>
      <?php else: ?>
        <div style="text-align: center; padding: 20px; color: #888;">
          <p>ไม่มีข้อมูลคำสั่งซื้อในรอบนี้</p>
          <a href="index.php" class="btn-main" style="display: inline-block; margin-top: 15px; width: auto; padding: 10px 25px;">กลับสู่หน้าร้าน</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>