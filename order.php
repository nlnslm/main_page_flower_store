<?php
session_start();
require_once 'dbconnect_flower.php';

// ป้องกัน Warning ตัวแปร $error ที่อาจเกิดขึ้นในแบบฟอร์ม
$error = '';

// 1. ดึงข้อมูลผู้ใช้งานที่ล็อกอินมาแสดงในช่องผู้รับ & ที่อยู่
$saved_user = null;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $u_res = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $uid");
    if ($u_res) {
        $saved_user = mysqli_fetch_assoc($u_res);
    }
}

// 2. ดึงรายการสินค้าจากตะกร้า Session หรือกรณีส่ง product_id มาโดยตรง (Buy Now)
$cart_items = [];
$subtotal = 0;
$shipping_fee = 80; // ค่าจัดส่งคงที่ตามหน้าเว็บเดิม

// กรณีที่ 1: สั่งซื้อโดยตรงผ่าน URL เช่น order.php?product_id=1&qty=1
if (isset($_GET['product_id'])) {
    $pid = (int)$_GET['product_id'];
    $qty = isset($_GET['qty']) ? max(1, (int)$_GET['qty']) : 1;
    
    $p_stmt = mysqli_prepare($conn, "SELECT product_id, product_name, product_price, product_image FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($p_stmt, "i", $pid);
    mysqli_stmt_execute($p_stmt);
    $p_res = mysqli_stmt_get_result($p_stmt);
    if ($row = mysqli_fetch_assoc($p_res)) {
        $row['qty'] = $qty;
        $row['line_total'] = $row['product_price'] * $qty;
        $subtotal += $row['line_total'];
        $cart_items[] = $row;
    }
    mysqli_stmt_close($p_stmt);
} 
// กรณีที่ 2: มาจากตะกร้าสินค้า ($_SESSION['cart'])
elseif (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    // ดึง product_id ทั้งหมดใน cart
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    if (!empty($ids)) {
        $ids_str = implode(',', $ids);
        $p_res = mysqli_query($conn, "SELECT product_id, product_name, product_price, product_image FROM products WHERE product_id IN ($ids_str)");
        
        if ($p_res) {
            while ($row = mysqli_fetch_assoc($p_res)) {
                $pid = $row['product_id'];
                
                // รองรับทั้งแบบ $_SESSION['cart'][$pid]['qty'] และแบบ $_SESSION['cart'][$pid] = จำนวนตัวเลข
                if (is_array($_SESSION['cart'][$pid])) {
                    $qty = isset($_SESSION['cart'][$pid]['qty']) ? (int)$_SESSION['cart'][$pid]['qty'] : 1;
                } else {
                    $qty = (int)$_SESSION['cart'][$pid];
                }
                $qty = max(1, $qty);

                $row['qty'] = $qty;
                $row['line_total'] = $row['product_price'] * $qty;
                $subtotal += $row['line_total'];
                $cart_items[] = $row;
            }
        }
    }
}

// ถ้าไม่มีสินค้าในตะกร้าเลย จึงค่อยดึงสินค้าตัวอย่าง 1 ชิ้น
if (empty($cart_items)) {
    $fallback_res = mysqli_query($conn, "SELECT product_id, product_name, product_price, product_image FROM products ORDER BY product_id ASC LIMIT 1");
    if ($fallback_res && ($row = mysqli_fetch_assoc($fallback_res))) {
        $row['qty'] = 1;
        $row['line_total'] = $row['product_price'];
        $subtotal += $row['line_total'];
        $cart_items[] = $row;
    }
}

$grand_total = $subtotal + $shipping_fee;

// รวมที่อยู่จัดส่งของผู้ใช้ที่บันทึกไว้
$full_address = "";
if (!empty($saved_user['address'])) {
    $full_address = $saved_user['address'] . 
                    (!empty($saved_user['subdistrict']) ? " ต." . $saved_user['subdistrict'] : "") . 
                    (!empty($saved_user['district']) ? " อ." . $saved_user['district'] : "") . 
                    (!empty($saved_user['province']) ? " จ." . $saved_user['province'] : "") . 
                    (!empty($saved_user['zipcode']) ? " " . $saved_user['zipcode'] : "");
}
?>

<!doctype html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>สั่งซื้อสินค้า | TERMTEM+</title>
    <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:wght@300;400;500;600;700&family=Itim&display=swap" rel="stylesheet">
    <style>
:root{--brown:#8a4b0f;--cream:#fffdf8;--border:#e9dcca;--muted:#7c7369}*{box-sizing:border-box}body{margin:0;background:var(--cream);color:#342a21;font-family:'Bai Jamjuree',sans-serif;font-size:15px}.site-header{max-width:1200px;margin:auto;padding:22px 28px;border-bottom:2px solid var(--brown);display:flex;justify-content:space-between;align-items:center}.brand{font-size:23px;font-weight:700;color:var(--brown);text-decoration:none}.site-header nav{display:flex;gap:24px;align-items:center}.site-header nav a,.back{color:#594b3f;text-decoration:none}.container{max-width:1200px;margin:34px auto 65px;padding:0 28px}.back{font-size:14px}.intro{margin:28px 0}.intro h1{font-family:Itim,sans-serif;font-size:36px;color:var(--brown);font-weight:400;margin:0 0 5px}.intro p{color:var(--muted);margin:0}.layout{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(290px,1fr);gap:24px;align-items:start}.left{display:grid;gap:20px}.panel{border:1px solid var(--border);background:#fff;border-radius:15px;padding:26px;box-shadow:0 5px 24px #7b4a1610}.panel h2{font-size:19px;color:var(--brown);margin:0 0 22px;font-weight:600}.product{display:flex;gap:22px}.product img{width:155px;height:165px;object-fit:cover;border-radius:11px;background:#f4ece5}.product-info h3{font-size:20px;margin:4px 0 6px}.muted,.hint{color:var(--muted)}.product-info strong{color:var(--brown);font-size:21px}.qty{display:flex;align-items:center;margin-top:16px;gap:16px}.qty button{background:#fff9f1;border:1px solid var(--border);border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:20px;color:var(--brown)}.two{display:grid;grid-template-columns:1fr 1fr;gap:16px}label:not(.radio){display:block;font-weight:600;margin-bottom:16px}label em{color:#b34848;font-style:normal}input:not([type=radio]),textarea,select{display:block;width:100%;margin-top:8px;border:1px solid #dcd2c6;border-radius:8px;padding:12px 13px;font:inherit;background:#fffdfb;color:#352b21}input:focus,textarea:focus,select:focus{outline:2px solid #c7a47d}textarea{resize:vertical}.radio{display:block;margin:15px 0;cursor:pointer}.radio input{accent-color:var(--brown)}.hint{font-size:12px;line-height:1.65}.line{display:flex;justify-content:space-between;gap:12px;margin:18px 0}.summary{position:sticky;top:20px}.summary hr{border:0;border-top:1px solid var(--border);margin:23px 0}.total{font-size:20px;color:var(--brown);font-weight:700}.submit{background:var(--brown);color:#fff;border:0;border-radius:9px;width:100%;padding:15px;cursor:pointer;font:600 16px 'Bai Jamjuree',sans-serif;margin-top:18px}.submit:hover{background:#6e3908}.center{text-align:center}#result{margin-top:15px;font-size:13px;line-height:1.7}#result.success{background:#eef8ee;border:1px solid #a7d1a7;border-radius:8px;padding:12px}footer{text-align:center;padding:25px;color:var(--muted);border-top:1px solid var(--border)}@media(max-width:800px){.layout{grid-template-columns:1fr}.summary{position:static}}@media(max-width:550px){.site-header{padding:15px;gap:10px;flex-wrap:wrap}.site-header nav{gap:12px;font-size:12px}.container{padding:0 15px}.panel{padding:18px}.two{grid-template-columns:1fr;gap:0}.product img{width:105px;height:135px}.product{gap:12px}.intro h1{font-size:30px}}

.error{margin-top:14px;padding:12px;border-radius:8px;color:#9b1c1c;background:#fff1f1;border:1px solid #f3b6b6}

.receipt-container{max-width:780px}.receipt-panel{padding:36px 42px}.receipt-panel h1{font-family:Itim,sans-serif;text-align:center;font-size:38px;font-weight:400;color:var(--brown);margin:10px 0}.receipt-icon{background:#f5eadb;color:var(--brown);font-size:32px;display:grid;place-items:center;width:64px;height:64px;border-radius:50%;margin:0 auto}.receipt-lead{text-align:center;color:var(--muted)}.order-number{text-align:center;background:#fff8ef;border:1px solid var(--border);border-radius:10px;padding:15px;margin:25px 0}.receipt-panel h2{margin-top:30px;margin-bottom:18px}.detail{display:grid;grid-template-columns:155px 1fr;gap:15px;margin:15px 0;line-height:1.65}.detail span{color:var(--muted)}.detail strong{font-weight:500;overflow-wrap:anywhere}.receipt-panel hr{border:0;border-top:1px solid var(--border);margin:22px 0}.receipt-note{margin-top:30px;padding:16px;border-radius:9px;background:#fff8ef;color:#65482f;text-align:center}.receipt-link{display:block;text-decoration:none;text-align:center}.receipt-link:hover{color:white}@media(max-width:550px){.receipt-panel{padding:24px 20px}.detail{grid-template-columns:1fr;gap:2px}}

/* ส่วนหัวให้สอดคล้องกับหน้า main.html */
.site-header {max-width:900px;padding:18px 15px}
.container {max-width:1000px}
.site-header .brand {font-size:20px}
.site-header nav a:hover,.back:hover {color:#8a4b0f;text-decoration:underline}
@media(max-width:550px){.site-header{max-width:100%}}

</style>
</head>

<body>

    <header class="site-header">
        <a class="brand" href="index.php">🌸 TERMTEM+</a>

        <nav>
            <a href="index.php">หน้าหลัก</a>
            <a href="login.php">ล็อกอิน/สมัครสมาชิก</a>
            <a href="cart.php" class="nav-item">🛒 รถเข็น</a>
        </nav>
    </header>

    <main class="container">

        <a class="back" href="index.php">← กลับไปเลือกดอกไม้</a>

        <div class="intro">
            <h1>สั่งซื้อสินค้า</h1>
            <p>เติมความพิเศษให้ทุกโอกาส ด้วยช่อดอกไม้ที่คุณเลือกเอง ♡</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form id="order-form" action="order-success.php" method="post">

            <input type="hidden" name="product_id" id="product-id" value="1">
            <input type="hidden" name="quantity" id="quantity-input" value="1">

            <div class="layout">

                <section class="left">

                    <!-- ฝั่งซ้าย: รายการสินค้า และ ข้อมูลผู้รับ -->
                    <div style="flex: 2; min-width: 320px;">
      
                        <div class="order-card">
                            <h3>01 · รายการสินค้า</h3>

                            <?php if (!empty($cart_items)): ?>
                              <?php foreach ($cart_items as $item): ?>
                                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f2ede7;">
                                  <img src="<?= htmlspecialchars($item['product_image']) ?>" style="width: 75px; height: 75px; object-fit: cover; border-radius: 6px;">
                                  <div style="flex: 1;">
                                    <h4 style="margin: 0; font-size: 16px;"><?= htmlspecialchars($item['product_name']) ?></h4>
                                    <div style="color: #777; font-size: 13px; margin: 4px 0;">ช่อดอกไม้จัดสด · <?= $item['qty'] ?> ช่อ</div>
                                    <div style="color: #8a4b0f; font-weight: bold;">฿<?= number_format($item['line_total']) ?></div>
                                  </div>
                                  <div style="font-size: 14px; color: #555; background: #faf6f0; padding: 4px 10px; border-radius: 4px;">
                                    จำนวน: <?= $item['qty'] ?>
                                  </div>
                                </div>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <p style="color: #888;">ไม่มีสินค้าในรถเข็น</p>
                            <?php endif; ?>
                        </div>

                    <!-- 02 · ข้อมูลผู้รับ (ดึงจาก users อัตโนมัติ) -->
                    <div class="order-card" style="background:#fff; border:1px solid #ebd9c8; border-radius:8px; padding:20px; margin-bottom:20px;">
                        <h3 style="color:#8a4b0f; font-size:17px; margin-bottom:15px;">02 · ข้อมูลผู้รับ</h3>
        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                            <div>
                                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">ชื่อผู้รับ *</label>
                                <input type="text" name="receiver_name" required 
                                       value="<?= htmlspecialchars(trim(($saved_user['user_name'] ?? '') . ' ' . ($saved_user['user_lastname'] ?? ''))) ?>" 
                                       style="width:100%; padding:9px 12px; border:1px solid #ebd9c8; border-radius:4px; font-family:inherit;" 
                                       placeholder="ชื่อ-นามสกุล">
                            </div>
                            <div>
                                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">เบอร์โทรศัพท์ *</label>
                                <input type="tel" name="receiver_phone" required 
                                       value="<?= htmlspecialchars($saved_user['user_phone'] ?? '') ?>" 
                                       style="width:100%; padding:9px 12px; border:1px solid #ebd9c8; border-radius:4px; font-family:inherit;" 
                                       placeholder="08xxxxxxxx">
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">ที่อยู่จัดส่ง *</label>
                            <textarea name="receiver_address" rows="3" required 
                                      style="width:100%; padding:9px 12px; border:1px solid #ebd9c8; border-radius:4px; font-family:inherit;" 
                                      placeholder="บ้านเลขที่ ถนน ตำบล อำเภอ จังหวัด รหัสไปรษณีย์"><?= htmlspecialchars($full_address) ?></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                            <div>
                                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">วันที่ต้องการจัดส่ง *</label>
                                <input type="date" name="delivery_date" required style="width:100%; padding:9px 12px; border:1px solid #ebd9c8; border-radius:4px; font-family:inherit;">
                            </div>
                            <div>
                                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">ช่วงเวลาจัดส่ง</label>
                                <select name="delivery_time" style="width:100%; padding:9px 12px; border:1px solid #ebd9c8; border-radius:4px; font-family:inherit;">
                                    <option value="09:00-12:00">09:00–12:00 น.</option>
                                    <option value="13:00-16:00">13:00–16:00 น.</option>
                                    <option value="16:00-19:00">16:00–19:00 น.</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; margin-bottom:4px;">ข้อความบนการ์ด (ไม่บังคับ)</label>
                            <input type="text" name="card_message" placeholder="ระบุข้อความอวยพร" style="width:100%; padding:9px 12px; border:1px solid #ebd9c8; border-radius:4px; font-family:inherit;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ฝั่งขวา: สรุปคำสั่งซื้อ -->
            <div style="flex: 1; min-width: 280px;">
                <div class="summary-box" style="background:#fff; border:1px solid #ebd9c8; border-radius:8px; padding:22px; position:sticky; top:20px;">
                    <h3 style="font-size:18px; margin-bottom:15px; color:#222;">สรุปคำสั่งซื้อ</h3>
        
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; color:#666; font-size:14px;">
                        <span>ราคาสินค้า</span>
                        <span style="font-weight:600; color:#333;">฿<?= number_format($subtotal) ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#666; font-size:14px;">
                        <span>ค่าจัดส่ง</span>
                        <span style="font-weight:600; color:#333;">฿<?= number_format($shipping_fee) ?></span>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-bottom:20px; padding-top:12px; border-top:1px dashed #ebd9c8; font-size:16px;">
                        <span style="font-weight:bold; color:#8a4b0f;">ยอดรวม</span>
                        <span style="font-weight:bold; color:#8a4b0f; font-size:20px;">฿<?= number_format($grand_total) ?></span>
                    </div>

                    <button class="submit" type="submit" style="width:100%; background:#8a4b0f; color:#fff; border:none; padding:12px; border-radius:4px; font-size:16px; font-weight:bold; cursor:pointer;">
                        ยืนยันคำสั่งซื้อ →
                    </button>
                </div>
            </div>
        </div>
    </form>

    </main>

    <footer>© 2026 TERMTEM+ · Flowers for every moment</footer>

    <script>
        // แก้รายการนี้ให้รหัส ชื่อ ราคา และตำแหน่งรูปตรงกับหน้าหลักของเพื่อน
        const $ = id => document.getElementById(id);
        const params = new URLSearchParams(location.search);
        $('product-id').value = productId;
        const product = products[productId];
        let quantity = <?= (int) $initialQuantity ?>;
        const shippingFee = 80; // ค่าจัดส่งสำหรับการสาธิต
        const baht = amount => '฿' + amount.toLocaleString('th-TH');
        $('product-image').alt = product.name;
        $('shipping').textContent = baht(shippingFee);
        function render() {
            $('quantity').textContent = quantity;
            $('quantity-input').value = quantity;
            $('subtotal').textContent = baht(product.price * quantity);
            $('shipping').textContent = baht(shippingFee);
            $('total').textContent = baht(product.price * quantity + shippingFee);
        }
        $('minus').addEventListener('click',()=>{quantity=Math.max(1,quantity-1);render()});
        $('plus').addEventListener('click',()=>{quantity=Math.min(99,quantity+1);render()});
        const dateInput = $('delivery-date');
        const today = new Date();
        const localToday = new Date(today.getTime()-today.getTimezoneOffset()*60000).toISOString().slice(0,10);
        dateInput.min = localToday;
        render();


    </script>

</body>
</html>
