<?php
include_once 'dbconnect_flower.php';
 //4.2 check if form is submitted
    if(isset($_POST['login'])) {
        //4.3 initialize variables
        //4.4 sanitize user inputs, prevent SQL injection
        $user_email = mysqli_real_escape_string($conn, $_POST['user-email']);
        $user_password = mysqli_real_escape_string($conn, $_POST['user-password']);

        //4.5 check if user exists in database
        $SQL = "SELECT * FROM users WHERE user_email='$user_email' AND user_passwd='" . md5($user_password) . "'";
        // execute the query
        $result = mysqli_query($conn, $SQL);

        //4.6 if user exists, start session and redirect to index.php
        //convert resultset to array and check if it has any rows
        if($row = mysqli_fetch_array($result)) {
            // user exists, start session
            session_start();
            if ($row['user_type'] == 'A') {
                $_SESSION['session_admin_name'] = $row['user_name'];
                header("Location: show_user.php");
                exit();
            } else {
                $_SESSION['session_user_id'] = $row['user_id'];
                $_SESSION['session_user_name'] = $row['user_name'];
                // redirect to index.php
                header("Location: index.php");
                exit();
            }
        } else {
            $login_error = "Invalid email or password.";
        }
    }
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700&family=Charm:wght@400;700&family=Sriracha&display=swap" rel="stylesheet">
    
    <title>สร้างบัญชี - TERMTEM+</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Sriracha", cursive;
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
            font-size: 32px;
            font-weight: bold;
            font-style: italic;
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
            gap: 25px;
            color: #8a4b0f;
            font-size: 18px;
        }

        .header-menu a {
            text-decoration: none;
            color: #8a4b0f;
        }

        .menu-icon {
            font-size: 28px;
        }


        /* ===== Main ===== */
        main {
            max-width: 900px;
            margin: 35px auto 100px;
            padding: 0 20px;
        }

        h1 {
            color: #8a4b0f;
            font-size: 32px;
            margin-bottom: 35px;
        }

        .register-box {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 30px 25px;
        }


        /* ===== Form ===== */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 19px;
            margin-bottom: 10px;
            color: #555;
        }

        label span {
            color: red;
        }

        input {
            width: 100%;
            height: 50px;
            border: none;
            background-color: #f5f5f5;
            padding: 10px 15px;
            font-size: 17px;
            outline: none;
        }

        input:focus {
            background-color: white;
            border: 2px solid #8a4b0f;
        }


        /* ===== Button ===== */
        .register-btn {
            width: 100%;
            height: 60px;
            border: none;
            border-radius: 15px;
            background-color: #8a4b0f;
            color: white;
            font-size: 21px;
            cursor: pointer;
            margin-top: 10px;
        }

        .register-btn:hover {
            background-color: #8a4b0f;
        }


        /* ===== Login ===== */
        .already {
            text-align: center;
            margin-top: 45px;
            font-size: 18px;
        }

        .login-link {
            display: block;
            margin-top: 15px;
            color: #8a4b0f;
            text-decoration: none;
            font-size: 18px;
        }

        .login-link:hover {
            text-decoration: underline;
        }


        /* ===== Footer ===== */
        footer {
            background-color: #fafafa;
            padding: 50px 7%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .footer-column h2 {
            color: #8a4b0f;
            font-size: 23px;
            margin-bottom: 25px;
        }

        .footer-column p {
            margin-bottom: 15px;
            font-size: 17px;
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
                font-size: 13px;
            }

            main {
                margin-top: 30px;
                padding: 0 15px;
            }

            h1 {
                font-size: 24px;
            }

            .register-box {
                padding: 25px 20px;
            }

            label {
                font-size: 18px;
            }

            .register-btn {
                height: 55px;
                font-size: 18px;
            }

            footer {
                grid-template-columns: 1fr;
                padding: 40px 25px;
            }
        }
    </style>
</head>
<body>
    
    <!-- Header -->
    <header>

        <div class="logo">
           🌸TERMTEM+
            <span>02-970-8000</span>
        </div>

        <div class="header-menu"> 
            <a href="login.html">
                👤 ล็อกอิน/สมัครสมาชิก
            </a>

            <span>🛒</span>

            <span class="menu-icon">☰</span>
        </div>

    </header>


    <!-- Main -->
    <main>

        <h1>สร้างบัญชี TERMTEM+</h1>

        <div class="register-box">

            <form id="registerForm" action="register.php" method="POST">

                <!-- ชื่อ -->
                <div class="form-group">
                    <label>
                        ชื่อ <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="firstname"
                        name="firstname"
                        placeholder="กรุณากรอกชื่อ"
                        required
                    >
                </div>


                <!-- นามสกุล -->
                <div class="form-group">
                    <label>
                        นามสกุล <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="lastname"
                        name="lastname"
                        placeholder="กรุณากรอกนามสกุล"
                        required
                    >
                </div>


                <!-- Email -->
                <div class="form-group">
                    <label>
                        อีเมล์ <span>*</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@email.com"
                        required
                    >
                </div>


                <!-- เบอร์โทร -->
                <div class="form-group">
                    <label>
                        เบอร์โทรศัพท์ <span>*</span>
                    </label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="กรุณากรอกเบอร์โทรศัพท์"
                        required
                    >
                </div>


                <!-- รหัสผ่าน -->
                <div class="form-group">
                    <label>
                        รหัสผ่าน <span>*</span>
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="กรอกรหัสผ่าน"
                        required
                    >
                </div>


                <!-- ยืนยันรหัสผ่าน -->
                <div class="form-group">
                    <label>
                        ยืนยันรหัสผ่าน <span>*</span>
                    </label>

                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        placeholder="กรอกรหัสผ่านอีกครั้ง"
                        required
                    >
                </div>


                <!-- Button -->
                <button
                    type="submit"
                    class="register-btn">
                    สร้างบัญชี
                </button>

            </form>


            <!-- กลับไป Login -->
            <div class="already">

                มีบัญชีอยู่แล้ว?

                <a href="login.html" class="login-link">
                    เข้าสู่ระบบ
                </a>

            </div>

        </div>

    </main>


    <!-- Footer -->
    <footer>

        <div class="footer-column">

            <h2>TERMTEM+</h2>

            <p>เกี่ยวกับเรา</p>
            <p>ข่าวและกิจกรรม</p>
            <p>ติดต่อเรา</p>
            <p>ร่วมงานกับเรา</p>

        </div>


        <div class="footer-column">

            <h2>ช่องทางการสั่งซื้อ</h2>

            <p>สั่งซื้อทางโทรศัพท์</p>
            <p>สั่งซื้อผ่านเว็บไซต์</p>
            <p>สั่งซื้อผ่านไลน์</p>
            <p>สั่งซื้อผ่านอีเมล์</p>

        </div>

    </footer>


    <script>

        document.getElementById("registerForm")
        .addEventListener("submit", function(event) {
            const password =
                document.getElementById("password").value;

            const confirmPassword =
                document.getElementById("confirmPassword").value;

            if (password !== confirmPassword) {
                event.preventDefault();

                alert("รหัสผ่านไม่ตรงกัน");

                return;
            }
        });

    </script>

</body>
</html>
