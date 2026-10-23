<?php
session_start();
require_once 'dbconnect_flower.php';

// -------------------------------------------------------------
// 1. ตรวจสอบและดึง user_id ของผู้ใช้คนนั้น
// -------------------------------------------------------------
if (isset($_GET['id'])) {
    // กรณีระบุ ID ผ่าน URL เช่น profile.php?id=2
    $user_id = (int)$_GET['id'];
} elseif (isset($_SESSION['user_id'])) {
    // กรณีดึงจากคนที่ล็อกอินอยู่ในระบบ
    $user_id = (int)$_SESSION['user_id'];
} else {
    // หากยังไม่ได้ล็อกอิน ให้ใช้ ID ล่าสุดมาแสดงเพื่อทดสอบ
    $latest_user = mysqli_query($conn, "SELECT user_id FROM users ORDER BY user_id DESC LIMIT 1");
    if ($row = mysqli_fetch_assoc($latest_user)) {
        $user_id = (int)$row['user_id'];
    } else {
        die("<h2 style='text-align:center; margin-top:50px;'>ยังไม่มีข้อมูลผู้ใช้งานในระบบ</h2>");
    }
}

$alert_msg = "";
$alert_type = "";

// -------------------------------------------------------------
// 2. บันทึกการแก้ไขข้อมูลลงในคอลัมน์จริง
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'] ?? '';

    // แก้ไขชื่อ (user_name)
    if ($field === 'user_name') {
        $val = trim($_POST['user_name'] ?? '');
        if (!empty($val)) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET user_name = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $val, $user_id);
            mysqli_stmt_execute($stmt);
            $alert_msg = "แก้ไขชื่อสำเร็จแล้ว!";
            $alert_type = "success";
        }
    }

    // แก้ไขนามสกุล (user_lastname)
    if ($field === 'user_lastname') {
        $val = trim($_POST['user_lastname'] ?? '');
        if (!empty($val)) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET user_lastname = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $val, $user_id);
            mysqli_stmt_execute($stmt);
            $alert_msg = "แก้ไขนามสกุลสำเร็จแล้ว!";
            $alert_type = "success";
        }
    }

    // แก้ไขอีเมล (user_email)
    if ($field === 'user_email') {
        $val = trim($_POST['user_email'] ?? '');
        if (!empty($val)) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET user_email = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $val, $user_id);
            mysqli_stmt_execute($stmt);
            $alert_msg = "แก้ไขอีเมลล์สำเร็จแล้ว!";
            $alert_type = "success";
        }
    }

    // แก้ไขเบอร์โทรศัพท์ (user_phone)
    if ($field === 'user_phone') {
        $val = trim($_POST['user_phone'] ?? '');
        if (!empty($val)) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET user_phone = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $val, $user_id);
            mysqli_stmt_execute($stmt);
            $alert_msg = "แก้ไขเบอร์โทรศัพท์สำเร็จแล้ว!";
            $alert_type = "success";
        }
    }

    // แก้ไขที่อยู่จัดส่ง
    if ($field === 'shipping_address') {
        $addr    = trim($_POST['address'] ?? '');
        $subdist = trim($_POST['subdistrict'] ?? '');
        $dist    = trim($_POST['district'] ?? '');
        $prov    = trim($_POST['province'] ?? '');
        $zip     = trim($_POST['zipcode'] ?? '');

        $stmt = mysqli_prepare($conn, "UPDATE users SET address = ?, subdistrict = ?, district = ?, province = ?, zipcode = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "sssssi", $addr, $subdist, $dist, $prov, $zip, $user_id);
        mysqli_stmt_execute($stmt);
        $alert_msg = "แก้ไขที่อยู่จัดส่งสำเร็จแล้ว!";
        $alert_type = "success";
    }
}

// -------------------------------------------------------------
// 3. ดึงข้อมูลของผู้ใช้ตาม user_id จากฐานข้อมูล
// -------------------------------------------------------------
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("<h2 style='text-align:center; margin-top:50px;'>ไม่พบข้อมูลของผู้ใช้รหัส ID: $user_id</h2>");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข้อมูลส่วนตัว & ที่อยู่ | TERMTEM+</title>
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

    .alert {
      padding: 12px 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 15px;
    }
    .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .card {
      background: #fff;
      border: 1px solid #ebd9c8;
      border-radius: 8px;
      padding: 25px 30px;
      box-shadow: 0 4px 15px rgba(138, 75, 15, 0.06);
    }
    .page-title {
      color: #8a4b0f;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 20px;
    }
    .section-title {
      font-size: 17px;
      font-weight: 600;
      color: #8a4b0f;
      margin: 25px 0 15px;
      padding-bottom: 6px;
      border-bottom: 1px solid #f2e4d8;
    }

    .row-card {
      padding: 14px 0;
      border-bottom: 1px solid #f2ede7;
    }

    /* แถบแสดงข้อมูล: หัวข้อซ้าย | ข้อมูลเดิมแสดงตรงกลางหน้าปุ่ม | ปุ่มแก้ไขขวา */
    .info-display-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 15px;
    }
    .info-label {
      width: 140px;
      font-weight: 600;
      color: #777;
      font-size: 14px;
      flex-shrink: 0;
    }
    .info-value-box {
      flex: 1;
      font-size: 15px;
      color: #222;
      font-weight: 500;
      word-break: break-word;
      padding-right: 15px;
    }
    .empty-text {
      color: #bbb;
      font-style: italic;
    }

    /* ฟอร์มแก้ไข */
    .edit-box {
      display: none;
      background: #fffdf8;
      border: 1px solid #8a4b0f;
      border-radius: 6px;
      padding: 15px;
      margin-top: 10px;
    }
    .edit-box.active {
      display: block;
    }
    .edit-input {
      width: 100%;
      max-width: 400px;
      padding: 8px 12px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      font-size: 14px;
      font-family: inherit;
      background: #fff;
    }
    .edit-textarea {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      font-size: 14px;
      font-family: inherit;
    }
    .address-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-top: 10px;
    }
    @media (max-width: 500px) {
      .address-grid { grid-template-columns: 1fr; }
    }

    .btn-edit-toggle {
      background: none;
      border: 1px solid #8a4b0f;
      color: #8a4b0f;
      padding: 6px 14px;
      border-radius: 4px;
      font-size: 13px;
      cursor: pointer;
      font-weight: 600;
      flex-shrink: 0;
      transition: all 0.2s;
    }
    .btn-edit-toggle:hover {
      background: #8a4b0f;
      color: #fff;
    }
    .btn-save {
      background: #8a4b0f;
      color: #fff;
      border: none;
      padding: 7px 15px;
      border-radius: 4px;
      font-size: 13px;
      cursor: pointer;
      font-weight: 600;
      margin-top: 10px;
    }
    .btn-save:hover {
      background: #6d3a0a;
    }
    .btn-cancel {
      background: #eee;
      color: #555;
      border: none;
      padding: 7px 15px;
      border-radius: 4px;
      font-size: 13px;
      cursor: pointer;
      margin-left: 6px;
      margin-top: 10px;
    }
    
    .btn-logout {
        background-color: #fff0f0;
        color: #c94a4a;
        border: 1px solid #c94a4a;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .btn-logout:hover {
        background-color: #c94a4a;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(201, 74, 74, 0.25);
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

    <div class="card">
      <h1 class="page-title">ข้อมูลส่วนตัวและที่อยู่ (รหัสผู้ใช้: #<?= $user['user_id'] ?>)</h1>

      <!-- หมวด 1: ข้อมูลส่วนตัว -->
      <div class="section-title">👤 ข้อมูลส่วนตัวทั่วไป</div>

      <!-- 1. ชื่อ (user_name) -->
      <div class="row-card">
        <div class="info-display-bar">
          <div class="info-label">ชื่อ</div>
          <div class="info-value-box" id="val-user_name"><?= htmlspecialchars($user['user_name'] ?? '-') ?></div>
          <button class="btn-edit-toggle" id="btn-user_name" onclick="toggleEdit('user_name', true)">แก้ไข</button>
        </div>
        <div class="edit-box" id="box-user_name">
          <form method="POST">
            <input type="hidden" name="field" value="user_name">
            <label style="display:block; font-size:13px; margin-bottom:5px; font-weight:600;">ระบุชื่อใหม่:</label>
            <input type="text" name="user_name" class="edit-input" value="<?= htmlspecialchars($user['user_name'] ?? '') ?>" required>
            <div>
              <button type="submit" class="btn-save">บันทึก</button>
              <button type="button" class="btn-cancel" onclick="toggleEdit('user_name', false)">ยกเลิก</button>
            </div>
          </form>
        </div>
      </div>

      <!-- 2. นามสกุล (user_lastname) -->
      <div class="row-card">
        <div class="info-display-bar">
          <div class="info-label">นามสกุล</div>
          <div class="info-value-box" id="val-user_lastname"><?= htmlspecialchars($user['user_lastname'] ?? '-') ?></div>
          <button class="btn-edit-toggle" id="btn-user_lastname" onclick="toggleEdit('user_lastname', true)">แก้ไข</button>
        </div>
        <div class="edit-box" id="box-user_lastname">
          <form method="POST">
            <input type="hidden" name="field" value="user_lastname">
            <label style="display:block; font-size:13px; margin-bottom:5px; font-weight:600;">ระบุนามสกุลใหม่:</label>
            <input type="text" name="user_lastname" class="edit-input" value="<?= htmlspecialchars($user['user_lastname'] ?? '') ?>" required>
            <div>
              <button type="submit" class="btn-save">บันทึก</button>
              <button type="button" class="btn-cancel" onclick="toggleEdit('user_lastname', false)">ยกเลิก</button>
            </div>
          </form>
        </div>
      </div>

      <!-- 3. อีเมลล์ (user_email) -->
      <div class="row-card">
        <div class="info-display-bar">
          <div class="info-label">อีเมลล์</div>
          <div class="info-value-box" id="val-user_email"><?= htmlspecialchars($user['user_email'] ?? '-') ?></div>
          <button class="btn-edit-toggle" id="btn-user_email" onclick="toggleEdit('user_email', true)">แก้ไข</button>
        </div>
        <div class="edit-box" id="box-user_email">
          <form method="POST">
            <input type="hidden" name="field" value="user_email">
            <label style="display:block; font-size:13px; margin-bottom:5px; font-weight:600;">ระบุอีเมลล์ใหม่:</label>
            <input type="email" name="user_email" class="edit-input" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>" required>
            <div>
              <button type="submit" class="btn-save">บันทึก</button>
              <button type="button" class="btn-cancel" onclick="toggleEdit('user_email', false)">ยกเลิก</button>
            </div>
          </form>
        </div>
      </div>

      <!-- 4. เบอร์โทรศัพท์ (user_phone) -->
      <div class="row-card">
        <div class="info-display-bar">
          <div class="info-label">เบอร์โทรศัพท์</div>
          <div class="info-value-box" id="val-user_phone"><?= htmlspecialchars($user['user_phone'] ?? '-') ?></div>
          <button class="btn-edit-toggle" id="btn-user_phone" onclick="toggleEdit('user_phone', true)">แก้ไข</button>
        </div>
        <div class="edit-box" id="box-user_phone">
          <form method="POST">
            <input type="hidden" name="field" value="user_phone">
            <label style="display:block; font-size:13px; margin-bottom:5px; font-weight:600;">ระบุเบอร์โทรศัพท์ใหม่:</label>
            <input type="tel" name="user_phone" class="edit-input" value="<?= htmlspecialchars($user['user_phone'] ?? '') ?>" required>
            <div>
              <button type="submit" class="btn-save">บันทึก</button>
              <button type="button" class="btn-cancel" onclick="toggleEdit('user_phone', false)">ยกเลิก</button>
            </div>
          </form>
        </div>
      </div>

      <!-- หมวด 2: ที่อยู่จัดส่ง -->
      <div class="section-title">📍 ที่อยู่จัดส่งสินค้า</div>

      <!-- 5. ที่อยู่จัดส่ง -->
      <div class="row-card">
        <div class="info-display-bar">
          <div class="info-label">ที่อยู่จัดส่งประจำ</div>
          <div class="info-value-box" id="val-address">
            <?php if (!empty($user['address'])): ?>
              <?= htmlspecialchars($user['address']) ?> 
              ต.<?= htmlspecialchars($user['subdistrict'] ?? '') ?> 
              อ.<?= htmlspecialchars($user['district'] ?? '') ?> 
              จ.<?= htmlspecialchars($user['province'] ?? '') ?> 
              <?= htmlspecialchars($user['zipcode'] ?? '') ?>
            <?php else: ?>
              <span class="empty-text">ยังไม่ได้ระบุที่อยู่จัดส่ง</span>
            <?php endif; ?>
          </div>
          <button class="btn-edit-toggle" id="btn-address" onclick="toggleEdit('address', true)">แก้ไข</button>
        </div>
        <div class="edit-box" id="box-address">
          <form method="POST">
            <input type="hidden" name="field" value="shipping_address">
            <label style="display:block; font-size:13px; margin-bottom:5px; font-weight:600;">บ้านเลขที่, หมู่, ซอย, ถนน, อาคาร:</label>
            <textarea name="address" class="edit-textarea" rows="2" placeholder="กรอกรายละเอียดที่อยู่"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            
            <div class="address-grid">
              <div>
                <label style="font-size:12px; color:#666;">ตำบล / แขวง</label>
                <input type="text" name="subdistrict" class="edit-input" value="<?= htmlspecialchars($user['subdistrict'] ?? '') ?>">
              </div>
              <div>
                <label style="font-size:12px; color:#666;">อำเภอ / เขต</label>
                <input type="text" name="district" class="edit-input" value="<?= htmlspecialchars($user['district'] ?? '') ?>">
              </div>
              <div>
                <label style="font-size:12px; color:#666;">จังหวัด</label>
                <input type="text" name="province" class="edit-input" value="<?= htmlspecialchars($user['province'] ?? '') ?>">
              </div>
              <div>
                <label style="font-size:12px; color:#666;">รหัสไปรษณีย์</label>
                <input type="text" name="zipcode" class="edit-input" maxlength="5" value="<?= htmlspecialchars($user['zipcode'] ?? '') ?>">
              </div>
            </div>

            <div>
              <button type="submit" class="btn-save">บันทึกที่อยู่</button>
              <button type="button" class="btn-cancel" onclick="toggleEdit('address', false)">ยกเลิก</button>
            </div>
          </form>
        </div>
      </div>
        <form action="logout.php" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันออกจากระบบใช่หรือไม่?');">
            <button type="submit" class="btn-logout">
                ออกจากระบบ
            </button>
        </form>
    </div>
  </div>

  <script>
    function toggleEdit(fieldId, isEditing) {
      const boxEl = document.getElementById('box-' + fieldId);
      const btnEl = document.getElementById('btn-' + fieldId);

      if (isEditing) {
        boxEl.classList.add('active');
        btnEl.innerText = 'กำลังแก้ไข';
        btnEl.style.pointerEvents = 'none';
        btnEl.style.opacity = '0.6';
      } else {
        boxEl.classList.remove('active');
        btnEl.innerText = 'แก้ไข';
        btnEl.style.pointerEvents = 'auto';
        btnEl.style.opacity = '1';
      }
    }
  </script>
</body>
</html>