<?php
session_start();

// Check if logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = (float)$_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $rating = (int)$_POST['rating'];

    // معالجة رفع الصورة
    $image_path = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../img/products/';

        // التأكد من وجود المجلد
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = 'img/products/' . $file_name;
        } else {
            $error = 'فشل رفع الصورة';
        }
    } else {
        // استخدام رابط صورة من الإنترنت إذا تم إدخاله
        if (!empty($_POST['image_url'])) {
            $image_path = $_POST['image_url'];
        } else {
            $error = 'الرجاء رفع صورة أو إدخال رابط صورة';
        }
    }

    if (empty($error)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO products (name, price, image, category, description, rating) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsssi", $name, $price, $image_path, $category, $description, $rating);

        if ($stmt->execute()) {
            $success = 'تم إضافة المنتج بنجاح!';
        } else {
            $error = 'حدث خطأ: ' . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج جديد - Bloom Heaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }
        body {
            background: #FFF5F9;
            direction: rtl;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .header {
            background: linear-gradient(135deg, #FF7AA2, #FFB6D0);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #3A3A3A;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #FFE5EF;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Cairo', sans-serif;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #FF7AA2;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #FF7AA2, #FF5C8D);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 122, 162, 0.4);
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            margin-top: 1rem;
        }
        .file-input-wrapper {
            border: 2px dashed #FFE5EF;
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-input-wrapper:hover {
            border-color: #FF7AA2;
            background: #FFF5F9;
        }
        .file-input-wrapper input {
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>➕ إضافة منتج جديد</h1>
        <a href="products.php" class="back-link">← العودة لقائمة المنتجات</a>
    </div>

    <?php if ($error): ?>
        <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            ✅ <?php echo htmlspecialchars($success); ?>
            <br><a href="products.php">عرض جميع المنتجات</a>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>اسم المنتج *</label>
                <input type="text" name="name" required placeholder="مثال: باقة ورد الربيع">
            </div>

            <div class="form-group">
                <label>السعر ($) *</label>
                <input type="number" name="price" step="0.01" required placeholder="25.00">
            </div>

            <div class="form-group">
                <label>التصنيف *</label>
                <select name="category" required>
                    <option value="">اختر التصنيف</option>
                    <option value="birthday">عيد ميلاد</option>
                    <option value="wedding">زفاف</option>
                    <option value="anniversary">ذكرى سنوية</option>
                    <option value="sympathy">مواساة</option>
                    <option value="get-well">الشفاء العاجل</option>
                    <option value="seasonal">موسمي</option>
                </select>
            </div>

            <div class="form-group">
                <label>الوصف *</label>
                <textarea name="description" required placeholder="أدخل وصف المنتج هنا..."></textarea>
            </div>

            <div class="form-group">
                <label>التقييم (عدد النجوم) *</label>
                <select name="rating" required>
                    <option value="5">⭐⭐⭐⭐⭐ (5 نجوم)</option>
                    <option value="4">⭐⭐⭐⭐ (4 نجوم)</option>
                    <option value="3">⭐⭐⭐ (3 نجوم)</option>
                </select>
            </div>

            <div class="form-group">
                <label>صورة المنتج *</label>
                <div class="file-input-wrapper" onclick="document.getElementById('imageFile').click()">
                    <p>📸 اضغط لرفع صورة</p>
                    <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">أو</p>
                    <input type="file" id="imageFile" name="image" accept="image/*">
                </div>
                <p style="text-align: center; margin: 1rem 0; color: #666;">أو</p>
                <input type="url" name="image_url" placeholder="أدخل رابط صورة من الإنترنت">
            </div>

            <button type="submit" class="btn">✅ إضافة المنتج</button>
        </form>
    </div>
</div>
</body>
</html>