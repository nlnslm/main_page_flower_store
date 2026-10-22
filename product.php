<?php
session_start();
require_once 'dbconnect_flower.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT product_id, product_name, product_price, product_image, product_occasion, product_description FROM products WHERE product_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("<h2 style='text-align:center; margin-top:50px; font-family:sans-serif;'>ไม่พบข้อมูลสินค้ารายการนี้ <br><br><a href='index.php'>กลับหน้าหลัก</a></h2>");
}

// นับจำนวนสินค้าในรถเข็น
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['product_name']) ?> | TERMTEM+</title>
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
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      padding: 25px;
      border-radius: 8px;
      border: 1px solid #ebd9c8;
      box-shadow: 0 4px 15px rgba(138, 75, 15, 0.08);
    }
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .back-btn {
      text-decoration: none;
      color: #8a4b0f;
      font-weight: 600;
    }
    .cart-link {
      text-decoration: none;
      color: #8a4b0f;
      font-weight: 600;
    }
    .detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }
    @media (max-width: 650px) {
      .detail-grid { grid-template-columns: 1fr; }
    }
    .detail-img {
      width: 100%;
      height: 420px;
      object-fit: cover;
      border-radius: 6px;
      background-color: #fff5c8;
    }
    .detail-info h1 {
      color: #8a4b0f;
      font-size: 26px;
      margin-bottom: 10px;
    }
    .price-tag {
      font-size: 24px;
      font-weight: bold;
      color: #8a4b0f;
      margin-bottom: 15px;
    }
    .badge {
      display: inline-block;
      background: #fff5c8;
      color: #8a4b0f;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 13px;
      margin-bottom: 15px;
    }
    .desc {
      line-height: 1.7;
      color: #555;
      margin-bottom: 25px;
    }
    .qty-select {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    .qty-select input {
      width: 60px;
      padding: 8px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      text-align: center;
      font-size: 16px;
    }
    .order-btn {
      width: 100%;
      padding: 12px;
      background-color: #8a4b0f;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s;
    }
    .order-btn:hover {
      background-color: #6d3a0a;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-bar">
      <a href="index.php" class="back-btn">← กลับไปหน้ารวมสินค้า</a>
      <a href="cart.php" class="cart-link">🛒 รถเข็น (<?= $cart_count ?>)</a>
    </div>

    <div class="detail-grid">
      <div>
        <img class="detail-img" src="<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" onerror="this.src='https://placehold.co/400x500?text=TERMTEM+'">
      </div>
      <div class="detail-info">
        <h1><?= htmlspecialchars($product['product_name']) ?></h1>
        <div class="price-tag">฿<?= number_format($product['product_price']) ?></div>
        <div class="badge">โอกาสที่แนะนำ: <?= htmlspecialchars($product['product_occasion']) ?></div>
        
        <p class="desc">
          <?= !empty($product['product_description']) ? nl2br(htmlspecialchars($product['product_description'])) : 'ไม่มีคำอธิบายเพิ่มเติมสำหรับสินค้ารายการนี้' ?>
        </p>

        <!-- ฟอร์มเพิ่มสินค้าลงรถเข็น -->
        <form action="cart.php" method="POST">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
          
          <div class="qty-select">
            <label for="quantity">จำนวน:</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="99">
          </div>

          <button type="submit" class="order-btn">🛒 เพิ่มลงรถเข็นและดูรายการ</button>
          <br><br>
          <button type="submit" class="order-btn" formaction="order.php">สั่งซื้อทันที</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>