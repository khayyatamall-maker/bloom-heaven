
<?php
session_start();

// Check if logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<?php
require_once '../auth.php';
requireAdmin();

require_once '../config.php';

$success = '';
$error = '';

// Get product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: products.php');
    exit;
}

$conn = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $category = $_POST['category'];
    $stock = (int)$_POST['stock'];

    // Check if new image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $newname = 'uploads/products/' . uniqid() . '.' . $filetype;

            if (!file_exists('../uploads/products')) {
                mkdir('../uploads/products', 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $newname)) {
                // Delete old image
                $oldImage = $conn->query("SELECT image FROM products WHERE id = $id")->fetch_assoc()['image'];
                if (file_exists('../' . $oldImage)) {
                    unlink('../' . $oldImage);
                }

                $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, image=?, stock=? WHERE id=?");
                $stmt->bind_param("ssdssii", $name, $description, $price, $category, $newname, $stock, $id);
            }
        } else {
            $error = 'Invalid file type';
        }
    } else {
        // Update without changing image
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, stock=? WHERE id=?");
        $stmt->bind_param("ssdsii", $name, $description, $price, $category, $stock, $id);
    }

    if (isset($stmt) && $stmt->execute()) {
        $success = 'Product updated successfully!';
    } elseif (!isset($stmt)) {
        $error = $error ?: 'Failed to update product';
    }

    if (isset($stmt)) $stmt->close();
}

// Get product details
$result = $conn->query("SELECT * FROM products WHERE id = $id");
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #FFF5F9;
            padding: 2rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #FF7AA2;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #3A3A3A;
            font-weight: 500;
        }
        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #FFE5EF;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #FF7AA2;
        }
        .btn {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #FF7AA2, #FF5C8D);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 122, 162, 0.4);
        }
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #FF7AA2;
            text-decoration: none;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .current-image {
            margin-top: 1rem;
            max-width: 300px;
        }
        .current-image img {
            width: 100%;
            border-radius: 8px;
        }
        .image-preview {
            margin-top: 1rem;
            max-width: 300px;
        }
        .image-preview img {
            width: 100%;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="products.php" class="back-link">← Back to Products</a>
    <h1>Edit Product</h1>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Price ($) *</label>
            <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
        </div>

        <div class="form-group">
            <label>Category *</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="roses" <?php echo $product['category'] === 'roses' ? 'selected' : ''; ?>>Roses</option>
                <option value="tulips" <?php echo $product['category'] === 'tulips' ? 'selected' : ''; ?>>Tulips</option>
                <option value="orchids" <?php echo $product['category'] === 'orchids' ? 'selected' : ''; ?>>Orchids</option>
                <option value="lilies" <?php echo $product['category'] === 'lilies' ? 'selected' : ''; ?>>Lilies</option>
                <option value="mixed" <?php echo $product['category'] === 'mixed' ? 'selected' : ''; ?>>Mixed Bouquets</option>
                <option value="arrangements" <?php echo $product['category'] === 'arrangements' ? 'selected' : ''; ?>>Arrangements</option>
            </select>
        </div>

        <div class="form-group">
            <label>Stock Quantity *</label>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
        </div>

        <div class="form-group">
            <label>Current Image</label>
            <div class="current-image">
                <img src="../<?php echo $product['image']; ?>" alt="Current product image">
            </div>
        </div>

        <div class="form-group">
            <label>Change Image (optional)</label>
            <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
            <div class="image-preview">
                <img id="preview" alt="Preview">
            </div>
        </div>

        <button type="submit" class="btn">Update Product</button>
    </form>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>