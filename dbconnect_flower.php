<?php
    //1. เชื่อม php เข้ากับฐานข้อมูล MySQL
    $host = "localhost"; // กำหนดที่อยู่ของฐานข้อมูลเป็น localhost
    $user = "root"; // กำหนดชื่อผู้ใช้ฐานข้อมูลเป็น root
    $password = ""; // กำหนดรหัสผ่านฐานข้อมูลเป็นค่าว่าง
    $database = "termtem_store"; // กำหนดชื่อฐานข้อมูลเป็น userDB
    $conn = mysqli_connect($host, $user, $password, $database); // สร้างการเชื่อมต่อกับฐานข้อมูล MySQL
    // ตรวจสอบว่าการเชื่อมต่อสำเร็จหรือไม่
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error()); // . mysqli_connect_error() ใช้ดึงข้อความข้อผิดพลาดจาก MySQL
    }
?>