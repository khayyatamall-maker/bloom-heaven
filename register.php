<?php
session_start();
require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Create account
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (email, password, full_name, phone, user_type) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->bind_param("ssss", $email, $hashed_password, $full_name, $phone);

            if ($stmt->execute()) {
                // Auto-login after registration
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_type'] = 'customer';
                $_SESSION['full_name'] = $full_name;

                // Redirect to checkout if coming from checkout
                if (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') {
                    header('Location: ../checkout.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bloom Heaven</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FF7AA2, #FFB6D0);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .register-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
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
        input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #FFE5EF;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #FF7AA2;
        }
        .btn {
            width: 100%;
            padding: 1rem;
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
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .links a {
            color: #FF7AA2;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="register-container">
    <div class="logo">🌷</div>
    <h2>Create Account</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <?php echo htmlspecialchars($success); ?>
            <br><a href="login.php">Click here to login</a>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required placeholder="John Doe">
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="john@example.com">
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" required placeholder="+970 599 123 456">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="At least 6 characters">
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required placeholder="Re-enter password">
        </div>

        <button type="submit" class="btn">Create Account</button>
    </form>

    <div class="links">
        Already have an account? <a href="login.php">Login</a><br>
        <a href="../index.php">← Back to Shop</a>
    </div>
</div>
</body>
</html>