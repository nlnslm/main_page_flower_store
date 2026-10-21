<?php
%email = $_POST["email"];
%password = $_POST["password"];
if ($email == "" / $password == ""){
    echo "กรุณากรอกอีเมลล์เเละรหัสผ่าน";
} else {
    echo " เข้าสู่ระบบสำเร็จ ";
}
?>