<?php
include_once 'dbconnect_flower.php';

// ดึงข้อมูลสินค้าโดยใช้ชื่อคอลัมน์ตามตารางจริง
$sql = "SELECT product_id, product_name, product_price, product_image, product_occasion, product_description FROM products ORDER BY product_id DESC";
$result = mysqli_query($conn,$sql);

$db_products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {$db_products[] = [
            'id' => (int)$row['product_id'],
            'name' => $row['product_name'],
            'price' => (float)$row['product_price'],
            'occasions' => array_filter(array_map('trim', explode(',', $row['product_occasion'] ?? ''))),
            'img' => $row['product_image'],
            'description' => $row['product_description']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TERMTEM+</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
  
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Bai Jamjuree', sans-serif;
    }
    body {
      color: #333;
      background-color: #fffdf8;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 15px;
    }

    /* Header */
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
    .header-right {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 17px;
    }
    .menu-toggle-btn {
      background: none;
      border: 1px solid #8a4b0f;
      padding: 5px 10px;
      cursor: pointer;
      font-size: 16px;
      border-radius: 4px;
      color: #8a4b0f;
    }

    /* ปรับปรุงปุ่มให้เป็นลิงก์ที่ดูเหมือนปุ่ม */
    .btn-text {
      background: none;        /* ลบสีพื้นหลังสีเทา */
      border: none;            /* ลบเส้นกรอบ */
      padding: 0;              /* ลบช่องว่างรอบตัวอักษร */
      font: inherit;           /* ใช้รูปแบบตัวอักษรและขนาดเดียวกับหน้าเว็บ */
      color: inherit;          /* ใช้สีตัวอักษรตามเนื้อหาปกติ */
      cursor: pointer;         /* แสดงรูปมือชี้เมื่อนำเมาส์ไปวาง */
      display: inline-flex;    /* จัดไอคอนกับตัวหนังสือให้อยู่กึ่งกลางพอดีกัน */
      align-items: center;
      gap: 6px;                /* ระยะห่างระหว่างไอคอนกับข้อความ */
    }

    /* เมนูหมวดหมู่ */
    #categoryMenu {
      display: none;
      border-bottom: 1px solid #8a4b0f;
      background-color: #fffdf8;
    }
    #categoryMenu.active {
      display: block;
    }
    .menu-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 10px;
      border-bottom: 1px solid #f0e6d6;
      text-decoration: none;
      color: #555;
      cursor: pointer;
      font-size: 15px;
    }
    .menu-item:hover {
      background-color: #fff5c8;
      color: #8a4b0f;
    }
    .menu-item.highlight-btn {
      font-weight: 600;
      color: #8a4b0f;
      background-color: #fef7e0;
    }

    /* กล่องค้นหา */
    .search-box-container {
      display: none;
      padding: 12px 10px;
      background: #fdf5e6;
      border-bottom: 1px solid #8a4b0f;
      gap: 8px;
    }
    .search-box-container.active {
      display: flex;
    }
    .search-input {
      flex: 1;
      padding: 8px 12px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      font-size: 14px;
      background: #fff;
    }
    .search-clear-btn {
      padding: 6px 12px;
      border: 1px solid #8a4b0f;
      background: #fff;
      color: #8a4b0f;
      border-radius: 4px;
      cursor: pointer;
    }

    /* ตัวกรอง */
    .page-title {
      color: #8a4b0f;
      font-size: 24px;
      margin: 20px 0 15px;
    }
    .filters-bar {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-bottom: 25px;
    }
    .filter-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
    }
    .filter-item select {
      padding: 6px 12px;
      border: 1px solid #8a4b0f;
      border-radius: 4px;
      background: #fffdf8;
      font-family: inherit;
    }

    /* การ์ดสินค้า */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      padding-bottom: 50px;
    }
    .product-link {
      text-decoration: none;
      color: inherit;
      display: block;
    }
    .product-card {
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
      background: #fff;
      border: 1px solid #f0e6d6;
      border-radius: 6px;
      padding: 10px;
    }
    .product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(138, 75, 15, 0.12);
    }
    .product-card img {
      width: 100%;
      height: 320px;
      object-fit: cover;
      border-radius: 4px;
      background-color: #fff5c8;
    }
    .product-name {
      font-size: 15px;
      color: #555;
      margin-top: 10px;
      font-weight: 500;
    }
    .product-price {
      font-size: 16px;
      font-weight: bold;
      color: #8a4b0f;
      margin-top: 4px;
    }
    .no-products {
      grid-column: 1 / -1;
      text-align: center;
      padding: 50px 0;
      color: #888;
      font-size: 16px;
    }

    /* Modals */
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
    .modal-content {
      background: #fffdf8;
      border: 2px solid #8a4b0f;
      border-radius: 8px;
      max-width: 450px;
      width: 100%;
      padding: 24px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
    }
    .modal-header h2 {
      font-size: 20px;
      color: #8a4b0f;
    }
    .modal-close-btn {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: #888;
    }
    .form-group {
      margin-bottom: 14px;
    }
    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 5px;
      color: #555;
    }
    .form-group input, 
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-family: inherit;
      font-size: 14px;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #8a4b0f;
      outline: none;
    }
    .checkbox-group {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-top: 5px;
    }
    .checkbox-group label {
      display: flex;
      align-items: center;
      gap: 5px;
      font-weight: normal;
      cursor: pointer;
    }
    .btn-submit {
      width: 100%;
      padding: 10px;
      background-color: #8a4b0f;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 10px;
    }
    .btn-submit:hover {
      background-color: #6d3a0a;
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
    <header>
      <div class="logo">🌸TERMTEM+</div>
      <div class="header-right">
        <a href="login.php" class="btn-text" style="text-decoration: none; color: inherit;">
          👤 ล็อกอิน/สมัครสมาชิก
        </a>
        <span>🛒 0</span>
        <button class="menu-toggle-btn" onclick="toggleMenu()">☰ เมนู</button>
      </div>
    </header>

    <nav id="categoryMenu">
      
      <div class="menu-item highlight-btn" onclick="toggleSearchBox()">
        <span>ค้นหาสินค้า</span> <span>🔍</span>
      </div>

      <div class="menu-item highlight-btn" onclick="openAddModal()">
        <span>เพิ่มสินค้าใหม่ (บันทึกลง Database)</span> <span>➕</span>
      </div>
    </nav>

    <div id="searchBoxContainer" class="search-box-container">
      <input type="text" id="searchInput" class="search-input" placeholder="พิมพ์ชื่อดอกไม้ที่ต้องการค้นหา..." oninput="applyFilters()">
      <button class="search-clear-btn" onclick="clearSearch()">ล้างการค้นหา</button>
    </div>

    <h1 class="page-title">ช่อดอกไม้</h1>
    <div class="filters-bar">
      <div class="filter-item">
        <label for="priceFilter">ราคา:</label>
        <select id="priceFilter" onchange="applyFilters()">
          <option value="all">ทั้งหมด</option>
          <option value="under1500">ต่ำกว่า 1,500</option>
          <option value="1500-2500">1,500 - 2,500</option>
          <option value="above2500">มากกว่า 2,500</option>
        </select>
      </div>

      <div class="filter-item">
        <label for="sortFilter">จัดเรียง:</label>
        <select id="sortFilter" onchange="applyFilters()">
          <option value="latest">สินค้าล่าสุด</option>
          <option value="price-asc">ราคา: ต่ำไปสูง</option>
          <option value="price-desc">ราคา: สูงไปต่ำ</option>
        </select>
      </div>

      <div class="filter-item">
        <label for="occasionFilter">โอกาส:</label>
        <select id="occasionFilter" onchange="applyFilters()">
          <option value="all">ทั้งหมด</option>
          <option value="birthday">วันเกิด</option>
          <option value="graduation">รับปริญญา</option>
          <option value="congrats">แสดงความยินดี</option>
        </select>
      </div>
    </div>

    <main class="product-grid" id="productGrid"></main>
  </div>

  <!-- Modal ฟอร์มเพิ่มสินค้าใหม่ -->
  <div id="addModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h2>เพิ่มรายการสินค้าใหม่</h2>
        <button class="modal-close-btn" onclick="closeAddModal()">&times;</button>
      </div>
      
      <form id="newProductForm" onsubmit="handleAddNewProduct(event)">
        <div class="form-group">
          <label for="newProdName">ชื่อสินค้า (product_name) *</label>
          <input type="text" id="newProdName" placeholder="เช่น Pastel Rose Dream" required>
        </div>

        <div class="form-group">
          <label for="newProdPrice">ราคา (product_price) *</label>
          <input type="number" id="newProdPrice" min="1" placeholder="เช่น 1800" required>
        </div>

        <div class="form-group">
          <label for="newProdImg">ลิงก์รูปภาพ (product_image) *</label>
          <input type="url" id="newProdImg" placeholder="https://..." required>
        </div>

        <div class="form-group">
          <label>เหมาะสำหรับโอกาส (product_occasion):</label>
          <div class="checkbox-group">
            <label><input type="checkbox" name="occasionCheck" value="birthday"> วันเกิด</label>
            <label><input type="checkbox" name="occasionCheck" value="graduation"> รับปริญญา</label>
            <label><input type="checkbox" name="occasionCheck" value="congrats"> แสดงความยินดี</label>
          </div>
        </div>

        <div class="form-group">
          <label for="newProdDesc">รายละเอียดสินค้า (product_description)</label>
          <textarea id="newProdDesc" rows="3" placeholder="ระบุประเภทดอกไม้ หรือความหมายพิเศษ..."></textarea>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">+ บันทึกสินค้าลง Database</button>
      </form>
    </div>
  </div>

  <!-- Modal แจ้งเตือนสินค้าใหม่เด้งขึ้นมากลางจออัตโนมัติ -->
  <div id="newProductPopup" class="modal-overlay">
    <div class="modal-content" style="text-align: center;">
      <div class="modal-header">
        <h2 style="width: 100%; text-align: center;">🎉 เพิ่มสินค้าใหม่สำเร็จ!</h2>
        <button class="modal-close-btn" onclick="closeNewProductPopup()">&times;</button>
      </div>
      <img id="popupImg" class="popup-img-preview" src="" alt="สินค้าใหม่">
      <h3 id="popupName" style="color: #333; margin-bottom: 5px;"></h3>
      <p id="popupPrice" style="color: #8a4b0f; font-weight: bold; font-size: 18px; margin-bottom: 15px;"></p>
      <a id="popupLink" href="#" style="text-decoration: none;">
        <button class="btn-submit" style="margin-top: 0;">ดูหน้ารายละเอียดสินค้านี้</button>
      </a>
    </div>
  </div>

  <script>
    let products = <?= json_encode($db_products, JSON_UNESCAPED_UNICODE) ?>;

    function toggleMenu() {
      const menu = document.getElementById('categoryMenu');
      menu.classList.toggle('active');
    }

    function toggleSearchBox() {
      const searchBox = document.getElementById('searchBoxContainer');
      searchBox.classList.toggle('active');
      if (searchBox.classList.contains('active')) {
        document.getElementById('searchInput').focus();
      }
    }

    function clearSearch() {
      document.getElementById('searchInput').value = '';
      applyFilters();
    }

    function openAddModal() {
      document.getElementById('addModal').classList.add('active');
    }

    function closeAddModal() {
      document.getElementById('addModal').classList.remove('active');
      document.getElementById('newProductForm').reset();
    }

    function showNewProductPopup(item) {
      document.getElementById('popupImg').src = item.img;
      document.getElementById('popupName').innerText = item.name;
      document.getElementById('popupPrice').innerText = '฿' + Number(item.price).toLocaleString();
      document.getElementById('popupLink').href = 'product.php?id=' + item.id;
      document.getElementById('newProductPopup').classList.add('active');
    }

    function closeNewProductPopup() {
      document.getElementById('newProductPopup').classList.remove('active');
    }

    function renderProducts(items) {
      const grid = document.getElementById('productGrid');
      grid.innerHTML = '';

      if (items.length === 0) {
        grid.innerHTML = '<div class="no-products">ไม่พบสินค้าที่ตรงตามเงื่อนไขหรือคำค้นหา</div>';
        return;
      }

      items.forEach(product => {
        const link = document.createElement('a');
        link.href = `product.php?id=${product.id}`;
        link.className = 'product-link';
        link.innerHTML = `
          <div class="product-card">
            <img src="${product.img}" alt="${product.name}" loading="lazy" onerror="this.src='https://placehold.co/400x500?text=TERMTEM+'">
            <div class="product-name">${product.name}</div>
            <div class="product-price">฿${product.price.toLocaleString()}</div>
          </div>
        `;
        grid.appendChild(link);
      });
    }

    function applyFilters() {
      const searchQuery = document.getElementById('searchInput').value.trim().toLowerCase();
      const priceVal = document.getElementById('priceFilter').value;
      const sortVal = document.getElementById('sortFilter').value;
      const occasionVal = document.getElementById('occasionFilter').value;

      let filtered = products.filter(product => {
        const matchSearch = product.name.toLowerCase().includes(searchQuery);

        let matchPrice = true;
        if (priceVal === 'under1500') {
          matchPrice = product.price < 1500;
        } else if (priceVal === '1500-2500') {
          matchPrice = product.price >= 1500 && product.price <= 2500;
        } else if (priceVal === 'above2500') {
          matchPrice = product.price > 2500;
        }

        let matchOccasion = true;
        if (occasionVal !== 'all') {
          matchOccasion = product.occasions && product.occasions.includes(occasionVal);
        }

        return matchSearch && matchPrice && matchOccasion;
      });

      if (sortVal === 'price-asc') {
        filtered.sort((a, b) => a.price - b.price);
      } else if (sortVal === 'price-desc') {
        filtered.sort((a, b) => b.price - a.price);
      } else if (sortVal === 'latest') {
        filtered.sort((a, b) => b.id - a.id);
      }

      renderProducts(filtered);
    }

    async function handleAddNewProduct(event) {
      event.preventDefault();

      const submitBtn = document.getElementById('submitBtn');
      submitBtn.disabled = true;
      submitBtn.innerText = 'กำลังบันทึก...';

      const checkedBoxes = document.querySelectorAll('input[name="occasionCheck"]:checked');
      const selectedOccasions = Array.from(checkedBoxes).map(cb => cb.value);

      const payload = {
        product_name: document.getElementById('newProdName').value.trim(),
        product_price: parseFloat(document.getElementById('newProdPrice').value),
        product_image: document.getElementById('newProdImg').value.trim(),
        product_occasion: selectedOccasions.length > 0 ? selectedOccasions.join(',') : 'birthday,congrats,graduation',
        product_description: document.getElementById('newProdDesc').value.trim()
      };

      try {
        const response = await fetch('add_product.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();

        if (result.success) {
          products.unshift(result.data);
          closeAddModal();
          applyFilters();
          showNewProductPopup(result.data);
        } else {
          alert('เกิดข้อผิดพลาด: ' + result.message);
        }
      } catch (err) {
        alert('ไม่สามารถเชื่อมต่อ Server ได้');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = '+ บันทึกสินค้าลง Database';
      }
    }

    document.addEventListener('DOMContentLoaded', applyFilters);
  </script>
</body>
</html>