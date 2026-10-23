<?php
session_start();

// 1. เคลียร์ค่าตัวแปรใน Session ทั้งหมด
$_SESSION = [];

// 2. ลบคุกกี้ของ Session ในเบราว์เซอร์ (ถ้ามี)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. ทำลาย Session ทั้งหมด
session_destroy();

// 4. ส่งผู้ใช้กลับไปยังหน้าล็อกอิน (หรือเปลี่ยนเป็น index.php ได้ตามต้องการ)
header("Location: login.php");
exit();
?>