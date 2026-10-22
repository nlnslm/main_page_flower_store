<?php
    //connect to database
    include_once 'dbconnect_flower.php';
    //initialize variables
    $err_flag = false;

    //check if form is submitted
    if(isset($_POST['signup'])) {
        $name = $_POST['user-name'];
        $lastname = $_POST['user-lastname'];
        $email = $_POST['user-email'];
        $phone = $_POST['user-phone'];
        $password = $_POST['user-password'];
        $cpassword = $_POST['user-cpassword'];

        //validate form inputs
        // validate password and confirm password
        if($password != $cpassword) {
            $cpassword_error = "Passwords do not match.";
            $err_flag = true;
        }

        // validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "Invalid email format.";
            $err_flag = true;
        }

        // validate password length
        if(strlen($password) < 6) {
            $password_error = "Password must be at least 6 characters long.";
            $err_flag = true;
        }

        // validate name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Zก-๙\s]+$/u",$name)) {
            $name_error = "Only letters and white space allowed in name.";
            $err_flag = true;
        }

        //2.5 if no errors, insert into database
        if(!$err_flag) {
            // hash the password before storing it
            //$hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $hashed_password = md5($password);
            // insert into database
            $SQL = "INSERT INTO users (user_name, user_lastname , user_email, user_phone, user_password)
                    VALUES ('$name','$lastname','$email','$phone', '$hashed_password' )";
            // execute the query
            if(mysqli_query($conn, $SQL)) {
                $success_message = "Registration successful. You can now <a href='login.php'>login</a>.";
            } else {
                $database_error = "Error: " . mysqli_error($conn);
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างบัญชี - TERMTEM+</title>

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
            font-family:'Bai Jamjuree', sans-serif;
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
        </div>

        <div class="header-menu"> 
            <a href="login.php">
                👤 ล็อกอิน/สมัครสมาชิก
            </a>
        </div>
    </header>

    <!-- Main -->
    <main>
        <h1>สร้างบัญชี TERMTEM+</h1>
        <div class="register-box">
            <form id="registerForm" action="register.php" method="POST">

                <!-- ชื่อ -->
                <div class="form-group"><label> ชื่อ <span>*</span></label>
                    <input
                        type="text" id="firstname" name="user-name" placeholder="กรุณากรอกชื่อ" required
                    >
                </div>

                <!-- นามสกุล -->
                <div class="form-group">
                    <label> นามสกุล <span>*</span></label>
                    <input
                        type="text" id="lastname" name="user-lastname" placeholder="กรุณากรอกนามสกุล" required
                    >
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label> อีเมล์ <span>*</span></label>
                    <input
                        type="email" id="email" name="user-email" placeholder="example@email.com" required
                    >
                </div>

                <!-- เบอร์โทร -->
                <div class="form-group">
                    <label> เบอร์โทรศัพท์ <span>*</span> </label>

                    <input
                        type="tel" id="phone" name="user-phone" placeholder="กรุณากรอกเบอร์โทรศัพท์" required
                    >
                </div>

                <!-- รหัสผ่าน -->
                <div class="form-group">
                    <label> รหัสผ่าน <span>*</span> </label>
                    <input
                        type="password" id="password" name="user-password" placeholder="กรอกรหัสผ่าน" required
                    >
                </div>

                <!-- ยืนยันรหัสผ่าน -->
                <div class="form-group">
                    <label>ยืนยันรหัสผ่าน <span>*</span></label>
                    <input
                        type="password" id="confirmPassword" name="user-cpassword" placeholder="กรอกรหัสผ่านอีกครั้ง" required
                    >
                </div>

                <!-- Button -->
                <button
                    type="submit" name="signup" class="register-btn">
                    สร้างบัญชี
                </button>
            </form>

            <!-- กลับไป Login -->
            <div class="already">
                มีบัญชีอยู่แล้ว?
                <a href="login.php" class="login-link"> เข้าสู่ระบบ </a>
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