<?php
// ย้าย session_start() มาไว้บรรทัดแรกสุดของไฟล์ เพื่อป้องกันปัญหา Header already sent
session_start();
include_once 'dbconnect_flower.php';

$login_error = "";

// ตรวจสอบเมื่อมีการกดปุ่ม Submit ฟอร์มล็อกอิน
if (isset($_POST['login'])) {
    // กำจัดอักขระพิเศษเพื่อป้องกัน SQL Injection
    $user_email = mysqli_real_escape_string($conn, trim($_POST['user-email']));
    $user_password = mysqli_real_escape_string($conn, trim($_POST['user-password']));

    // ค้นหาผู้ใช้จากตาราง users ด้วย email และ password (md5)
    $hashed_pass = md5($user_password);
    $SQL = "SELECT * FROM users WHERE user_email = '$user_email' AND user_password = '$hashed_pass'";
    $result = mysqli_query($conn, $SQL);

    if ($row = mysqli_fetch_assoc($result)) {
        // 1. เก็บ Session พื้นฐานสำหรับทุกคน (ใช้สำหรับ cart.php, profile.php, index.php)
        $_SESSION['user_id']   = $row['user_id'];
        $_SESSION['user_name'] = $row['user_name'];
        $_SESSION['user_email'] = $row['user_email'];

        // รองรับทั้งระบบตัวแปรเก่าที่คุณเคยตั้งไว้
        $_SESSION['session_user_id']   = $row['user_id'];
        $_SESSION['session_user_name'] = $row['user_name'];

        // 2. ตรวจสอบสิทธิ์ผู้ใช้งาน (role = 'admin')
        $is_admin = (isset($row['role']) && $row['role'] === 'admin');

        if ($is_admin) {
            // สิทธิ์ Admin
            $_SESSION['role'] = 'admin';
            $_SESSION['session_admin_name'] = $row['user_name'];
            
            // ส่งแอดมินไปหน้าควบคุม (เช่น admin_dashboard.php)
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // สิทธิ์ Customer ปกติ
            $_SESSION['role'] = 'customer';
            
            // ส่งลูกค้าไปที่หน้าเลือกซื้อสินค้า
            header("Location: index.php");
            exit();
        }
    } else {
        $login_error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - TERMTEM+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
  
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Bai Jamjuree', sans-serif;
            background-color: #fffdf8;
            color: #555;
        }

        /* ===== Header ===== */
        header {
            height: 90px;
            border-bottom: 5px solid #8a4b0f;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            font-style: normal;
            color: #8a4b0f;
        }

        .logo span {
            display: block;
            font-size: 15px;
            text-align: center;
            margin-top: -5px;
        }

        .header-menu {
            display: flex;
            align-items: center;
            gap: 30px;
            color: #8a4b0f;
            font-size: 18px;
        }

        .header-menu a {
            text-decoration: none;
            color: #8a4b0f;
        }

        .menu-icon {
            font-size: 28px;
            cursor: pointer;
        }

        /* ===== Main ===== */
        main {
            max-width: 900px;
            margin: 35px auto 120px;
            padding: 0 20px;
        }

        h1 {
            color: #8a4b0f;
            font-size: 24px;
            margin-bottom: 35px;
        }

        .login-box {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 30px 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 20px;
            margin-bottom: 10px;
            color: #555;
        }

        label span {
            color: #e33;
        }

        input {
            width: 100%;
            height: 50px;
            border: none;
            background-color: #f5f5f5;
            padding: 10px 15px;
            font-size: 18px;
            outline: none;
        }

        input:focus {
            border: 2px solid #8a4b0f;
            background-color: #fff;
        }

        .password-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .forgot {
            color: #555;
            text-decoration: none;
            font-size: 17px;
        }

        .forgot:hover {
            color: #8a4b0f;
        }

        /* กล่องแจ้งเตือน Error */
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 18px;
            border: 1px solid #ef9a9a;
        }

        /* ===== Buttons ===== */
        .login-btn {
            width: 100%;
            height: 62px;
            border: none;
            border-radius: 15px;
            background-color: #8a4b0f;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-top: 5px;
        }

        .login-btn:hover {
            background-color: #6f3b0c;
        }

        .new-account {
            text-align: center;
            margin-top: 70px;
        }

        .new-account-title {
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
            color: #8a4b0f;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .new-account-title::before,
        .new-account-title::after {
            content: "";
            width: 105px;
            height: 1px;
            background-color: #8f7f53;
        }

        .register-btn {
            display: block;
            width: 100%;
            height: 62px;
            background-color: #ddd;
            color: #333;
            font-size: 21px;
            text-align: center;
            text-decoration: none;
            padding: 18px;
            border-radius: 15px;
            cursor: pointer;
        }

        .register-btn:hover {
            background-color: #ccc;
        }

        /* ===== Mobile ===== */
        @media (max-width: 600px) {
            header {
                height: 75px;
                padding: 0 20px;
            }

            .logo {
                font-size: 25px;
            }

            .logo span {
                font-size: 11px;
            }

            .header-menu {
                gap: 10px;
                font-size: 14px;
            }

            .menu-icon {
                font-size: 24px;
            }

            main {
                margin-top: 30px;
                padding: 0 15px;
                margin-bottom: 80px;
            }

            h1 {
                font-size: 24px;
            }

            .login-box {
                padding: 25px 20px;
            }

            label {
                font-size: 18px;
            }

            .login-btn,
            .register-btn {
                height: 55px;
                font-size: 18px;
            }

            .new-account {
                margin-top: 50px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            🌸TERMTEM+
        </div>
    </header>

    <!-- Main -->
    <main>
        <h1>ลงชื่อเข้าใช้งาน</h1>
        <div class="login-box">
            <!-- แสดง Error ถ้าอีเมลหรือรหัสผ่านผิด -->
            <?php if (!empty($login_error)): ?>
                <div class="error-message">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <!-- Email -->
                <div class="form-group">
                    <label> อีเมลล์ <span>*</span></label>
                    <input type="email" name="user-email" placeholder="กรอกอีเมลของคุณ" required >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div class="password-title">
                        <label> รหัสผ่าน <span>*</span> </label>
                    </div>
                    <input
                        type="password" name="user-password" placeholder="กรอกรหัสผ่านของคุณ" required
                    >
                </div>

                <!-- Login Button -->
                <button type="submit" name="login" class="login-btn">
                    ลงชื่อเข้าใช้งาน
                </button>
            </form>

            <!-- Register -->
            <div class="new-account">
                <div class="new-account-title">
                    ลูกค้าใหม่ TERMTEM+?
                </div>
                <a href="register.php" class="register-btn">
                    สมัครสมาชิก
                </a>
            </div>
        </div>
    </main>
    
    <script>
        // ตรวจสอบความถูกต้องเบื้องต้นก่อนส่งฟอร์ม (Validation)
        document.querySelector("form").addEventListener("submit", function(event) {
            const email = document.querySelector('input[name="user-email"]').value.trim();
            const password = document.querySelector('input[name="user-password"]').value.trim();
            if (email === "" || password === "") {
                event.preventDefault(); // ระงับการส่งฟอร์มถ้าข้อมูลว่าง
                alert("กรุณากรอกอีเมลและรหัสผ่านให้ครบถ้วน");
            }
        });
    </script>
</body>
</html>