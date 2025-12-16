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
session_destroy();
header('Location: login.php');
exit;
?>