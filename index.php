<?php
require_once 'db.php';

// ดึงข้อมูลสินค้าทั้งหมดจากฐานข้อมูล เรียงจากรายการล่าสุด
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn,$sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TERMTEM+ | ร้านขายช่อดอกไม้</title>
  
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
      padding: 15px;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
    }

    /* ส่วนหัว */
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 2px solid #8a4b0f;
    }
    .logo {
      font-size: 20px;
      font-weight: bold;
      color: #8a4b0f;
    }
    .menu-btn {
      border: 1px solid #8a4b0f;
      background: none;
      padding: 6px 12px;
      cursor: pointer;
      color: #8a4b0f;
      font-size: 15px;
      border-radius: 4px;
    }

    /* เมนูหมวดหมู่ */
    #categoryMenu {
      display: none;
      border-bottom: 1px solid #8a4b0f;
      margin-bottom: 15px;
    }
    .menu-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 10px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
    }
    .menu-item:hover {
      background-color: #fff5c8;
      color: #8a4b0f;
    }

    /* แถบตัวกรองและค้นหา */
    .title {
      color: #8a4b0f;
      margin: 20px 0 15px;
    }
    .filter-section {
      margin-bottom: 25px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
    }
    .filter-section input, .filter-section select {
      padding: 8px 12px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      font-family: inherit;
      background-color: #fff;
    }
    .search-input {
      width: 100%;
      max-width: 280px;
    }

    /* การ์ดสินค้า */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      padding-bottom: 50px;
    }
    /* เมื่อคลิกกรอบสินค้า จะทำเป็นลิงก์ไปยัง product.php */
    .product-link {
      text-decoration: none;
      color: inherit;
      display: block;
    }
    .product-card {
      text-align: center;
      background: #fff;
      padding: 12px;
      border: 1px solid #ebd9c8;
      border-radius: 6px;
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(138, 75, 15, 0.12);
    }
    .product-card img {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 4px;
      background-color: #fff5c8;
    }
    .product-name {
      font-weight: 600;
      margin-top: 10px;
      font-size: 16px;
    }
    .product-price {
      color: #8a4b0f;
      font-weight: bold;
      margin-top: 5px;
      font-size: 16px;
    }

    /* Modal ป็อปอัปแจ้งเตือนและฟอร์ม */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
      padding: 15px;
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal-box {
      background: #fffdf8;
      border: 2px solid #8a4b0f;
      border-radius: 8px;
      max-width: 420px;
      width: 100%;
      padding: 22px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.25);
    }
    .modal-box h2 {
      color: #8a4b0f;
      margin-bottom: 15px;
      font-size: 18px;
    }
    .form-group {
      margin-bottom: 12px;
    }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 4px;
    }
    .form-group input, .form-group textarea, .form-group select {
      width: 100%;
      padding: 8px 10px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      font-family: inherit;
    }
    .btn-action {
      width: 100%;
      padding: 10px;
      background-color: #8a4b0f;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 600;
      font-size: 15px;
      margin-top: 5px;
    }
    .btn-action:hover {
      background-color: #6d3a0a;
    }
    .modal-close-btn {
      background: none;
      border: none;
      float: right;
      font-size: 18px;
      cursor: pointer;
      color: #888;
    }
    .popup-img-preview {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-radius: 6px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <div class="container">
    <!-- Header -->
    <header>
      <div class="logo">🌸 TERMTEM+</div>
      <button class="menu-btn" onclick="toggleMenu()">☰ เมนู</button>
    </header>

    <!-- เมนูหมวดหมู่ -->
    <nav id="categoryMenu">
      <div class="menu-item"><span>ดอกไม้สด</span> <span>+</span></div>
      <div class="menu-item"><span>พวงหรีด</span> <span>+</span></div>
      <!-- ปุ่มเปิดฟอร์มเพิ่มสินค้าใหม่ -->
      <div class="menu-item" style="color: #8a4b0f; font-weight: bold;" onclick="openAddModal()">
        <span>+ เพิ่มสินค้าใหม่ลงระบบ (MySQL)</span> <span>➕</span>
      </div>
    </nav>

    <h1 class="title">ช่อดอกไม้</h1>

    <!-- ตัวกรองและค้นหา -->
    <div class="filter-section">
      <input type="text" id="searchInput" class="search-input" placeholder="พิมพ์ค้นหาชื่อสินค้า..." oninput="filterProducts()">

      <select id="priceFilter" onchange="filterProducts()">
        <option value="all">ราคาทั้งหมด</option>
        <option value="low">ต่ำกว่า 2,000</option>
        <option value="high">2,000 ขึ้นไป</option>
      </select>

      <select id="occasionFilter" onchange="filterProducts()">
        <option value="all">ทุกโอกาส</option>
        <option value="birthday">วันเกิด</option>
        <option value="congrats">แสดงความยินดี</option>
      </select>
    </div>

    <!-- แสดงรายการสินค้าที่ดึงมาจาก MySQL -->
    <main class="product-grid" id="productList">
      <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <!-- เมื่อคลิกกรอบการ์ด จะส่ง id ไปยัง product.php -->
          <a href="product.php?id=<?= $row['id'] ?>" class="product-link">
            <div class="product-card" data-price="<?= $row['price'] ?>" data-occasion="<?= htmlspecialchars($row['occasion']) ?>">
              <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
              <div class="product-name"><?= htmlspecialchars($row['name']) ?></div>
              <div class="product-price">฿<?= number_format($row['price']) ?></div>
            </div>
          </a>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #888;">ยังไม่มีรายการสินค้าในระบบ</p>
      <?php endif; ?>
    </main>
  </div>

  <!-- 1. Modal ฟอร์มเพิ่มสินค้าใหม่ -->
  <div id="addModal" class="modal-overlay">
    <div class="modal-box">
      <button class="modal-close-btn" onclick="closeAddModal()">&times;</button>
      <h2>เพิ่มสินค้าใหม่ลง Database</h2>
      <form id="newProductForm" onsubmit="submitNewProduct(event)">
        <div class="form-group">
          <label>ชื่อสินค้า *</label>
          <input type="text" id="name" required placeholder="เช่น Pastel Rose Blossom">
        </div>
        <div class="form-group">
          <label>ราคา (บาท) *</label>
          <input type="number" id="price" required placeholder="เช่น 2490">
        </div>
        <div class="form-group">
          <label>URL รูปภาพ *</label>
          <input type="url" id="image_url" required placeholder="https://...">
        </div>
        <div class="form-group">
          <label>โอกาสที่เหมาะสม</label>
          <select id="occasion">
            <option value="birthday">วันเกิด</option>
            <option value="congrats">แสดงความยินดี</option>
            <option value="birthday,congrats">ทั้งวันเกิดและแสดงความยินดี</option>
          </select>
        </div>
        <div class="form-group">
          <label>รายละเอียดสินค้า</label>
          <textarea id="description" rows="3" placeholder="ระบุประเภทดอกไม้ ความหมาย ฯลฯ"></textarea>
        </div>
        <button type="submit" class="btn-action">บันทึกลง MySQL</button>
      </form>
    </div>
  </div>

  <!-- 2. Modal ป็อปอัปสินค้าใหม่เด้งขึ้นมาอัตโนมัติ -->
  <div id="newProductPopup" class="modal-overlay">
    <div class="modal-box" style="text-align: center;">
      <button class="modal-close-btn" onclick="closeNewProductPopup()">&times;</button>
      <h2>🎉 สินค้าใหม่เข้าแล้ว!</h2>
      <img id="popupImg" class="popup-img-preview" src="" alt="สินค้าใหม่">
      <h3 id="popupName" style="color: #333; margin-bottom: 5px;"></h3>
      <p id="popupPrice" style="color: #8a4b0f; font-weight: bold; font-size: 18px; margin-bottom: 15px;"></p>
      <a id="popupLink" href="#" style="text-decoration: none;">
        <button class="btn-action">ดูรายละเอียดช่อนี้</button>
      </a>
    </div>
  </div>

  <script>
    // สลับเปิด/ปิดเมนูหมวดหมู่
    function toggleMenu() {
      const menu = document.getElementById('categoryMenu');
      menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    // ฟังก์ชันค้นหาและกรองสินค้า
    function filterProducts() {
      const search = document.getElementById('searchInput').value.toLowerCase();
      const priceSelect = document.getElementById('priceFilter').value;
      const occasionSelect = document.getElementById('occasionFilter').value;
      const links = document.querySelectorAll('.product-link');

      links.forEach(link => {
        const card = link.querySelector('.product-card');
        const name = card.querySelector('.product-name').innerText.toLowerCase();
        const price = Number(card.getAttribute('data-price'));
        const occasions = card.getAttribute('data-occasion');

        const matchSearch = name.includes(search);
        let matchPrice = true;
        if (priceSelect === 'low') matchPrice = price < 2000;
        if (priceSelect === 'high') matchPrice = price >= 2000;

        let matchOccasion = true;
        if (occasionSelect !== 'all') matchOccasion = occasions.includes(occasionSelect);

        link.style.display = (matchSearch && matchPrice && matchOccasion) ? "" : "none";
      });
    }

    // Modal จัดการฟอร์มเพิ่มสินค้า
    function openAddModal() {
      document.getElementById('addModal').classList.add('active');
    }
    function closeAddModal() {
      document.getElementById('addModal').classList.remove('active');
      document.getElementById('newProductForm').reset();
    }

    // Modal ป็อปอัปสินค้าใหม่เด้งขึ้นมา
    function showNewProductPopup(item) {
      document.getElementById('popupImg').src = item.image_url;
      document.getElementById('popupName').innerText = item.name;
      document.getElementById('popupPrice').innerText = '฿' + Number(item.price).toLocaleString();
      document.getElementById('popupLink').href = 'product.php?id=' + item.id;
      document.getElementById('newProductPopup').classList.add('active');
    }
    function closeNewProductPopup() {
      document.getElementById('newProductPopup').classList.remove('active');
    }

    // ฟังก์ชันส่งข้อมูลไปยัง add_product.php ผ่าน AJAX Fetch
    async function submitNewProduct(e) {
      e.preventDefault();
      
      const payload = {
        name: document.getElementById('name').value.trim(),
        price: document.getElementById('price').value,
        image_url: document.getElementById('image_url').value.trim(),
        occasion: document.getElementById('occasion').value,
        description: document.getElementById('description').value.trim()
      };

      try {
        const res = await fetch('add_product.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await res.json();

        if (result.success) {
          closeAddModal();

          // สร้างการ์ดใหม่แทรกบนสุดของรายการทันที
          const productList = document.getElementById('productList');
          const newLink = document.createElement('a');
          newLink.href = 'product.php?id=' + result.data.id;
          newLink.className = 'product-link';
          newLink.innerHTML = `
            <div class="product-card" data-price="${result.data.price}" data-occasion="${result.data.occasion}">
              <img src="${result.data.image_url}" alt="${result.data.name}">
              <div class="product-name">${result.data.name}</div>
              <div class="product-price">฿${Number(result.data.price).toLocaleString()}</div>
            </div>
          `;
          productList.prepend(newLink);

          // เด้งหน้าต่าง Pop-up สินค้าใหม่ขึ้นมาอัตโนมัติ
          showNewProductPopup(result.data);
        } else {
          alert('เกิดข้อผิดพลาด: ' + result.message);
        }
      } catch (err) {
        alert('ไม่สามารถเชื่อมต่อ Server ได้');
      }
    }
  </script>
</body>
</html>