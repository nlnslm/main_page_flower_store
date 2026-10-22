<?php
session_start();
require_once 'dbconnect_flower.php';

// ตรวจสอบสิทธิ์ Admin (หากจำลองทดสอบ สามารถตั้ง $_SESSION['user_id'] = 1 ไว้ได้)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // ID ของ Admin สำหรับทดสอบ
}

$user_id = (int)$_SESSION['user_id'];
$chk = mysqli_query($conn, "SELECT role FROM users WHERE user_id = $user_id");
$user_role = mysqli_fetch_assoc($chk)['role'] ?? '';

if ($user_role !== 'admin') {
    die("<h2 style='text-align:center; margin-top:50px; font-family:sans-serif; color:#c94a4a;'>⛔ คุณไม่มีสิทธิ์เข้าถึงหน้านี้! (เฉพาะ Admin เท่านั้น)<br><br><a href='index.php'>กลับหน้าหลัก</a></h2>");
}

$alert = "";

// -------------------------------------------------------------
// จัดการคำขอ เช่น ลบสินค้า (Delete Product)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_product') {
        $pid = (int)$_POST['product_id'];
        $del = mysqli_query($conn, "DELETE FROM products WHERE product_id = $pid");
        if ($del) {
            $alert = "ลบสินค้าเรียบร้อยแล้ว";
        }
    }
}

// -------------------------------------------------------------
// ดึงข้อมูลสถิติภาพรวม
// -------------------------------------------------------------
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products"))['cnt'];
$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users"))['cnt'];

// ดึงรายการสินค้าทั้งหมด
$products = mysqli_query($conn, "SELECT * FROM product ORDER BY product_id DESC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | TERMTEM+</title>
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
      max-width: 1200px;
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
      font-size: 22px;
      font-weight: bold;
      color: #8a4b0f;
      text-decoration: none;
    }
    .nav-links a {
      color: #8a4b0f;
      text-decoration: none;
      font-weight: 600;
      margin-left: 20px;
    }

    /* กล่องสรุปภาพรวม (Stat Cards) */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: #fff;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(138, 75, 15, 0.05);
    }
    .stat-title {
      font-size: 14px;
      color: #777;
      margin-bottom: 5px;
    }
    .stat-number {
      font-size: 28px;
      font-weight: bold;
      color: #8a4b0f;
    }

    /* การ์ดและตาราง */
    .panel-card {
      background: #fff;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 30px;
    }
    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f2ede7;
    }
    .panel-header h2 {
      font-size: 18px;
      color: #8a4b0f;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }
    th, td {
      padding: 12px 14px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }
    th {
      background-color: #fff9f0;
      color: #8a4b0f;
      font-weight: 600;
    }
    .prod-thumb {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 4px;
    }
    .btn-delete {
      background: #dc3545;
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 13px;
      cursor: pointer;
    }
    .btn-action {
      background: #8a4b0f;
      color: #fff;
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 4px;
      font-size: 14px;
      font-weight: 600;
      display: inline-block;
    }
  </style>
</head>
<body>

  <div class="container">
    <header>
      <a href="index.php" class="logo">🌸 TERMTEM+ [Admin Center]</a>
      <div class="nav-links">
        <a href="index.php">🏪 ไปหน้าร้าน</a>
        <a href="admin_users.php">👥 จัดการสมาชิก</a>
        <a href="profile.php">👤 บัญชีของฉัน</a>
      </div>
    </header>

    <?php if ($alert): ?>
      <div style="background:#d4edda; color:#155724; padding:12px; border-radius:4px; margin-bottom:20px;">
        <?= htmlspecialchars($alert) ?>
      </div>
    <?php endif; ?>

    <!-- สถิติเบื้องต้น -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-title">จำนวนสินค้าทั้งหมด</div>
        <div class="stat-number"><?= $total_products ?> รายการ</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">จำนวนสมาชิกทั้งหมด</div>
        <div class="stat-number"><?= $total_users ?> คน</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">การเข้าถึงด่วน</div>
        <div style="margin-top: 8px;">
          <a href="admin_users.php" class="btn-action" style="font-size: 13px; padding: 6px 12px;">จัดการข้อมูลผู้ใช้</a>
        </div>
      </div>
    </div>

    <!-- ตารางจัดการสินค้า -->
    <div class="panel-card">
      <div class="panel-header">
        <h2>💐 จัดการสินค้าในร้าน</h2>
        <a href="index.php" class="btn-action">เพิ่มสินค้าใหม่ที่หน้าร้าน</a>
      </div>

      <table>
        <thead>
          <tr>
            <th>รูปภาพ</th>
            <th>ชื่อสินค้า</th>
            <th>ราคา</th>
            <th>เหมาะสำหรับโอกาส</th>
            <th>รายละเอียด</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <tr>
              <td>
                <img class="prod-thumb" src="<?= htmlspecialchars($p['product_image']) ?>" onerror="this.src='https://placehold.co/100x100?text=Flower'">
              </td>
              <td><strong><?= htmlspecialchars($p['product_name']) ?></strong></td>
              <td style="color:#8a4b0f; font-weight:600;">฿<?= number_format($p['product_price']) ?></td>
              <td><?= htmlspecialchars($p['product_occasion']) ?></td>
              <td style="color:#666; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?= htmlspecialchars($p['product_description'] ?: '-') ?>
              </td>
              <td>
                <form method="POST" onsubmit="return confirm('ยืนยันที่จะลบสินค้านี้ใช่หรือไม่?');">
                  <input type="hidden" name="action" value="delete_product">
                  <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                  <button type="submit" class="btn-delete">ลบ</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>