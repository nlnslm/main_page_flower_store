<?php
session_start();
require_once 'dbconnect_flower.php';

// สมมติจำลอง User ID ปัจจุบัน (หากมีระบบล็อกอิน ให้ใช้ $_SESSION['user_id'])
if (!isset($_SESSION['user_id'])) {
    // กำหนดค่าเริ่มต้นสำหรับทดสอบ เช่น ID = 1 (หรือ ID ของลูกค้าที่มีอยู่ในตาราง users)
    $_SESSION['user_id'] = 1;
}

$user_id = (int)$_SESSION['user_id'];
$alert_msg = "";
$alert_type = "";

// -------------------------------------------------------------
// จัดการการบันทึกข้อมูลเมื่อส่งฟอร์ม (POST)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $subdistrict = trim($_POST['subdistrict'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');

    if (empty($fullname) || empty($email) || empty($phone)) {
        $alert_msg = "กรุณากรอกชื่อ-นามสกุล, อีเมล และเบอร์โทรศัพท์";
        $alert_type = "error";
    } else {
        $update_sql = "UPDATE users SET 
                        fullname = ?, 
                        email = ?, 
                        phone = ?, 
                        address = ?, 
                        subdistrict = ?, 
                        district = ?, 
                        province = ?, 
                        zipcode = ? 
                       WHERE user_id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $fullname, $email, $phone, $address, $subdistrict, $district, $province, $zipcode, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $alert_msg = "บันทึกข้อมูลส่วนตัวและที่อยู่จัดส่งเรียบร้อยแล้ว!";
            $alert_type = "success";
        } else {
            $alert_msg = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
            $alert_type = "error";
        }
        mysqli_stmt_close($stmt);
    }
}

// -------------------------------------------------------------
// ดึงข้อมูลผู้ใช้งานปัจจุบันขึ้นมาแสดงในฟอร์ม
// -------------------------------------------------------------
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    die("<h2 style='text-align:center; margin-top:50px; font-family:sans-serif;'>ไม่พบข้อมูลผู้ใช้งาน</h2>");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข้อมูลส่วนตัวและที่อยู่จัดส่ง | TERMTEM+</title>
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
      max-width: 800px;
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
      font-weight: 600;
    }

    /* กล่องข้อความแจ้งเตือน */
    .alert {
      padding: 12px 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 15px;
    }
    .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* ฟอร์ม */
    .profile-card {
      background: #fff;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
      padding: 25px 30px;
      box-shadow: 0 4px 15px rgba(138, 75, 15, 0.06);
    }
    .section-title {
      font-size: 18px;
      font-weight: 600;
      color: #8a4b0f;
      margin-bottom: 15px;
      padding-bottom: 8px;
      border-bottom: 1px solid #f2e4d8;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 20px;
    }
    @media (max-width: 600px) {
      .form-grid { grid-template-columns: 1fr; }
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
      margin-bottom: 12px;
    }
    .form-group.full-width {
      grid-column: 1 / -1;
    }
    .form-group label {
      font-size: 14px;
      font-weight: 500;
      color: #555;
    }
    .form-group input, .form-group textarea {
      padding: 10px 12px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      font-size: 15px;
      font-family: inherit;
      background: #fff;
    }
    .form-group input:focus, .form-group textarea:focus {
      outline: none;
      box-shadow: 0 0 0 2px rgba(138, 75, 15, 0.2);
    }
    .btn-save {
      width: 100%;
      padding: 12px;
      background-color: #8a4b0f;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      margin-top: 10px;
    }
    .btn-save:hover {
      background-color: #6d3a0a;
    }
  </style>
</head>
<body>

  <div class="container">
    <header>
      <a href="index.php" class="logo">🌸 TERMTEM+</a>
      <a href="index.php" class="back-link">← กลับสู่หน้าร้าน</a>
    </header>

    <?php if ($alert_msg): ?>
      <div class="alert <?= $alert_type ?>"><?= htmlspecialchars($alert_msg) ?></div>
    <?php endif; ?>

    <div class="profile-card">
      <form method="POST">
        <!-- 1. ข้อมูลติดต่อทั่วไป -->
        <div class="section-title">👤 ข้อมูลส่วนตัวทั่วไป</div>
        <div class="form-grid">
          <div class="form-group full-width">
            <label>ชื่อบัญชีผู้ใช้ (Username)</label>
            <input type="text" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled style="background: #f7f7f7; color: #888;">
          </div>
          <div class="form-group">
            <label>ชื่อ - นามสกุล *</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required placeholder="เช่น สมชาย ใจดี">
          </div>
          <div class="form-group">
            <label>เบอร์โทรศัพท์สำหรับจัดส่ง *</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required placeholder="08x-xxx-xxxx">
          </div>
          <div class="form-group full-width">
            <label>อีเมล *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required placeholder="example@email.com">
          </div>
        </div>

        <!-- 2. ที่อยู่สำหรับจัดส่งดอกไม้ -->
        <div class="section-title">📍 ที่อยู่จัดส่งประจำ (ไม่ต้องกรอกซ้ำเมื่อสั่งซื้อ)</div>
        <div class="form-group">
          <label>บ้านเลขที่, หมู่, ซอย, ถนน หรือชื่ออาคาร/สถานที่</label>
          <textarea name="address" rows="3" placeholder="เช่น 123/45 หมู่ 6 ถ.กาญจนวนิช"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>ตำบล / แขวง</label>
            <input type="text" name="subdistrict" value="<?= htmlspecialchars($user['subdistrict'] ?? '') ?>" placeholder="เช่น คอหงส์">
          </div>
          <div class="form-group">
            <label>อำเภอ / เขต</label>
            <input type="text" name="district" value="<?= htmlspecialchars($user['district'] ?? '') ?>" placeholder="เช่น หาดใหญ่">
          </div>
          <div class="form-group">
            <label>จังหวัด</label>
            <input type="text" name="province" value="<?= htmlspecialchars($user['province'] ?? '') ?>" placeholder="เช่น สงขลา">
          </div>
          <div class="form-group">
            <label>รหัสไปรษณีย์</label>
            <input type="text" name="zipcode" maxlength="5" value="<?= htmlspecialchars($user['zipcode'] ?? '') ?>" placeholder="เช่น 90110">
          </div>
        </div>

        <button type="submit" class="btn-save">บันทึกข้อมูลและที่อยู่จัดส่ง</button>
      </form>
    </div>
  </div>

</body>
</html>