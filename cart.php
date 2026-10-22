<?php
session_start();
require_once 'dbconnect_flower.php';

// สร้างตะกร้าถ้ายังไม่มี
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ดึงข้อมูลที่อยู่ของผู้ใช้ปัจจุบันมาเตรียมไว้
$saved_user = null;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $u_res = mysqli_query($conn, "SELECT fullname, phone, address, subdistrict, district, province, zipcode FROM users WHERE user_id = $uid");
    if ($u_res) {
        $saved_user = mysqli_fetch_assoc($u_res);
    }
}

// จัดการคำขอเพิ่ม/ลด/ลบ สินค้า
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    // 1. เพิ่มสินค้าลงตะกร้า
    if ($action === 'add' && $id > 0) {
        $qty = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $qty;
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
        header("Location: cart.php");
        exit;
    }

    // 2. ปรับจำนวน (+ / -)
    if ($action === 'update' && $id > 0) {
        $type = $_POST['type'] ?? '';
        if ($type === 'plus') {
            $_SESSION['cart'][$id]++;
        } elseif ($type === 'minus') {
            $_SESSION['cart'][$id]--;
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
        header("Location: cart.php");
        exit;
    }

    // 3. ลบสินค้าออกจากตะกร้า
    if ($action === 'remove' && $id > 0) {
        unset($_SESSION['cart'][$id]);
        header("Location: cart.php");
        exit;
    }

    // 4. ล้างตะกร้าทั้งหมด
    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        header("Location: cart.php");
        exit;
    }
}

// ดึงข้อมูลสินค้าที่เลือกไว้จาก MySQL และคำนวณราคา
$cart_items = [];
$total_price = 0;
$total_qty = 0;

// กำหนดค่าจัดส่งคงที่ 80 บาท ต่อครั้ง (ถ้าไม่มีสินค้าในรถเข็น ค่าจัดส่งจะเป็น 0)
$shipping_fee = 0;
$grand_total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $product_ids));

    $sql = "SELECT product_id, product_name, product_price, product_image, product_occasion 
            FROM products 
            WHERE product_id IN ($ids_string)";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pid = $row['product_id'];
            $quantity = $_SESSION['cart'][$pid];
            $subtotal = $row['product_price'] * $quantity;

            $total_price += $subtotal;
            $total_qty += $quantity;

            $cart_items[] = [
                'id' => $row['product_id'],
                'name' => $row['product_name'],
                'price' => $row['product_price'],
                'image' => $row['product_image'],
                'occasion' => $row['product_occasion'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
    }

    // คิดค่าจัดส่ง 80 บาท เมื่อมีสินค้าในรถเข็น
    if ($total_qty > 0) {
        $shipping_fee = 80;
        $grand_total = $total_price + $shipping_fee;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>รถเข็นสินค้า | TERMTEM+</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Bai Jamjuree', sans-serif;
    }
    body {
      background-color: #fffdf8;
      color: #333;
      padding: 20px 15px;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 15px;
      border-bottom: 2px solid #8a4b0f;
      margin-bottom: 25px;
    }
    .logo {
      font-size: 20px;
      font-weight: bold;
      color: #8a4b0f;
      text-decoration: none;
    }
    .back-link {
      color: #8a4b0f;
      text-decoration: none;
      font-size: 15px;
      font-weight: 600;
    }
    .page-title {
      color: #8a4b0f;
      font-size: 24px;
      margin-bottom: 20px;
    }
    .cart-layout {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 25px;
      align-items: start;
    }
    @media (max-width: 768px) {
      .cart-layout { grid-template-columns: 1fr; }
    }
    .cart-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .cart-item {
      display: flex;
      align-items: center;
      gap: 15px;
      background: #fff;
      padding: 15px;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
    }
    .cart-item img {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 6px;
      background-color: #fff5c8;
    }
    .cart-item-info { flex: 1; }
    .cart-item-name {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 4px;
    }
    .cart-item-price {
      font-size: 14px;
      color: #8a4b0f;
      font-weight: 600;
      margin-bottom: 8px;
    }
    .qty-controls {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .qty-btn {
      width: 28px;
      height: 28px;
      background: #fffdf8;
      border: 1px solid #8a4b0f;
      color: #8a4b0f;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }
    .qty-btn:hover {
      background: #8a4b0f;
      color: #fff;
    }
    .qty-number {
      font-weight: 600;
      min-width: 24px;
      text-align: center;
    }
    .delete-btn {
      background: none;
      border: none;
      color: #c94a4a;
      font-size: 14px;
      cursor: pointer;
      text-decoration: underline;
    }
    .cart-summary {
      background: #fff;
      padding: 20px;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
      position: sticky;
      top: 20px;
    }
    .summary-title {
      font-size: 18px;
      font-weight: bold;
      color: #8a4b0f;
      margin-bottom: 15px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 15px;
      color: #555;
    }
    .summary-total {
      display: flex;
      justify-content: space-between;
      font-size: 18px;
      font-weight: bold;
      color: #8a4b0f;
      border-top: 1px dashed #ddd;
      padding-top: 12px;
      margin-top: 12px;
    }
    .checkout-btn {
      width: 100%;
      background-color: #8a4b0f;
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 4px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 18px;
    }
    .checkout-btn:hover { background-color: #6d3a0a; }
    .clear-cart-btn {
      width: 100%;
      background: none;
      border: 1px solid #ccc;
      color: #777;
      padding: 8px;
      border-radius: 4px;
      font-size: 13px;
      cursor: pointer;
      margin-top: 10px;
    }
    .empty-cart {
      text-align: center;
      padding: 60px 20px;
      background: #fff;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
    }
    .shop-now-btn {
      display: inline-block;
      background: #8a4b0f;
      color: #fff;
      padding: 10px 20px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
      margin-top: 15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <a href="index.php" class="logo">🌸 TERMTEM+</a>
      <a href="index.php" class="back-link">← เลือกดูสินค้าเพิ่ม</a>
    </header>

    <h1 class="page-title">🛒 รถเข็นของคุณ (<?= $total_qty ?> ชิ้น)</h1>

    <?php if (empty($cart_items)): ?>
      <div class="empty-cart">
        <p>ยังไม่มีดอกไม้ในรถเข็นของคุณเลย</p>
        <a href="index.php" class="shop-now-btn">เลือกดูช่อดอกไม้ตอนนี้</a>
      </div>
    <?php else: ?>
      <div class="cart-layout">
        <!-- รายการสินค้า -->
        <div class="cart-list">
          <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='https://placehold.co/400x500?text=TERMTEM+'">
              
              <div class="cart-item-info">
                <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="cart-item-price">฿<?= number_format($item['price']) ?></div>
                
                <div class="qty-controls">
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="type" value="minus">
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="qty-btn">-</button>
                  </form>

                  <span class="qty-number"><?= $item['quantity'] ?></span>

                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="type" value="plus">
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="qty-btn">+</button>
                  </form>
                </div>
              </div>

              <div style="text-align: right;">
                <div style="font-weight: bold; color: #8a4b0f; margin-bottom: 10px;">
                  ฿<?= number_format($item['subtotal']) ?>
                </div>
                <form method="POST">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="delete-btn">ลบ</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- กล่องแสดงที่อยู่จัดส่งอัตโนมัติ -->
        <div style="margin-top: 15px; padding: 12px; background: #fffdf8; border: 1px solid #8a4b0f; border-radius: 6px; font-size: 13px;">
            <div style="font-weight: 600; color: #8a4b0f; margin-bottom: 5px; display: flex; justify-content: space-between;">
                <span>📍 ที่อยู่จัดส่งของคุณ</span>
                <a href="profile.php" style="color: #8a4b0f; text-decoration: underline;">แก้ไข</a>
            </div>
            <?php if (!empty($saved_user['address'])): ?>
                <p><strong>ผู้รับ:</strong> <?= htmlspecialchars($saved_user['fullname']) ?> (<?= htmlspecialchars($saved_user['phone']) ?>)</p>
                <p style="color: #666; margin-top: 3px;">
                    <?= htmlspecialchars($saved_user['address']) ?> 
                    ต.<?= htmlspecialchars($saved_user['subdistrict']) ?> 
                    อ.<?= htmlspecialchars($saved_user['district']) ?> 
                    จ.<?= htmlspecialchars($saved_user['province']) ?> 
                    <?= htmlspecialchars($saved_user['zipcode']) ?>
                </p>
            <?php else: ?>
                <p style="color: #c94a4a;">คุณยังไม่ได้บันทึกที่อยู่จัดส่ง <a href="profile.php" style="color: #8a4b0f; font-weight: bold;">คลิกเพื่อเพิ่มที่อยู่</a></p>
            <?php endif; ?>
        </div>

        <!-- กล่องสรุปคำสั่งซื้อพร้อมคิดค่าจัดส่ง 80 บาท -->
        <div class="cart-summary">
          <div class="summary-title">สรุปคำสั่งซื้อ</div>
          <div class="summary-row">
            <span>รวมค่าสินค้า:</span>
            <span>฿<?= number_format($total_price) ?></span>
          </div>
          <div class="summary-row">
            <span>จำนวนทั้งหมด:</span>
            <span><?= $total_qty ?> ชิ้น</span>
          </div>
          <div class="summary-row">
            <span>ค่าจัดส่ง:</span>
            <span style="font-weight: 600; color: #8a4b0f;">฿<?= number_format($shipping_fee) ?></span>
          </div>
          <div class="summary-total">
            <span>ยอดชำระสุทธิ:</span>
            <span>฿<?= number_format($grand_total) ?></span>
          </div>

          <button class="checkout-btn" onclick="alert('ยอดชำระสุทธิ ฿<?= number_format($grand_total) ?>\nระบบกำลังนำคุณไปยังขั้นตอนการชำระเงิน...')">ดำเนินการสั่งซื้อ</button>

          <form method="POST" onsubmit="return confirm('คุณต้องการล้างสินค้าทั้งหมดใช่หรือไม่?');">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="clear-cart-btn">ล้างรถเข็นทั้งหมด</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>