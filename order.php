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
        <a class="brand" href="main.html">🌸 TERMTEM+</a>

        <nav>
            <a href="main.html">หน้าหลัก</a>
            <a href="Login.html">ล็อกอิน/สมัครสมาชิก</a>
            <span>🛒 <span id="cart-count">1</span></span>
        </nav>
    </header>

    <main class="container">

        <a class="back" href="main.html">← กลับไปเลือกดอกไม้</a>

        <div class="intro">
            <h1>สั่งซื้อดอกไม้</h1>
            <p>เติมความพิเศษให้ทุกโอกาส ด้วยช่อดอกไม้ที่คุณเลือกเอง ♡</p>
        </div>

        <form id="order-form" action="order-success.php" method="get">

            <input type="hidden" name="product_id" id="product-id" value="1">
            <input type="hidden" name="quantity" id="quantity-input" value="1">

            <div class="layout">

                <section class="left">

                    <div class="panel">
                        <h2>01 · รายการสินค้า</h2>

                        <div class="product">
                            <img id="product-image" src="https://i.pinimg.com/736x/7c/51/d9/7c51d99b4fb4463be950c8e7ef576cf1.jpg" alt="ช่อดอกไม้ที่เลือก" >

                            <div class="product-info">
                                <h3 id="product-name">Pastel Dream</h3>
                                <p class="muted">ช่อดอกไม้จัดสด · 1 ช่อ</p>
                                <strong id="unit-price">฿2,590</strong>

                                <div class="qty">
                                    <button type="button" id="minus" aria-label="ลดจำนวน">−</button>
                                    <output id="quantity">1</output>
                                    <button type="button" id="plus" aria-label="เพิ่มจำนวน">+</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <h2>02 · ข้อมูลผู้รับ</h2>

                        <div class="two">
                            <label>
                                ชื่อผู้รับ <em>*</em>
                                <input name="recipient" required placeholder="ชื่อ-นามสกุล">
                            </label>

                            <label>
                                เบอร์โทรศัพท์ <em>*</em>
                                <input name="phone" required inputmode="tel" pattern="[0-9]{9,10}" title="กรอกเบอร์โทร 9–10 หลัก" placeholder="08xxxxxxxx">
                            </label>
                        </div>

                        <label>
                            ที่อยู่จัดส่ง <em>*</em>
                            <textarea name="address" required rows="3" placeholder="บ้านเลขที่ ถนน ตำบล อำเภอ จังหวัด รหัสไปรษณีย์"></textarea>
                        </label>

                        <div class="two">
                            <label>
                                วันที่ต้องการจัดส่ง <em>*</em>
                                <input id="delivery-date" name="deliveryDate" type="date" required>
                            </label>

                            <label>
                                ช่วงเวลาจัดส่ง
                                <select name="deliveryTime">
                                    <option value="09:00–12:00">09:00–12:00 น.</option>
                                    <option value="12:00–15:00">12:00–15:00 น.</option>
                                    <option value="15:00–18:00">15:00–18:00 น.</option>
                                </select>
                            </label>
                        </div>

                        <label>
                            ข้อความบนการ์ด (ไม่บังคับ)
                            <textarea name="cardMessage" maxlength="200" rows="2" placeholder="เช่น สุขสันต์วันเกิด ขอให้มีความสุขมาก ๆ"></textarea>
                        </label>
                    </div>

                    <div class="panel">
                        <h2>03 · วิธีชำระเงิน</h2>

                        <label class="radio">
                            <input type="radio" name="payment" value="โอนเงินผ่านธนาคาร" checked> โอนเงินผ่านธนาคาร
                        </label>

                        <label class="radio">
                            <input type="radio" name="payment" value="ชำระปลายทาง"> ชำระเงินปลายทาง
                        </label>
                    </div>

                </section>

                <aside class="panel summary">
                    <h2>สรุปคำสั่งซื้อ</h2>

                    <div class="line">
                        <span>ราคาสินค้า</span>
                        <strong id="subtotal">฿2,590</strong>
                    </div>

                    <div class="line">
                        <span>ค่าจัดส่ง</span>
                        <span id="shipping">฿0</span>
                    </div>

                    <hr>

                    <div class="line total">
                        <span>ยอดรวม</span>
                        <strong id="total">฿2,590</strong>
                    </div>

                    <button class="submit" type="submit">ยืนยันคำสั่งซื้อ →</button>

                </aside>

            </div>
        </form>

    </main>

    <footer>© 2026 TERM TEM+ · Flowers for every moment</footer>

    <script>
// แก้รายการนี้ให้รหัส ชื่อ ราคา และตำแหน่งรูปตรงกับหน้าหลักของเพื่อน
const $ = id => document.getElementById(id);
const products = {
  '1': {name:'Pastel Dream', price:2590, image:'https://i.pinimg.com/736x/7c/51/d9/7c51d99b4fb4463be950c8e7ef576cf1.jpg'},
  '2': {name:'Blooming Paradise', price:3800, image:'https://i.pinimg.com/736x/61/cd/65/61cd65ba2c05be9de85a3f4ad88a7e42.jpg'},
  '3': {name:'Romantic Blossom', price:3160, image:'https://i.pinimg.com/736x/c2/7c/c9/c27cc9f21e3782d2f7c0b8b9220627e2.jpg'},
  '4': {name:'Morning Sunshine', price:1290, image:'https://i.pinimg.com/736x/56/b8/6f/56b86f967314de094717cce142fabec1.jpg'},
  '5': {name:'Autumn Glow', price:1150, image:'https://i.pinimg.com/1200x/be/8b/8e/be8b8e5989b0e16153afe8b4f8449498.jpg'},
  '6': {name:'Sweet Lily Charm', price:1420, image:'https://i.pinimg.com/1200x/14/cb/8f/14cb8ff8c75c6f0f5836af0d93019622.jpg'}
};
const params = new URLSearchParams(location.search);
const productId = params.get('id') || '1';
$('product-id').value = productId in products ? productId : '1';
const product = products[productId] || products['1'];
let quantity = 1;
const shippingFee = 0; // ค่าจัดส่งสำหรับการสาธิต
const baht = amount => '฿' + amount.toLocaleString('th-TH');
$('product-name').textContent = product.name;
$('product-image').src = product.image;
$('product-image').alt = product.name;
$('unit-price').textContent = baht(product.price);
$('shipping').textContent = baht(shippingFee);
function render(){ $('quantity').textContent = quantity; $('quantity-input').value = quantity; $('cart-count').textContent = quantity; $('subtotal').textContent = baht(product.price*quantity); $('total').textContent = baht(product.price*quantity+shippingFee); }
$('minus').addEventListener('click',()=>{quantity=Math.max(1,quantity-1);render()});
$('plus').addEventListener('click',()=>{quantity=Math.min(99,quantity+1);render()});
const dateInput = $('delivery-date');
const today = new Date();
const localToday = new Date(today.getTime()-today.getTimezoneOffset()*60000).toISOString().slice(0,10);
dateInput.min = localToday;
render();


// สาธิตขั้นตอนสั่งซื้อบนเว็บ HTML (ไม่บันทึกข้อมูลลงฐานข้อมูล)
$('order-form').addEventListener('submit', event => {
  event.preventDefault();
  const form = event.currentTarget;
  if (!form.reportValidity()) return;
  const data = new FormData(form);
  const order = {
    product_name: product.name,
    unit_price: product.price,
    quantity: quantity,
    shipping: shippingFee,
    total: product.price * quantity + shippingFee,
    recipient: String(data.get('recipient') || '').trim(),
    phone: String(data.get('phone') || '').trim(),
    address: String(data.get('address') || '').trim(),
    delivery_date: String(data.get('deliveryDate') || ''),
    delivery_time: String(data.get('deliveryTime') || ''),
    card_message: String(data.get('cardMessage') || '').trim(),
    payment_method: String(data.get('payment') || '')
  };
  if (!order.recipient || !order.address) return;
  try {
    sessionStorage.setItem('termtem_demo_order', JSON.stringify(order));
    window.location.href = 'order-success.php';
  } catch (error) {
    alert('เบราว์เซอร์ไม่สามารถเก็บข้อมูลสำหรับหน้าสรุปได้ กรุณาลองเปิดผ่าน Live Server');
  }
});

</script>

</body>
</html>
