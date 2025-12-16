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

// If already logged in as admin, go to dashboard
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

// If logged in as customer, redirect to customer dashboard
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer') {
    header('Location: ../customer/dashboard.php');
    exit;
}

require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, password, user_type, full_name FROM users WHERE email = ? AND user_type = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'Admin account not found or you do not have admin privileges';
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المدير - Bloom Heaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #FF7AA2, #FFB6D0);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            direction: rtl;
        }
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
        }
        .logo {
            text-align: center;
            font-size: 2.5rem;
            color: #FF7AA2;
            margin-bottom: 1rem;
        }
        h2 {
            text-align: center;
            color: #3A3A3A;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #3A3A3A;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #FFE5EF;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #FF7AA2;
            box-shadow: 0 0 0 3px rgba(255, 122, 162, 0.1);
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
            font-family: 'Cairo', sans-serif;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 122, 162, 0.4);
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #FF7AA2;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .info-box {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #1976d2;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo">🌷</div>
    <h2>تسجيل دخول المدير</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>معلومات الدخول الافتراضية:</strong><br>
        البريد: admin@bloomheaven.com<br>
        كلمة المرور: Admin@123
    </div>

    <form method="POST">
        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" required placeholder="admin@bloomheaven.com">
        </div>

        <div class="form-group">
            <label>كلمة المرور</label>
            <input type="password" name="password" required placeholder="أدخل كلمة المرور">
        </div>

        <button type="submit" class="btn">تسجيل الدخول</button>
    </form>

    <div class="back-link">
        <a href="../index.php">← العودة للموقع</a>
    </div>
</div>
</body>
</html>