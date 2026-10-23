<?php
session_start();
require_once 'dbconnect_flower.php';

// ดึง user_id ของคนที่ล็อกอินอยู่
$user_id = (int)($_SESSION['user_id'] ?? 1);

// ดึงเบอร์โทรศัพท์ของผู้ใช้งานคนนี้จากตาราง users มาใช้ค้นหา
$u_res = mysqli_query($conn, "SELECT user_phone FROM users WHERE user_id = $user_id");
$u_data = $u_res ? mysqli_fetch_assoc($u_res) : null;
$current_phone = $u_data['user_phone'] ?? '';

// ดึงข้อมูลคำสั่งซื้อและ JOIN เอาชื่อกับรูปดอกไม้จากตาราง product
if (!empty($current_phone)) {
    $clean_phone = mysqli_real_escape_string($conn, $current_phone);
    $sql = "SELECT o.*, p.product_name, p.product_image 
            FROM orders o 
            LEFT JOIN products p ON o.product_id = p.product_id 
            WHERE o.phone = '$clean_phone' OR o.user_id = $user_id 
            ORDER BY o.order_id DESC";
} else {
    $sql = "SELECT o.*, p.product_name, p.product_image 
            FROM orders o 
            LEFT JOIN products p ON o.product_id = p.product_id 
            ORDER BY o.order_id DESC";
}
$orders_result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ประวัติคำสั่งซื้อของฉัน | TERMTEM+</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Bai Jamjuree', sans-serif; }
    body { background-color: #fffdf8; color: #333; padding: 25px 15px; }
    .container { max-width: 850px; margin: 0 auto; }
    header {
      display: flex; justify-content: space-between; align-items: center;
      padding-bottom: 15px; border-bottom: 2px solid #8a4b0f; margin-bottom: 25px;
    }
    .logo { font-size: 20px; font-weight: bold; color: #8a4b0f; text-decoration: none; }
    .nav-links a { color: #8a4b0f; text-decoration: none; font-weight: 600; margin-left: 15px; }
    .page-title { color: #8a4b0f; font-size: 24px; font-weight: 700; margin-bottom: 20px; }
    
    .order-card {
      background: #fff;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
      padding: 22px;
      margin-bottom: 22px;
      box-shadow: 0 4px 12px rgba(138, 75, 15, 0.05);
    }
    .order-head {
      display: flex; justify-content: space-between; align-items: center;
      border-bottom: 1px solid #f2ede7; padding-bottom: 12px; margin-bottom: 15px;
    }
    .order-num { font-weight: 700; color: #8a4b0f; font-size: 16px; }
    .order-date { font-size: 13px; color: #888; }
    .badge {
      padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;
      background: #fff5c8; color: #8a4b0f; border: 1px solid #8a4b0f;
    }
    .item-row {
      display: flex; align-items: center; gap: 15px; padding: 10px 0; border-bottom: 1px dashed #f5efe9;
    }
    .item-img {
      width: 75px; height: 75px; object-fit: cover; border-radius: 6px; background: #fff5c8;
    }
    .item-info { flex: 1; font-size: 14px; }
    .item-price { font-weight: bold; color: #8a4b0f; font-size: 16px; }

    .order-summary {
      display: flex; justify-content: space-between; align-items: flex-start;
      margin-top: 15px; font-size: 14px; color: #555; flex-wrap: wrap; gap: 15px;
    }
    .shipping-info { max-width: 520px; line-height: 1.6; font-size: 13px; }
    .total-box { text-align: right; }
    .grand-total { font-size: 19px; font-weight: bold; color: #8a4b0f; }
  </style>
</head>
<body>

  <div class="container">
    <header>
      <a href="index.php" class="logo">🌸 TERMTEM+</a>
      <div class="nav-links">
        <a href="index.php">หน้าร้านค้า</a>
        <a href="cart.php">🛒 รถเข็น</a>
        <a href="profile.php">👤 ข้อมูลส่วนตัว</a>
      </div>
    </header>

    <h1 class="page-title">📦 ประวัติคำสั่งซื้อของฉัน</h1>

    <?php if ($orders_result && mysqli_num_rows($orders_result) > 0): ?>
      <?php while ($ord = mysqli_fetch_assoc($orders_result)): ?>
        <div class="order-card">
          <div class="order-head">
            <div>
              <span class="order-num">รหัสคำสั่งซื้อ #<?= $ord['order_id'] ?></span>
              <span class="order-date"> | สั่งซื้อเมื่อ: <?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?> น.</span>
            </div>
            <span class="badge"><?= htmlspecialchars($ord['order_status']) ?></span>
          </div>

          <!-- แสดงสินค้าที่สั่งซื้อตามตาราง orders และ product -->
          <div class="item-row">
            <img src="<?= htmlspecialchars($ord['product_image'] ?? '') ?>" class="item-img" onerror="this.src='https://placehold.co/100x100?text=Flower';">
            <div class="item-info">
              <strong style="font-size: 16px;"><?= htmlspecialchars($ord['product_name'] ?? 'ช่อดอกไม้') ?></strong>
              <div style="color: #777; margin-top: 4px;">
                จำนวน: <?= $ord['quantity'] ?> ช่อ (฿<?= number_format($ord['unit_price']) ?> / ช่อ)
              </div>
            </div>
            <div class="item-price">
              ฿<?= number_format($ord['unit_price'] * $ord['quantity']) ?>
            </div>
          </div>

          <!-- ข้อมูลจัดส่งและยอดเงินสุทธิ -->
          <div class="order-summary">
            <div class="shipping-info">
              <strong>ผู้รับ:</strong> <?= htmlspecialchars($ord['recipient']) ?> (<?= htmlspecialchars($ord['phone']) ?>)<br>
              <strong>จัดส่งที่:</strong> <?= htmlspecialchars($ord['address']) ?><br>
              <strong>กำหนดส่ง:</strong> <?= htmlspecialchars($ord['delivery_date'] ?: 'ตามระบุ') ?> (ช่วงเวลา: <?= htmlspecialchars($ord['delivery_time']) ?>)
              <?php if (!empty($ord['card_message'])): ?>
                <br><strong>ข้อความบนการ์ด:</strong> "<?= htmlspecialchars($ord['card_message']) ?>"
              <?php endif; ?>
              <br><strong>วิธีชำระเงิน:</strong> <?= htmlspecialchars($ord['payment_method']) ?>
            </div>
            <div class="total-box">
              <div>ค่าจัดส่ง: ฿80</div>
              <div style="margin-top: 5px;">
                ยอดชำระสุทธิ: <span class="grand-total">฿<?= number_format($ord['total_amount']) ?></span>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div style="background:#fff; border:1px solid #ebd9c8; border-radius:8px; padding:45px; text-align:center; color:#888;">
        <p style="font-size:16px; margin-bottom:15px;">ยังไม่มีประวัติคำสั่งซื้อ</p>
        <a href="index.php" style="color:#8a4b0f; font-weight:bold; text-decoration:underline;">เลือกซื้อช่อดอกไม้</a>
      </div>
    <?php endif; ?>

  </div>

</body>
</html>