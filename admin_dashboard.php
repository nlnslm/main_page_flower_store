<?php
session_start();
require_once 'dbconnect_flower.php'; // ตรวจสอบชื่อไฟล์เชื่อมต่อฐานข้อมูลของคุณ

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ (Admin)
$is_admin = false;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $is_admin = true;
} elseif (isset($_SESSION['session_admin_name'])) {
    $is_admin = true;
}

if (!$is_admin) {
    die("<h2 style='text-align:center; margin-top:50px; font-family:sans-serif; color:#c94a4a;'>⛔ เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถเข้าถึงหน้านี้ได้<br><br><a href='login.php'>เข้าสู่ระบบ</a> | <a href='index.php'>กลับหน้าร้าน</a></h2>");
}

$alert_msg = "";
$alert_type = "";
$active_tab = $_GET['tab'] ?? 'products';

// -------------------------------------------------------------
// 1. จัดการคำสั่งของ "สินค้า" (เพิ่ม / ลบ)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // 1.1 เพิ่มสินค้าใหม่
    if ($_POST['action'] === 'add_product') {
        $active_tab = 'products';
        $name = trim($_POST['product_name'] ?? '');
        $price = (float)($_POST['product_price'] ?? 0);
        $image = trim($_POST['product_image'] ?? '');
        $description = trim($_POST['product_description'] ?? '');
        $occasions = isset($_POST['occasions']) && is_array($_POST['occasions']) ? implode(',', $_POST['occasions']) : '';

        if (!empty($name) && $price > 0 && !empty($image)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (product_name, product_price, product_image, product_occasion, product_description) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sdsss", $name, $price, $image, $occasions, $description);

            if (mysqli_stmt_execute($stmt)) {
                $alert_msg = "เพิ่มสินค้าใหม่เรียบร้อยแล้ว!";
                $alert_type = "success";
            } else {
                $alert_msg = "เกิดข้อผิดพลาดในการบันทึกสินค้า: " . mysqli_stmt_error($stmt);
                $alert_type = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $alert_msg = "กรุณากรอกชื่อสินค้า, ราคา และ URL รูปภาพให้ครบถ้วน";
            $alert_type = "error";
        }
    }

    // 1.2 ลบสินค้า
    if ($_POST['action'] === 'delete_product') {
        $active_tab = 'products';
        $pid = (int)$_POST['product_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $pid);

        if (mysqli_stmt_execute($stmt)) {
            $alert_msg = "ลบสินค้าเรียบร้อยแล้ว!";
            $alert_type = "success";
        } else {
            $alert_msg = "ไม่สามารถลบสินค้าได้: " . mysqli_stmt_error($stmt);
            $alert_type = "error";
        }
        mysqli_stmt_close($stmt);
    }

    // -------------------------------------------------------------
    // 2. จัดการคำสั่งของ "ผู้ใช้งาน" (แก้ไข / ลบ)
    // -------------------------------------------------------------

    // 2.1 แก้ไขข้อมูลผู้ใช้
    if ($_POST['action'] === 'edit_user') {
        $active_tab = 'users';
        $uid        = (int)$_POST['user_id'];
        $u_name     = trim($_POST['user_name'] ?? '');
        $u_lastname = trim($_POST['user_lastname'] ?? '');
        $u_email    = trim($_POST['user_email'] ?? '');
        $u_phone    = trim($_POST['user_phone'] ?? '');
        $address    = trim($_POST['address'] ?? '');
        $subdist    = trim($_POST['subdistrict'] ?? '');
        $dist       = trim($_POST['district'] ?? '');
        $prov       = trim($_POST['province'] ?? '');
        $zip        = trim($_POST['zipcode'] ?? '');

        if (!empty($u_name) && !empty($u_lastname) && !empty($u_email)) {
            $sql = "UPDATE users SET 
                        user_name = ?, 
                        user_lastname = ?, 
                        user_email = ?, 
                        user_phone = ?, 
                        address = ?, 
                        subdistrict = ?, 
                        district = ?, 
                        province = ?, 
                        zipcode = ? 
                    WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssi", $u_name, $u_lastname, $u_email, $u_phone, $address, $subdist, $dist, $prov, $zip, $uid);

            if (mysqli_stmt_execute($stmt)) {
                $alert_msg = "อัปเดตข้อมูลผู้ใช้รหัส #$uid สำเร็จแล้ว!";
                $alert_type = "success";
            } else {
                $alert_msg = "เกิดข้อผิดพลาดในการอัปเดตข้อมูลผู้ใช้: " . mysqli_stmt_error($stmt);
                $alert_type = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $alert_msg = "กรุณากรอกชื่อ, นามสกุล และอีเมลให้ครบถ้วน";
            $alert_type = "error";
        }
    }

    // 2.2 ลบผู้ใช้
    if ($_POST['action'] === 'delete_user') {
        $active_tab = 'users';
        $uid = (int)$_POST['user_id'];

        // ป้องกันแอดมินเผลอลบบัญชีตัวเอง
        if (isset($_SESSION['user_id']) && $uid === (int)$_SESSION['user_id']) {
            $alert_msg = "ไม่สามารถลบบัญชีที่กำลังล็อกอินใช้งานอยู่ได้!";
            $alert_type = "error";
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $uid);

            if (mysqli_stmt_execute($stmt)) {
                $alert_msg = "ลบข้อมูลผู้ใช้งานรหัส #$uid เรียบร้อยแล้ว!";
                $alert_type = "success";
            } else {
                $alert_msg = "เกิดข้อผิดพลาดในการลบผู้ใช้งาน: " . mysqli_stmt_error($stmt);
                $alert_type = "error";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// -------------------------------------------------------------
// 3. ดึงข้อมูลแสดงผล
// -------------------------------------------------------------
// สถิติ
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products"))['cnt'];
$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users"))['cnt'];

// รายการสินค้า
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_id DESC");

// ค้นหาและรายการผู้ใช้งาน
$user_search = trim($_GET['user_search'] ?? '');
if (!empty($user_search)) {
    $search_param = "%{$user_search}%";
    $u_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_name LIKE ? OR user_lastname LIKE ? OR user_email LIKE ? OR user_phone LIKE ? ORDER BY user_id DESC");
    mysqli_stmt_bind_param($u_stmt, "ssss", $search_param, $search_param, $search_param, $search_param);
    mysqli_stmt_execute($u_stmt);
    $users_result = mysqli_stmt_get_result($u_stmt);
} else {
    $users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
}
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
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Bai Jamjuree', sans-serif; }
    body { background-color: #fffdf8; color: #333; padding: 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
    header {
      display: flex; justify-content: space-between; align-items: center;
      padding-bottom: 15px; border-bottom: 2px solid #8a4b0f; margin-bottom: 25px;
    }
    .logo { font-size: 22px; font-weight: bold; color: #8a4b0f; text-decoration: none; }
    .nav-links a { color: #8a4b0f; text-decoration: none; font-weight: 600; margin-left: 20px; }

    /* Alert */
    .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 15px; }
    .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* Stat Cards */
    .stat-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px; margin-bottom: 25px;
    }
    .stat-card {
      background: #fff; border: 1px solid #ebd9c8; border-radius: 8px;
      padding: 20px; box-shadow: 0 4px 10px rgba(138, 75, 15, 0.05);
    }
    .stat-title { font-size: 14px; color: #777; margin-bottom: 5px; }
    .stat-number { font-size: 28px; font-weight: bold; color: #8a4b0f; }

    /* แท็บสลับหน้า */
    .tab-bar {
      display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #ebd9c8;
    }
    .tab-button {
      padding: 10px 22px; background: none; border: none; font-family: inherit;
      font-size: 16px; font-weight: 600; color: #777; cursor: pointer;
      border-bottom: 3px solid transparent; margin-bottom: -2px; transition: 0.2s;
    }
    .tab-button.active {
      color: #8a4b0f; border-bottom-color: #8a4b0f;
    }

    /* กล่องข้อมูลหลัก */
    .panel-card {
      background: #fff; border: 1px solid #ebd9c8; border-radius: 8px;
      padding: 24px; box-shadow: 0 4px 15px rgba(138, 75, 15, 0.06);
    }
    .panel-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #f2ede7;
      flex-wrap: wrap; gap: 10px;
    }
    .panel-header h2 { font-size: 20px; color: #8a4b0f; }

    /* ปุ่ม */
    .btn-main {
      background: #8a4b0f; color: #fff; border: none; padding: 9px 16px;
      border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s;
    }
    .btn-main:hover { background: #6d3a0a; }
    .btn-edit {
      background: #ffc107; color: #212529; border: none; padding: 6px 12px;
      border-radius: 4px; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .btn-delete {
      background: #dc3545; color: #fff; border: none; padding: 6px 12px;
      border-radius: 4px; font-size: 13px; cursor: pointer;
    }
    .btn-delete:hover { background: #bd2130; }

    /* ตาราง */
    .table-container { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th, td { padding: 12px 14px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
    th { background-color: #fff9f0; color: #8a4b0f; font-weight: 600; }
    .prod-thumb { width: 55px; height: 55px; object-fit: cover; border-radius: 4px; background-color: #fff5c8; }

    /* Form ค้นหา */
    .search-box { display: flex; gap: 8px; }
    .search-input {
      padding: 8px 12px; border: 1px solid #8a4b0f; border-radius: 4px;
      font-family: inherit; font-size: 14px; width: 260px;
    }

    /* Modal ป็อปอัป */
    .modal-overlay {
      display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center;
      z-index: 1000; padding: 15px;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
      background: #fffdf8; border: 2px solid #8a4b0f; border-radius: 8px;
      max-width: 580px; width: 100%; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.25);
      max-height: 90vh; overflow-y: auto;
    }
    .modal-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #ebd9c8;
    }
    .modal-header h3 { color: #8a4b0f; font-size: 20px; }
    .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #888; }
    .form-group { margin-bottom: 12px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #444; }
    .form-group input, .form-group textarea {
      width: 100%; padding: 8px 12px; border: 1px solid #8a4b0f; border-radius: 4px;
      font-size: 14px; font-family: inherit; background: #fff;
    }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .checkbox-group { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 6px; }
    .checkbox-group label { font-weight: normal; display: flex; align-items: center; gap: 5px; cursor: pointer; }
  </style>
</head>
<body>

  <div class="container">
    <header>
        <a href="admin_dashboard.php" class="logo">🌸 TERMTEM+ [Admin Dashboard]</a>
        <div class="nav-links" style="display: flex; align-items: center; gap: 15px;">
            <a href="index.php">หน้าร้านค้า</a>
            <a href="profile.php">👤 บัญชีของฉัน</a>
    
            <!-- ปุ่มออกจากระบบ -->
            <form action="logout.php" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('ยืนยันออกจากระบบใช่หรือไม่?');">
                <button type="submit" class="btn-logout">
                    ออกจากระบบ
                </button>
            </form>
        </div>
    </header>

    <?php if ($alert_msg): ?>
      <div class="alert <?= $alert_type ?>"><?= htmlspecialchars($alert_msg) ?></div>
    <?php endif; ?>

    <!-- สถิติภาพรวม -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-title">จำนวนสินค้าทั้งหมด</div>
        <div class="stat-number"><?= $total_products ?> รายการ</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">จำนวนสมาชิกผู้ใช้งาน</div>
        <div class="stat-number"><?= $total_users ?> คน</div>
      </div>
      <div class="stat-card">
        <div class="stat-title">สถานะระบบ</div>
        <div class="stat-number" style="font-size: 20px; color:#28a745; margin-top:6px;">พร้อมใช้งาน (Online)</div>
      </div>
    </div>

    <!-- แถบสลับแท็บเมนู -->
    <div class="tab-bar">
      <button class="tab-button <?= $active_tab === 'products' ? 'active' : '' ?>" onclick="switchTab('products')">
        💐 จัดการสินค้า (Products)
      </button>
      <button class="tab-button <?= $active_tab === 'users' ? 'active' : '' ?>" onclick="switchTab('users')">
        👥 จัดการผู้ใช้ (Users)
      </button>
    </div>

    <!-- ========================================== -->
    <!-- แท็บ 1: จัดการสินค้า -->
    <!-- ========================================== -->
    <div id="tab-products" class="panel-card" style="display: <?= $active_tab === 'products' ? 'block' : 'none' ?>;">
      <div class="panel-header">
        <h2>💐 รายการสินค้าทั้งหมดในร้าน</h2>
        <button class="btn-main" onclick="openAddModal()">เพิ่มช่อดอกไม้</button>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>รูป</th>
              <th>ชื่อช่อดอกไม้</th>
              <th>ราคา</th>
              <th>โอกาสที่แนะนำ</th>
              <th>คำอธิบาย</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($products) > 0): ?>
              <?php while ($p = mysqli_fetch_assoc($products)): ?>
                <tr>
                  <td>
                    <img class="prod-thumb" src="<?= htmlspecialchars($p['product_image']) ?>" onerror="this.src='https://placehold.co/100x100?text=TERMTEM+'">
                  </td>
                  <td><strong><?= htmlspecialchars($p['product_name']) ?></strong></td>
                  <td style="color:#8a4b0f; font-weight:600;">฿<?= number_format($p['product_price']) ?></td>
                  <td><?= htmlspecialchars($p['product_occasion']) ?></td>
                  <td style="color:#666; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($p['product_description'] ?: '-') ?>
                  </td>
                  <td>
                    <form method="POST" onsubmit="return confirm('คุณต้องการลบสินค้านี้ใช่หรือไม่?');" style="margin:0;">
                      <input type="hidden" name="action" value="delete_product">
                      <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                      <button type="submit" class="btn-delete">ลบ</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" style="text-align:center; padding: 25px; color:#888;">ยังไม่มีสินค้าในระบบ</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- แท็บ 2: จัดการผู้ใช้ -->
    <!-- ========================================== -->
    <div id="tab-users" class="panel-card" style="display: <?= $active_tab === 'users' ? 'block' : 'none' ?>;">
      <div class="panel-header">
        <h2>👥 รายชื่อสมาชิกผู้ใช้งานทั้งหมด</h2>
        <form method="GET" class="search-box">
          <input type="hidden" name="tab" value="users">
          <input type="text" name="user_search" class="search-input" placeholder="ค้นหาชื่อ, อีเมล, เบอร์โทร..." value="<?= htmlspecialchars($user_search) ?>">
          <button type="submit" class="btn-main">ค้นหา</button>
          <?php if (!empty($user_search)): ?>
            <a href="admin_dashboard.php?tab=users" style="align-self:center; font-size:13px; color:#8a4b0f; text-decoration:none;">ล้างค้นหา</a>
          <?php endif; ?>
        </form>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>ชื่อ - นามสกุล</th>
              <th>อีเมล</th>
              <th>เบอร์โทร</th>
              <th>ที่อยู่จัดส่งประจำ</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($users_result) > 0): ?>
              <?php while ($u = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                  <td>#<?= $u['user_id'] ?></td>
                  <td><strong><?= htmlspecialchars($u['user_name'] . ' ' . $u['user_lastname']) ?></strong></td>
                  <td><?= htmlspecialchars($u['user_email']) ?></td>
                  <td><?= htmlspecialchars($u['user_phone'] ?: '-') ?></td>
                  <td style="color:#555; max-width: 280px; font-size:13px;">
                    <?php if (!empty($u['address'])): ?>
                      <?= htmlspecialchars($u['address']) ?> 
                      ต.<?= htmlspecialchars($u['subdistrict'] ?? '') ?> 
                      อ.<?= htmlspecialchars($u['district'] ?? '') ?> 
                      จ.<?= htmlspecialchars($u['province'] ?? '') ?> 
                      <?= htmlspecialchars($u['zipcode'] ?? '') ?>
                    <?php else: ?>
                      <span style="color:#aaa; font-style:italic;">ยังไม่ได้ระบุที่อยู่</span>
                    <?php endif; ?>
                  </td>
                  <td style="white-space: nowrap;">
                    <!-- ฟอร์มลบผู้ใช้ -->
                    <form method="POST" style="display:inline;" onsubmit="return confirm('ยืนยันลบผู้ใช้งานรหัส #<?= $u['user_id'] ?> ใช่หรือไม่?');">
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                      <button type="submit" class="btn-delete">ลบ</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" style="text-align:center; padding: 25px; color:#888;">ไม่พบข้อมูลผู้ใช้งานที่ค้นหา</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- ========================================== -->
  <!-- Modal 1: เพิ่มสินค้าใหม่ -->
  <!-- ========================================== -->
  <div id="addProductModal" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-header">
        <h3>เพิ่มช่อดอกไม้ใหม่</h3>
        <button class="modal-close" onclick="closeModal('addProductModal')">&times;</button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="add_product">

        <div class="form-group">
          <label>ชื่อช่อดอกไม้ (product_name) *</label>
          <input type="text" name="product_name" required placeholder="เช่น Sunflower Joy">
        </div>

        <div class="form-group">
          <label>ราคา (product_price) *</label>
          <input type="number" step="0.01" name="product_price" required placeholder="เช่น 1500">
        </div>

        <div class="form-group">
          <label>ลิงก์รูปภาพ (product_image) *</label>
          <input type="text" name="product_image" required placeholder="https://example.com/flower.jpg">
        </div>

        <div class="form-group">
          <label>เหมาะสำหรับโอกาส (product_occasion):</label>
          <div class="checkbox-group">
            <label><input type="checkbox" name="occasions[]" value="birthday"> วันเกิด</label>
            <label><input type="checkbox" name="occasions[]" value="graduation"> รับปริญญา</label>
            <label><input type="checkbox" name="occasions[]" value="marriage"> แต่งงาน</label>
            <label><input type="checkbox" name="occasions[]" value="congrats"> แสดงความยินดี</label>
          </div>
        </div>

        <div class="form-group">
          <label>รายละเอียดสินค้า (product_description):</label>
          <textarea name="product_description" rows="3" placeholder="ระบุข้อความหรือรายละเอียดของช่อนี้"></textarea>
        </div>

        <button type="submit" class="btn-main" style="width:100%; margin-top:10px; padding:12px;">บันทึกสินค้า</button>
      </form>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- Modal 2: แก้ไขข้อมูลผู้ใช้งาน -->
  <!-- ========================================== -->
  <div id="editUserModal" class="modal-overlay">
    <div class="modal-box">
      <form method="POST">
        <input type="hidden" name="action" value="edit_user">
        <input type="hidden" name="user_id" id="edit_uid">

        <div class="form-grid-2">
          <div class="form-group">
            <label>ชื่อ (user_name) *</label>
            <input type="text" name="user_name" id="edit_name" required>
          </div>
          <div class="form-group">
            <label>นามสกุล (user_lastname) *</label>
            <input type="text" name="user_lastname" id="edit_lastname" required>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label>อีเมล (user_email) *</label>
            <input type="email" name="user_email" id="edit_email" required>
          </div>
          <div class="form-group">
            <label>เบอร์โทรศัพท์ (user_phone)</label>
            <input type="tel" name="user_phone" id="edit_phone">
          </div>
        </div>

        <div class="form-group">
          <label>ที่อยู่ (บ้านเลขที่, หมู่, ถนน, ซอย, อาคาร)</label>
          <textarea name="address" id="edit_address" rows="2"></textarea>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label>ตำบล / แขวง</label>
            <input type="text" name="subdistrict" id="edit_subdistrict">
          </div>
          <div class="form-group">
            <label>อำเภอ / เขต</label>
            <input type="text" name="district" id="edit_district">
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label>จังหวัด</label>
            <input type="text" name="province" id="edit_province">
          </div>
          <div class="form-group">
            <label>รหัสไปรษณีย์</label>
            <input type="text" name="zipcode" id="edit_zipcode" maxlength="5">
          </div>
        </div>

        <button type="submit" class="btn-main" style="width:100%; margin-top:10px; padding:12px;">💾 บันทึกการแก้ไขข้อมูลผู้ใช้</button>
      </form>
    </div>
  </div>

  <script>
    // สลับแท็บ
    function switchTab(tabName) {
      document.getElementById('tab-products').style.display = (tabName === 'products') ? 'block' : 'none';
      document.getElementById('tab-users').style.display = (tabName === 'users') ? 'block' : 'none';

      const buttons = document.querySelectorAll('.tab-button');
      buttons[0].classList.toggle('active', tabName === 'products');
      buttons[1].classList.toggle('active', tabName === 'users');
    }

    // Modal สินค้า
    function openAddModal() {
      document.getElementById('addProductModal').classList.add('active');
    }

    // Modal ผู้ใช้
    function openEditUserModal(user) {
      document.getElementById('display_uid').innerText = '#' + user.user_id;
      document.getElementById('edit_uid').value = user.user_id;
      document.getElementById('edit_name').value = user.user_name || '';
      document.getElementById('edit_lastname').value = user.user_lastname || '';
      document.getElementById('edit_email').value = user.user_email || '';
      document.getElementById('edit_phone').value = user.user_phone || '';
      document.getElementById('edit_address').value = user.address || '';
      document.getElementById('edit_subdistrict').value = user.subdistrict || '';
      document.getElementById('edit_district').value = user.district || '';
      document.getElementById('edit_province').value = user.province || '';
      document.getElementById('edit_zipcode').value = user.zipcode || '';
      document.getElementById('editUserModal').classList.add('active');
    }

    function closeModal(modalId) {
      document.getElementById(modalId).classList.remove('active');
    }

    // คลิกด้านนอก Modal แล้วปิด
    window.onclick = function(e) {
      if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
      }
    }
  </script>
</body>
</html>